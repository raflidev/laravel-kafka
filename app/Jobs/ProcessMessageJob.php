<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Message $message)
    {
    }

    public function handle(): void
    {
        try {
            // Update message status to processing
            $this->message->update(['status' => Message::STATUS_PROCESSING]);


            // Update the message with processed data
            $this->message->update([
                'status' => Message::STATUS_COMPLETED,
                'metadata' => array_merge($this->message->metadata ?? [], [
                    'processed_at' => now()->toIsoString(),
                    'processor_id' => gethostname(),
                ]),
            ]);

            Log::info('Message processed successfully', [
                'message_id' => $this->message->id,
                'kafka_message_id' => $this->message->kafka_message_id,
            ]);
        } catch (\Exception $e) {
            $this->message->update([
                'status' => Message::STATUS_FAILED,
                'metadata' => array_merge($this->message->metadata ?? [], [
                    'error' => $e->getMessage(),
                    'failed_at' => now()->toIsoString(),
                ]),
            ]);

            Log::error('Failed to process message', [
                'message_id' => $this->message->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
} 