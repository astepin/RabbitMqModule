<?php

namespace RabbitMqModule\Service;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use RabbitMqModule\ConsumerInterface;
use RabbitMqModule\RpcServer;
use Zend\ServiceManager\ServiceManager;

/**
 * Class RpcServerFactoryTest
 * @package RabbitMqModule\Service
 */
class RpcServerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $factory = new RpcServerFactory('foo');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Configuration',
            [
                'rabbitmq_module' => [
                    'rpc_server' => [
                        'foo' => [
                            'connection' => 'foo',
                            'exchange' => [

                            ],
                            'queue' => [
                                'name' => 'bar',
                            ],
                            'qos' => [
                                'prefetch_size' => 99,
                                'prefetch_count' => 89,
                            ],
                            'callback' => 'callback-service',
                            'idle_timeout' => 5,
                            'serializer' => 'PhpSerialize',
                        ],
                    ],
                ],
            ]
        );

        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['channel'])
            ->getMockForAbstractClass();
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callback = static::getMockBuilder(ConsumerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMockForAbstractClass();
        $connection->expects(static::once())
            ->method('channel')
            ->will(static::returnValue($channel));
        $channel->expects(static::once())
            ->method('basic_qos')
            ->with(
                static::equalTo(99),
                static::equalTo(89),
                static::equalTo(false)
            );
        $serviceManager->setService('rabbitmq_module.connection.foo', $connection);
        $serviceManager->setService('callback-service', $callback);

        /** @var RpcServer $service */
        $service = $factory($serviceManager,'rpc');

        static::assertInstanceOf('RabbitMqModule\\RpcServer', $service);
        static::assertInstanceOf('RabbitMqModule\\Options\\Queue', $service->getQueueOptions());
        static::assertInstanceOf('RabbitMqModule\\Options\\Exchange', $service->getExchangeOptions());
        static::assertNotEmpty($service->getConsumerTag());
        static::assertTrue(is_callable($service->getCallback()));
        static::assertEquals(5, $service->getIdleTimeout());
        static::assertInstanceOf('Zend\\Serializer\\Adapter\\AdapterInterface', $service->getSerializer());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateServiceWithInvalidCallback()
    {
        $factory = new RpcServerFactory('foo');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Configuration',
            [
                'rabbitmq_module' => [
                    'rpc_server' => [
                        'foo' => [
                            'connection' => 'foo',
                            'exchange' => [

                            ],
                            'queue' => [
                                'name' => 'bar',
                            ],
                            'qos' => [
                                'prefetch_size' => 99,
                                'prefetch_count' => 89,
                            ],
                            'idle_timeout' => 5,
                        ],
                    ],
                ],
            ]
        );

        $factory($serviceManager, 'tmp');
    }
}
