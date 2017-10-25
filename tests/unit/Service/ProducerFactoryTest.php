<?php

namespace RabbitMqModule\Service;

use PhpAmqpLib\Connection\AbstractConnection;
use RabbitMqModule\Producer;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ProducerFactoryTest
 * @package RabbitMqModule\Service
 */
class ProducerFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateService()
    {
        $factory = new ProducerFactory('foo');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Configuration',
            [
                'rabbitmq_module' => [
                    'producer' => [
                        'foo' => [
                            'connection' => 'foo',
                            'exchange' => [
                                'name' => 'exchange-name',
                            ],
                            'queue' => [
                                'name' => 'queue-name',
                            ],
                            'auto_setup_fabric_enabled' => false,
                        ],
                    ],
                ],
            ]
        );

        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $serviceManager->setService(
            'rabbitmq_module.connection.foo',
            $connection
        );

        /** @var Producer $service */
        $service = $factory($serviceManager, 'producer');

        static::assertInstanceOf(Producer::class, $service);
        static::assertSame($connection, $service->getConnection());
        static::assertEquals('exchange-name', $service->getExchangeOptions()->getName());
        static::assertEquals('queue-name', $service->getQueueOptions()->getName());
        static::assertFalse($service->isAutoSetupFabricEnabled());
    }
}
