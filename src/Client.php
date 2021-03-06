<?php

namespace Mouf\AmqpClient;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;

class Client
{
    /**
     * RabbitMq host.
     *
     * @var string
     */
    private $host;

    /**
     * RabbitMq port.
     *
     * @var string
     */
    private $port;

    /**
     * RabbitMq user.
     *
     * @var string
     */
    private $user;

    /**
     * RabbitMq password.
     *
     * @var string
     */
    private $password;

    /**
     * It's for QOS prefetch-size http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos.
     *
     * @var int
     */
    private $prefetchSize = null;

    /**
     * It's for QOS prefetch-count http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos.
     *
     * @var int
     */
    private $prefetchCount = null;

    /**
     * It's for QOS global http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos.
     *
     * @var int
     */
    private $aGlobal = null;

    /**
     * RabbitMq connection.
     *
     * @var AMQPStreamConnection
     */
    private $connection = null;

    /**
     * RabbitMq channel.
     *
     * @var \AMQPChannel
     */
    private $channel = null;

    /**
     * List of RabbitMq object.
     *
     * @var RabbitMqObjectInterface[]
     */
    private $rabbitMqObjects = [];

    public function __construct($host, $port, $user, $password)
    {
        $this->host = $host;
        $this->port = ($port !== null) ? $port : 5672;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Get prefetch size for QOS.
     */
    public function getPrefetchSize()
    {
        return $this->prefetchSize;
    }

    /**
     * Set prefetch size
     * It's for QOS prefetch-size http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos.
     * 
     * @param int $prefetchSize
     */
    public function setPrefetchSize($prefetchSize)
    {
        $this->prefetchSize = $prefetchSize;

        return $this;
    }

    /**
     * Get prefetch count for QOS.
     */
    public function getPrefetchCount()
    {
        return $this->prefetchCount;
    }

    /**
     * Set prefetch size
     * It's for QOS prefetch-size http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos.
     * 
     * @param int $prefetchCount
     */
    public function setPrefetchCount($prefetchCount)
    {
        $this->prefetchCount = $prefetchCount;

        return $this;
    }

    /**
     * Get a global for QOS.
     */
    public function getAGlobal()
    {
        return $this->aGlobal;
    }

    /**
     * Set global
     * It's for QOS prefetch-size http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos.
     * 
     * @param int $aGlobal
     */
    public function setAGlobal($aGlobal)
    {
        $this->aGlobal = $aGlobal;

        return $this;
    }

    /**
     * Set RabbitMq object.
     *
     * @param RabbitMqObjectInterface[] $rabbitMqObjects
     */
    public function setRabbitMqObjects(array $rabbitMqObjects)
    {
        $this->rabbitMqObjects = $rabbitMqObjects;
    }

    public function register(RabbitMqObjectInterface $object)
    {
        if (!in_array($object, $this->rabbitMqObjects, true)) {
            $this->rabbitMqObjects[] = $object;
        }
    }

    /**
     * Connection to the RabbitMq service with AMQPStreamConnection.
     *
     * @return AMQPChannel
     */
    public function getChannel()
    {
        if (!$this->connection) {
            $this->connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password);

            $this->channel = $this->connection->channel();

            if ($this->prefetchSize !== null || $this->prefetchCount !== null || $this->aGlobal !== null) {
                $this->channel->basic_qos($this->prefetchSize, $this->prefetchCount, $this->aGlobal);
            }

            foreach ($this->rabbitMqObjects as $rabbitMqObject) {
                $rabbitMqObject->init($this->channel);
            }
        }

        return $this->channel;
    }
}
