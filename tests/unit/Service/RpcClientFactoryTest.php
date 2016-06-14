<?php

namespace RabbitMqModule\Service;

use PhpAmqpLib\Connection\AbstractConnection;
use RabbitMqModule\RpcClient;
use Zend\Serializer\Adapter\AdapterInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class RpcClientFactoryTest
 * @package RabbitMqModule\Service
 */
class RpcClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $factory = new RpcClientFactory('foo');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Configuration',
            [
                'rabbitmq_module' => [
                    'rpc_client' => [
                        'foo' => [
                            'connection' => 'foo',
                            'serializer' => 'PhpSerialize',
                        ],
                    ],
                ],
            ]
        );

        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $serviceManager->setService('rabbitmq_module.connection.foo', $connection);

        /** @var RpcClient $service */
        $service = $factory($serviceManager, 'temp');

        static::assertInstanceOf(RpcClient::class, $service);
        static::assertInstanceOf(AdapterInterface::class, $service->getSerializer());
    }
}
