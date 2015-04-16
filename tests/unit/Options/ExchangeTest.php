<?php

namespace RabbitMqModuleTest\Options;

use RabbitMqModule\Options\Exchange;

class ExchangeTest extends \PHPUnit_Framework_TestCase
{
    public function testOptions()
    {
        $configuration = [
            'name' => 'test-name',
            'type' => 'type-name',
            'passive' => true,
            'durable' => true,
            'auto_delete' => false,
            'internal' => true,
            'no_wait' => true,
            'ticket' => 1,
            'declare' => true,
            'arguments' => [
                'argument1' => 'value1'
            ]
        ];
        $options = new Exchange();
        $options->setFromArray($configuration);

        static::assertEquals($configuration['name'], $options->getName());
        static::assertEquals($configuration['type'], $options->getType());
        static::assertEquals($configuration['passive'], $options->isPassive());
        static::assertEquals($configuration['durable'], $options->isDurable());
        static::assertEquals($configuration['auto_delete'], $options->isAutoDelete());
        static::assertEquals($configuration['internal'], $options->isInternal());
        static::assertEquals($configuration['no_wait'], $options->isNoWait());
        static::assertEquals($configuration['ticket'], $options->getTicket());
        static::assertEquals($configuration['declare'], $options->isDeclare());
        static::assertEquals($configuration['arguments'], $options->getArguments());
    }
}
