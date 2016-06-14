<?php

namespace RabbitMqModule\Service;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use RabbitMqModule\Consumer;
use RabbitMqModule\ConsumerInterface;
use RabbitMqModule\Options\Exchange;
use RabbitMqModule\Options\Queue;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ConsumerFactoryTest
 * @package RabbitMqModule\Service
 */
class ConsumerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $factory = new ConsumerFactory('foo');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Configuration',
            [
                'rabbitmq_module' => [
                    'consumer' => [
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

        /** @var Consumer $service */
        $service = $factory($serviceManager, 'name');

        static::assertInstanceOf(Consumer::class, $service);
        static::assertInstanceOf(Queue::class, $service->getQueueOptions());
        static::assertInstanceOf(Exchange::class, $service->getExchangeOptions());
        static::assertNotEmpty($service->getConsumerTag());
        static::assertTrue(is_callable($service->getCallback()));
        static::assertEquals(5, $service->getIdleTimeout());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateServiceWithInvalidCallback()
    {
        $factory = new ConsumerFactory('foo');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Configuration',
            [
                'rabbitmq_module' => [
                    'consumer' => [
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

        $factory($serviceManager, 'consumer');
    }
}
