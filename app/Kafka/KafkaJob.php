<?php

namespace App\Kafka;

use Illuminate\Container\Container;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Contracts\Queue\Job as JobContract;
use RdKafka\Message;

class KafkaJob extends Job implements JobContract
{
    protected $consumer;
    protected $message;
    protected $queue;
    protected $connectionName;

    public function __construct(Container $container, KafkaQueue $consumer, Message $message, $queue)
    {
        $this->container = $container;
        $this->consumer = $consumer;
        $this->message = $message;
        $this->queue = $queue;
        $this->connectionName = 'kafka';
    }

    public function getJobId()
    {
        return $this->message->key ?? null;
    }

    public function getRawBody()
    {
        return $this->message->payload;
    }

    public function attempts()
    {
        // Kafka doesn't track attempts
        return 1;
    }

    public function delete()
    {
        parent::delete();
        // Kafka auto-commits the offset when auto.commit.enable is true (default)
    }

    public function release($delay = 0)
    {
        parent::release($delay);
        
        // Re-publish the message to the topic
        $this->consumer->pushRaw($this->message->payload, $this->queue);
    }
} 