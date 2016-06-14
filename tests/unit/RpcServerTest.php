<?php

namespace RabbitMqModule;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Zend\Serializer\Serializer;

/**
 * Class RpcServerTest
 * @package RabbitMqModule
 */
class RpcServerTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessMessage()
    {
        $response = 'ciao';

        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $message = new AMQPMessage('request', [
            'reply_to' => 'foo',
            'correlation_id' => 'bar',
        ]);

        $message->delivery_info = [
            'channel' => $channel,
            'delivery_tag' => 'foo',
        ];

        /* @var AbstractConnection $connection */
        $rpcServer = new RpcServer($connection, $channel);
        $rpcServer->setCallback(function () use ($response) {
            return $response;
        });

        $channel->expects(static::once())->method('basic_publish')
            ->with(
                static::callback(function ($a) use ($response) {
                    return $a instanceof AMQPMessage
                        && $a->body === $response
                        && $a->get('correlation_id') === 'bar'
                        && $a->get('content_type') === 'text/plain';
                }),
                static::equalTo(''),
                static::equalTo('foo')
            );

        $rpcServer->processMessage($message);
    }

    public function testProcessMessageWithSerializer()
    {
        $response = ['response' => 'ciao'];

        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $message = new AMQPMessage('request', [
            'reply_to' => 'foo',
            'correlation_id' => 'bar',
        ]);

        $message->delivery_info = [
            'channel' => $channel,
            'delivery_tag' => 'foo',
        ];

        $serializer = Serializer::factory('json');

        /* @var AbstractConnection $connection */
        $rpcServer = new RpcServer($connection, $channel);
        $rpcServer->setSerializer($serializer);
        $rpcServer->setCallback(function () use ($response) {
            return $response;
        });

        $channel->expects(static::once())->method('basic_publish')
            ->with(
                static::callback(function ($a) use ($response) {
                    return $a instanceof AMQPMessage
                    && $a->body === '{"response":"ciao"}'
                    && $a->get('correlation_id') === 'bar'
                    && $a->get('content_type') === 'text/plain';
                }),
                static::equalTo(''),
                static::equalTo('foo')
            );

        $rpcServer->processMessage($message);
    }
}
