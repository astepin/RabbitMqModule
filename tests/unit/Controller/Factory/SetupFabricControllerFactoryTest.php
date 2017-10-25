<?php

namespace RabbitMqModule\Controller\Factory;

/**
 * Class SetupFabricControllerFactoryTest
 * @package RabbitMqModule\Controller\Factory
 */
class SetupFabricControllerFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testFactory()
    {
        $serviceLocator = static::getMockBuilder('Zend\ServiceManager\ServiceLocatorInterface')
            ->getMock();
        $pluginManager = static::getMockBuilder('Zend\ServiceManager\AbstractPluginManager')
            ->disableOriginalConstructor()
            ->getMock();

        $pluginManager->method('getServiceLocator')->willReturn($serviceLocator);

        $factory = new SetupFabricControllerFactory();
        $controller = $factory($pluginManager, 'controller');

        static::assertInstanceOf('RabbitMqModule\Controller\SetupFabricController', $controller);
    }
}
