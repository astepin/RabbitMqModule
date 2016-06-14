<?php

namespace RabbitMqModule\Service;

use PhpAmqpLib\Connection\AbstractConnection;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AbstractServiceFactoryTest
 * @package RabbitMqModule\Service
 */
class AbstractServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->serviceManager = new ServiceManager();
        $this->serviceManager->setService(
            'Configuration',
            [
                'rabbitmq_module' => [
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
                    'factories' => [
                        'foo' => 'fooFactory',
                        'producer' => ServiceFactoryMock::class,
                    ],
                ],
            ]
        );
    }

    public function testCanCreateServiceWithName()
    {
        $sm = $this->serviceManager;
        $factory = new AbstractServiceFactory();
        static::assertTrue($factory->canCreate($sm, 'rabbitmq_module.foo.bar'));
        static::assertFalse($factory->canCreate($sm, 'rabbitmq_module.foo.bar2'));
    }

    public function testCreateServiceWithName()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sm = $this->serviceManager;
        $sm->setService('rabbitmq_module.connection.default', $connection);
        $factory = new AbstractServiceFactory();
        static::assertTrue(
            $factory($sm, 'rabbitmq_module.producer.foo')
        );
    }

    /**
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testCreateServiceUnknown()
    {
        $sm = $this->serviceManager;
        $factory = new AbstractServiceFactory();
        static::assertTrue(
            $factory($sm, 'rabbitmq_module.unknown-key.foo')
        );
    }
}
