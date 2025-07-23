<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProcessBulkMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Collection $messages)
    {
    }

    public function handle(): void
    {
        Log::info('Starting bulk message processing', [
            'count' => $this->messages->count()
        ]);

        $this->messages->each(function ($messageData) {
            try {
                // Create and dispatch individual message
                $message = Message::create([
                    'title' => $messageData['title'],
                    'content' => $messageData['content'],
                    'status' => Message::STATUS_PENDING,
                    'metadata' => [
                        'bulk_job_id' => $this->job->getJobId(),
                        'queued_at' => now()->toIsoString(),
                    ],
                ]);

                ProcessMessageJob::dispatch($message)
                    ->onConnection('kafka')
                    ->onQueue('messages');

                Log::info('Queued message from bulk job', [
                    'message_id' => $message->id,
                    'bulk_job_id' => $this->job->getJobId()
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to queue message from bulk job', [
                    'error' => $e->getMessage(),
                    'bulk_job_id' => $this->job->getJobId(),
                    'message_data' => $messageData
                ]);
            }
        });

        Log::info('Completed bulk message processing', [
            'bulk_job_id' => $this->job->getJobId(),
            'processed_count' => $this->messages->count()
        ]);
    }
}
