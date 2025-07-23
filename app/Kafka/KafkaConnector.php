<?php

namespace App\Kafka;

use Illuminate\Queue\Connectors\ConnectorInterface;
use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\Consumer;

class KafkaConnector implements ConnectorInterface
{
    public function connect(array $config)
    {
        $conf = new Conf();
        
        // Set configuration
        $conf->set('metadata.broker.list', $config['brokers'] ?? 'localhost:9092');
        
        if (isset($config['security_protocol'])) {
            $conf->set('security.protocol', $config['security_protocol']);
        }
        
        if (isset($config['sasl_mechanisms'])) {
            $conf->set('sasl.mechanisms', $config['sasl_mechanisms']);
            $conf->set('sasl.username', $config['sasl_username'] ?? '');
            $conf->set('sasl.password', $config['sasl_password'] ?? '');
        }

        // Create producer and consumer instances
        $producer = new Producer($conf);
        $consumer = new Consumer($conf);
        
        return new KafkaQueue($producer, $consumer, $config['queue'] ?? 'default');
    }
}