<?php

namespace RabbitMqModule\Service;

use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class AbstractServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->serviceManager = new ServiceManager();
        $this->serviceManager->setService(
            'Configuration',
            [
                'rabbitmq' => [
                    'connection' => [
                        'default' => [],
                    ],
                    'producer' => [
                        'foo' => [
                            'exchange' => [],
                        ],
                    ],
                    'foo' => [
                        'bar' => [

                        ],
                    ],
                ],
                'rabbitmq_factories' => [
                    'foo' => 'fooFactory',
                    'producer' => 'RabbitMqModuleTest\\Service\\ServiceFactoryMock',
                ],
            ]
        );
    }

    public function testCanCreateServiceWithName()
    {
        $sm = $this->serviceManager;
        $factory = new AbstractServiceFactory();
        static::assertTrue($factory->canCreateServiceWithName($sm, 'rabbitmq.foo.bar', 'rabbitmq.foo.bar'));
        static::assertFalse($factory->canCreateServiceWithName($sm, 'rabbitmq.foo.bar', 'rabbitmq.foo.bar2'));
    }

    public function testCreateServiceWithName()
    {
        $connection = static::getMockBuilder('PhpAmqpLib\\Connection\\AbstractConnection')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sm = $this->serviceManager;
        $sm->setService('rabbitmq.connection.default', $connection);
        $factory = new AbstractServiceFactory();
        static::assertTrue(
            $factory->createServiceWithName($sm, 'rabbitmq.producer.foo', 'rabbitmq.producer.foo')
        );
    }
}
