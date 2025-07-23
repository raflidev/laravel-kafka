<?php

namespace App\Kafka;

use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Jobs\JobName;
use RdKafka\Producer;
use RdKafka\Consumer;
use RdKafka\Message;

class KafkaQueue extends Queue implements QueueContract
{
    protected $producer;
    protected $consumer;
    protected $defaultQueue;
    protected $consumerTopics = [];

    public function __construct(Producer $producer, Consumer $consumer, $defaultQueue)
    {
        $this->producer = $producer;
        $this->consumer = $consumer;
        $this->defaultQueue = $defaultQueue;
    }

    public function size($queue = null)
    {
        // Kafka doesn't provide a way to get queue size
        return 0;
    }

    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $queue ?: $this->defaultQueue, $data), $queue);
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        try {
            $topic = $this->producer->newTopic($queue ?: $this->defaultQueue);
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $payload);
            $this->producer->flush(1000);
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Could not push message to Kafka: " . $e->getMessage());
        }
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        // Kafka doesn't support delayed messages natively
        // You might want to implement this using a separate delayed message processor
        return $this->push($job, $data, $queue);
    }

    public function pop($queue = null)
    {
        $queue = $queue ?: $this->defaultQueue;

        if (!isset($this->consumerTopics[$queue])) {
            $topic = $this->consumer->newTopic($queue);
            $topic->consumeStart(0, RD_KAFKA_OFFSET_END);
            $this->consumerTopics[$queue] = $topic;
        }

        $message = $this->consumerTopics[$queue]->consume(0, 1000); // Menambahkan parameter partition (0)

        if ($message === null) {
            return null;
        }

        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                return new KafkaJob(
                    $this->container,
                    $this,
                    $message,
                    $queue
                );
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                return null;
            default:
                throw new \Exception($message->errstr(), $message->err);
        }
    }
}