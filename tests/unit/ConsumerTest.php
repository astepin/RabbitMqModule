<?php

namespace RabbitMqModule;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use RabbitMqModule\Options\ExchangeBind;
use RabbitMqModule\Options\Queue as QueueOptions;
use RabbitMqModule\Options\Exchange as ExchangeOptions;

/**
 * Class ConsumerTest
 * @package RabbitMqModule
 */
class ConsumerTest extends \PHPUnit\Framework\TestCase
{
    public function testProperties()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /* @var AbstractConnection $connection */
        $consumer = new Consumer($connection);

        static::assertTrue($consumer->isAutoSetupFabricEnabled());
        static::assertEquals(0, $consumer->getIdleTimeout());

        $queueOptions = new QueueOptions();
        $exchangeOptions = new ExchangeOptions();

        $callback = function () {

        };

        $consumer->setConsumerTag('consumer-tag-test');
        $consumer->setCallback($callback);
        $consumer->setQueueOptions($queueOptions);
        $consumer->setExchangeOptions($exchangeOptions);
        $consumer->setAutoSetupFabricEnabled(false);
        $consumer->setIdleTimeout(5);

        static::assertSame($connection, $consumer->getConnection());
        static::assertSame($callback, $consumer->getCallback());
        static::assertSame($queueOptions, $consumer->getQueueOptions());
        static::assertSame($exchangeOptions, $consumer->getExchangeOptions());
        static::assertFalse($consumer->isAutoSetupFabricEnabled());
        static::assertEquals(5, $consumer->getIdleTimeout());
    }

    public function testSetupFabric()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queueOptions = new QueueOptions();
        $queueOptions->setName('foo');
        $exchangeOptions = new ExchangeOptions();

        $exchangeBindOptions = new ExchangeOptions();
        $exchangeBind = new ExchangeBind();
        $exchangeBind->setExchange($exchangeBindOptions);
        $exchangeOptions->setExchangeBinds([$exchangeBind]);

        /* @var AbstractConnection $connection */
        $consumer = new Consumer($connection, $channel);
        $consumer->setQueueOptions($queueOptions);
        $consumer->setExchangeOptions($exchangeOptions);

        $channel->expects(static::exactly(1))
            ->method('exchange_bind');
        $channel->expects(static::exactly(2))
            ->method('exchange_declare');
        $channel->expects(static::once())
            ->method('queue_declare');

        static::assertSame($consumer, $consumer->setupFabric());
    }

    public function testSetupFabricWithEmptyQueueName()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queueOptions = new QueueOptions();
        $exchangeOptions = new ExchangeOptions();
        $exchangeOptions->setDeclare(false);

        /* @var AbstractConnection $connection */
        $consumer = new Consumer($connection, $channel);
        $consumer->setQueueOptions($queueOptions);
        $consumer->setExchangeOptions($exchangeOptions);

        $channel->expects(static::never())
            ->method('exchange_bind');
        $channel->expects(static::never())
            ->method('exchange_declare');
        $channel->expects(static::never())
            ->method('queue_declare');

        static::assertSame($consumer, $consumer->setupFabric());
    }

    public function testSetupFabricWithoutQueueOptions()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exchangeOptions = new ExchangeOptions();
        $exchangeOptions->setDeclare(false);

        /* @var AbstractConnection $connection */
        $consumer = new Consumer($connection, $channel);
        $consumer->setExchangeOptions($exchangeOptions);

        $channel->expects(static::never())
            ->method('exchange_bind');
        $channel->expects(static::never())
            ->method('exchange_declare');
        $channel->expects(static::never())
            ->method('queue_declare');

        static::assertSame($consumer, $consumer->setupFabric());
    }

    public function testSetupFabricWithNoDeclareExchange()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exchangeOptions = new ExchangeOptions();
        $exchangeOptions->setDeclare(false);

        /* @var AbstractConnection $connection */
        $consumer = new Consumer($connection, $channel);
        $consumer->setExchangeOptions($exchangeOptions);

        $channel->expects(static::never())
            ->method('exchange_bind');
        $channel->expects(static::never())
            ->method('exchange_declare');
        $channel->expects(static::never())
            ->method('queue_declare');

        static::assertSame($consumer, $consumer->setupFabric());
    }

    /**
     * @dataProvider processMessageProvider
     * @param $response
     * @param $method
     * @param $paramsExpects
     */
    public function testProcessMessage($response, $method, $paramsExpects)
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var AMQPMessage $message */
        $message = static::getMockBuilder(AMQPMessage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message->delivery_info = [
            'channel' => $channel,
            'delivery_tag' => 'foo',
        ];

        /* @var AbstractConnection $connection */
        $consumer = new Consumer($connection, $channel);
        $consumer->setCallback(\Closure::fromCallable(function ($p1, $p2) use ($response, $message, $consumer) {
            $this->assertSame($message, $p1);
            $this->assertSame($consumer, $p2);
            return $response;
        })->bindTo($this));

        $expect = $channel->expects(static::once())
            ->method($method);
        call_user_func_array([$expect, 'with'], $paramsExpects);

        $consumer->processMessage($message);
    }

    public function testTestCallbackParameter()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $channel    = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var AMQPMessage $message */
        $message                = static::getMockBuilder(AMQPMessage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message->delivery_info = [
            'channel'      => $channel,
            'delivery_tag' => 'foo',
        ];

        /** @var ConsumerInterface|\PHPUnit_Framework_MockObject_MockObject|callable $callback */
        $callback = static::getMockBuilder(ConsumerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMockForAbstractClass();

        /* @var AbstractConnection $connection */
        $consumer = new Consumer($connection, $channel);
        $consumer->setCallback([$callback, 'execute']);

        $callback->expects(self::once())->method('execute')->with($message, $consumer);

        $consumer->processMessage($message);
    }

    public function testPurge()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $queueOptions = new QueueOptions();
        $queueOptions->setName('foo');

        $channel->expects(static::once())
            ->method('queue_purge')
            ->with(static::equalTo('foo'), static::equalTo(true));

        /* @var AbstractConnection $connection */
        $consumer = new Consumer($connection, $channel);
        $consumer->setQueueOptions($queueOptions);
        $consumer->purgeQueue();
    }

    public function testStart()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        /** @var AMQPChannel|\PHPUnit_Framework_MockObject_MockObject $channel */
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $exchangeOptions = new ExchangeOptions();
        $exchangeOptions->setName('foo');
        $queueOptions = new QueueOptions();
        $queueOptions->setName('foo');

        $callbacks = range(0, 2);
        $channel->callbacks = $callbacks;
        $channel->expects(static::exactly(count($callbacks)))
            ->method('wait')
            ->willReturnCallback(function () use ($channel) {
                array_shift($channel->callbacks);

                return true;
            });

        $channel->expects(static::once())
            ->method('basic_consume');
        $channel->expects(static::exactly(count($callbacks)))
            ->method('wait');

        /* @var AbstractConnection $connection */
        $consumer = new Consumer($connection, $channel);
        $consumer->setExchangeOptions($exchangeOptions);
        $consumer->setQueueOptions($queueOptions);
        $consumer->start();
    }

    public function testConsume()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        /** @var AMQPChannel|\PHPUnit_Framework_MockObject_MockObject $channel */
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $exchangeOptions = new ExchangeOptions();
        $exchangeOptions->setName('foo');
        $queueOptions = new QueueOptions();
        $queueOptions->setName('foo');

        $callbacks = range(0, 2);
        $channel->callbacks = $callbacks;
        $channel->expects(static::exactly(count($callbacks)))
            ->method('wait')
            ->willReturnCallback(function () use ($channel) {
                array_shift($channel->callbacks);

                return true;
            });

        $channel->expects(static::once())
            ->method('basic_consume');
        $channel->expects(static::exactly(count($callbacks)))
            ->method('wait');

        /* @var AbstractConnection $connection */
        $consumer = new Consumer($connection, $channel);
        $consumer->setExchangeOptions($exchangeOptions);
        $consumer->setQueueOptions($queueOptions);
        $consumer->consume();
    }

    public function testConsumeWithStop()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        /** @var AMQPChannel|\PHPUnit_Framework_MockObject_MockObject $channel */
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();

        /* @var AbstractConnection $connection */
        $consumer = new Consumer($connection, $channel);

        $exchangeOptions = new ExchangeOptions();
        $exchangeOptions->setName('foo');
        $queueOptions = new QueueOptions();
        $queueOptions->setName('foo');

        $callbacks = range(0, 2);
        $channel->callbacks = $callbacks;
        $channel->expects(static::atLeast(1))
            ->method('wait')
            ->willReturnCallback(function () use ($channel, $consumer) {
                array_shift($channel->callbacks);
                $consumer->forceStopConsumer();

                return true;
            });

        $channel->expects(static::once())
            ->method('basic_consume');
        $channel->expects(static::once())
            ->method('basic_cancel')
            ->willReturnCallback(function () use ($channel) {
                $channel->callbacks = [];

                return true;
            });
        $channel->expects(static::atLeast(1))
            ->method('wait');

        $consumer->setExchangeOptions($exchangeOptions);
        $consumer->setQueueOptions($queueOptions);
        $consumer->consume();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetCallbackWithInvalidValue()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();

        /* @var AbstractConnection $connection */
        $consumer = new Consumer($connection, $channel);

        $consumer->setCallback('string');
    }

    /**
     * @return array
     */
    public function processMessageProvider()
    {
        return [
            [
                ConsumerInterface::MSG_ACK,
                'basic_ack',
                [
                    static::equalTo('foo'),
                ],
            ],
            [
                ConsumerInterface::MSG_REJECT,
                'basic_reject',
                [
                    static::equalTo('foo'),
                    static::equalTo(false),
                ],
            ],
            [
                ConsumerInterface::MSG_REJECT_REQUEUE,
                'basic_reject',
                [
                    static::equalTo('foo'),
                    static::equalTo(true),
                ],
            ],
            [
                ConsumerInterface::MSG_SINGLE_NACK_REQUEUE,
                'basic_nack',
                [
                    static::equalTo('foo'),
                    static::equalTo(false),
                    static::equalTo(true),
                ],
            ],
        ];
    }
}
