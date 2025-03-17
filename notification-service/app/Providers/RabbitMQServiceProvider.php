<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Queue;

class RabbitMQServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Queue::extend('rabbitmq', function () {
            $config = $this->app['config']['queue.connections.rabbitmq'];

            return new class($config) {
                protected $config;
                protected $connection;
                protected $channel;

                public function __construct($config)
                {
                    $this->config = $config;
                    $this->connection = new AMQPStreamConnection(
                        $config['host'],
                        $config['port'],
                        $config['login'],
                        $config['password'],
                        $config['vhost']
                    );
                    $this->channel = $this->connection->channel();
                    
                    // Declare exchange
                    $this->channel->exchange_declare(
                        $config['options']['exchange']['name'],
                        $config['options']['exchange']['type'],
                        false,
                        true,
                        false
                    );

                    // Declare queue
                    $this->channel->queue_declare(
                        $config['queue'],
                        false,
                        true,
                        false,
                        false
                    );

                    // Bind queue to exchange
                    $this->channel->queue_bind(
                        $config['queue'],
                        $config['options']['exchange']['name']
                    );
                }

                public function size($queue = null)
                {
                    list(, $messageCount) = $this->channel->queue_declare(
                        $queue ?? $this->config['queue'],
                        false,
                        true,
                        false,
                        false
                    );
                    return $messageCount;
                }

                public function push($job, $data = '', $queue = null)
                {
                    $payload = json_encode([
                        'displayName' => get_class($job),
                        'job' => 'Illuminate\Queue\CallQueuedHandler@call',
                        'maxTries' => null,
                        'timeout' => null,
                        'data' => [
                            'commandName' => get_class($job),
                            'command' => serialize($job)
                        ]
                    ]);

                    $message = new AMQPMessage(
                        $payload,
                        ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
                    );

                    $this->channel->basic_publish(
                        $message,
                        $this->config['options']['exchange']['name'],
                        $queue ?? $this->config['queue']
                    );

                    return true;
                }

                public function pop($queue = null)
                {
                    $message = $this->channel->basic_get(
                        $queue ?? $this->config['queue']
                    );

                    if ($message instanceof \PhpAmqpLib\Message\AMQPMessage) {
                        return json_decode($message->body, true);
                    }

                    return null;
                }

                public function __destruct()
                {
                    if ($this->channel) {
                        $this->channel->close();
                    }
                    if ($this->connection) {
                        $this->connection->close();
                    }
                }
            };
        });
    }
}
