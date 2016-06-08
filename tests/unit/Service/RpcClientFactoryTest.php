<?php

namespace RabbitMqModule\Service;

use RabbitMqModule\RpcClient;
use Zend\ServiceManager\ServiceManager;

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

        $connection = static::getMockBuilder('PhpAmqpLib\\Connection\\AbstractConnection')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $serviceManager->setService('rabbitmq_module.connection.foo', $connection);

        /** @var RpcClient $service */
        $service = $factory($serviceManager, 'temp');

        static::assertInstanceOf('RabbitMqModule\\RpcClient', $service);
        static::assertInstanceOf('Zend\\Serializer\\Adapter\\AdapterInterface', $service->getSerializer());
    }
}
