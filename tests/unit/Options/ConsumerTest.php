<?php

namespace RabbitMqModule\Options;

/**
 * Class ConsumerTest
 * @package RabbitMqModule\Options
 */
class ConsumerTest extends \PHPUnit\Framework\TestCase
{
    public function testOptions()
    {
        $configuration = [
            'connection' => 'connection-name',
            'exchange' => [
                'name' => 'exchange-name',
            ],
            'queue' => [
                'name' => 'queue-name',
            ],
            'callback' => 'callback-name',
            'idle_timeout' => 6,
            'qos' => [

            ],
            'auto_setup_fabric_enabled' => false,
            'consumer_tag' => 'test-tag',
            'signals_enabled' => true,
        ];
        $options = new Consumer();
        $options->setFromArray($configuration);

        static::assertEquals($configuration['connection'], $options->getConnection());
        static::assertInstanceOf('RabbitMqModule\\Options\\Exchange', $options->getExchange());
        static::assertInstanceOf('RabbitMqModule\\Options\\Queue', $options->getQueue());
        static::assertEquals($configuration['callback'], $options->getCallback());
        static::assertEquals($configuration['idle_timeout'], $options->getIdleTimeout());
        static::assertInstanceOf('RabbitMqModule\\Options\\Qos', $options->getQos());
        static::assertEquals(6, $options->getIdleTimeout());
        static::assertEquals($configuration['auto_setup_fabric_enabled'], $options->isAutoSetupFabricEnabled());
        static::assertEquals('test-tag', $options->getConsumerTag());
        static::assertEquals($configuration['signals_enabled'], $options->isSignalsEnabled());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetQueueInvalidValue()
    {
        $options = new Consumer();
        $options->setQueue('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetExchangeInvalidValue()
    {
        $options = new Consumer();
        $options->setExchange('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetWosInvalidValue()
    {
        $options = new Consumer();
        $options->setQos('');
    }
}
