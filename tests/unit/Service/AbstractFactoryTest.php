<?php

namespace RabbitMqModule\Service;

use Interop\Container\ContainerInterface;
use RabbitMqModule\Service\AbstractFactory as ModuleAbstractFactory;
use Zend\Stdlib\ArrayObject;

/**
 * Class AbstractFactoryTest
 * @package RabbitMqModule\Service
 */
class AbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOptions()
    {
        $configuration = [
            'rabbitmq_module' => [
                'default-key' => [
                    'default-name' => [
                        'opt2' => 'value2',
                    ],
                    'name1' => [
                        'opt1' => 'value1',
                    ],
                ],
            ],
        ];

        /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject $serviceLocator */
        $serviceLocator = static::getMockBuilder(ContainerInterface::class)
            ->setMethods(['get', 'has'])
            ->getMock();
        $factory = static::getMockBuilder(ModuleAbstractFactory::class)
            ->setConstructorArgs(['default-name'])
            ->setMethods(['getOptionsClass', '__invoke'])
            ->getMock();

        $serviceLocator->method('get')->willReturn($configuration);
        $serviceLocator->method('has')->willReturn(true);
        $factory->method('getOptionsClass')->willReturn('ArrayObject');

        /* @var ModuleAbstractFactory $factory */
        /** @var ArrayObject $ret */
        $ret = $factory->getOptions($serviceLocator, 'default-key');

        static::assertInstanceOf('ArrayObject', $ret);
        static::assertEquals($configuration['rabbitmq_module']['default-key']['default-name'], $ret->getArrayCopy());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetInvalidOptions()
    {
        $configuration = [
            'rabbitmq_module' => [
                'default-key' => [
                    'default-name' => [
                        'opt2' => 'value2',
                    ],
                    'name1' => [
                        'opt1' => 'value1',
                    ],
                ],
            ],
        ];

        /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject $serviceLocator */
        $serviceLocator = static::getMockBuilder(ContainerInterface::class)
            ->setMethods(['get', 'has'])
            ->getMock();
        $factory = static::getMockBuilder(ModuleAbstractFactory::class)
            ->setConstructorArgs(['default-name'])
            ->setMethods(['getOptionsClass', '__invoke'])
            ->getMock();

        $serviceLocator->method('get')->willReturn($configuration);

        /* @var AbstractFactory $factory */
        $factory->getOptions($serviceLocator, 'default-key', 'invalid-key');
    }
}
