<?php

namespace RabbitMqModule\Controller\Factory;

/**
 * Class RpcServerControllerFactoryTest
 * @package RabbitMqModule\Controller\Factory
 */
class RpcServerControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceLocator = static::getMockBuilder('Zend\ServiceManager\ServiceLocatorInterface')
            ->getMock();
        $pluginManager = static::getMockBuilder('Zend\ServiceManager\AbstractPluginManager')
            ->disableOriginalConstructor()
            ->getMock();

        $pluginManager->method('getServiceLocator')->willReturn($serviceLocator);

        $factory = new RpcServerControllerFactory();
        $controller = $factory($pluginManager, 'controller');

        static::assertInstanceOf('RabbitMqModule\Controller\RpcServerController', $controller);
    }
}
