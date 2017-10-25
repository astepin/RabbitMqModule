<?php

namespace RabbitMqModule\Controller\Factory;

/**
 * Class ConsumerControllerFactoryTest
 * @package RabbitMqModule\Controller\Factory
 */
class ConsumerControllerFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testFactory()
    {
        $serviceLocator = static::getMockBuilder('Zend\ServiceManager\ServiceLocatorInterface')
            ->getMock();
        $pluginManager = static::getMockBuilder('Zend\ServiceManager\AbstractPluginManager')
            ->disableOriginalConstructor()
            ->getMock();

        $pluginManager->method('getServiceLocator')->willReturn($serviceLocator);

        $factory = new ConsumerControllerFactory();
        $controller = $factory($pluginManager,'controller');

        static::assertInstanceOf('RabbitMqModule\Controller\ConsumerController', $controller);
    }
}
