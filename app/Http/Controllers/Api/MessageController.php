<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkMessageRequest;
use App\Jobs\ProcessBulkMessagesJob;
use App\Jobs\ProcessMessageJob;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function index(): JsonResponse
    {
        $messages = Message::latest()->paginate(10);
        return response()->json($messages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $message = Message::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'status' => Message::STATUS_PENDING,
            'kafka_message_id' => Str::uuid()->toString(),
        ]);

        // Dispatch the job to Kafka queue
        ProcessMessageJob::dispatch($message)
            ->onConnection('kafka')
            ->onQueue('messages');

        return response()->json([
            'message' => 'Message created successfully',
            'data' => $message,
        ], 201);
    }

    public function bulkStore(BulkMessageRequest $request): JsonResponse
    {
        $messages = new Collection($request->validated('messages'));
        
        ProcessBulkMessagesJob::dispatch($messages)
            ->onConnection('kafka')
            ->onQueue('bulk-messages');

        return response()->json([
            'message' => 'Bulk messages queued for processing',
            'count' => $messages->count(),
        ], 202);
    }

    public function show(Message $message): JsonResponse
    {
        return response()->json($message);
    }

    public function update(Request $request, Message $message): JsonResponse
    {
        if ($message->status !== Message::STATUS_PENDING) {
            return response()->json([
                'message' => 'Cannot update message that is already being processed or completed',
            ], 422);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $message->update($validated);

        return response()->json([
            'message' => 'Message updated successfully',
            'data' => $message,
        ]);
    }

    public function destroy(Message $message): JsonResponse
    {
        if ($message->status === Message::STATUS_PROCESSING) {
            return response()->json([
                'message' => 'Cannot delete message that is being processed',
            ], 422);
        }

        $message->delete();

        return response()->json([
            'message' => 'Message deleted successfully',
        ]);
    }

    public function retry(Message $message): JsonResponse
    {
        if ($message->status !== Message::STATUS_FAILED) {
            return response()->json([
                'message' => 'Only failed messages can be retried',
            ], 422);
        }

        $message->update([
            'status' => Message::STATUS_PENDING,
            'metadata' => array_merge($message->metadata ?? [], [
                'retried_at' => now()->toIsoString(),
                'retry_count' => ($message->metadata['retry_count'] ?? 0) + 1,
            ]),
        ]);

        ProcessMessageJob::dispatch($message)
            ->onConnection('kafka')
            ->onQueue('messages');

        return response()->json([
            'message' => 'Message queued for retry',
            'data' => $message,
        ]);
    }
}
