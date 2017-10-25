<?php

namespace RabbitMqModule;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;

/**
 * Class BaseAmqpTest
 * @package RabbitMqModule
 */
class BaseAmqpTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $baseAmqp = static::getMockBuilder(BaseAmqp::class)
            ->setConstructorArgs([$connection])
            ->setMethods(['__destruct'])
            ->getMock();

        $baseAmqp->method('__destruct');

        $connection->expects(static::once())->method('channel')->willReturn($channel);

        /** @var BaseAmqp $baseAmqp */
        static::assertEquals($channel, $baseAmqp->getChannel());
    }

    public function testSetChannel()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var AMQPChannel $channel */
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $baseAmqp = static::getMockBuilder(BaseAmqp::class)
            ->setConstructorArgs([$connection])
            ->setMethods(['__destruct'])
            ->getMock();

        $baseAmqp->method('__destruct');

        /* @var BaseAmqp $baseAmqp */
        $baseAmqp->setChannel($channel);
        static::assertEquals($channel, $baseAmqp->getChannel());
    }

    /**
     *
     * @param $name
     * @return \ReflectionMethod
     */
    protected static function getMethod($name)
    {
        $class = new \ReflectionClass(BaseAmqp::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function testDestruct()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var AMQPChannel|\PHPUnit_Framework_MockObject_MockObject $channel */
        $channel = static::getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $baseAmqp = static::getMockBuilder(BaseAmqp::class)
            ->setConstructorArgs([$connection])
            ->setMethods(null)
            ->getMock();

        $connection->expects(static::once())->method('isConnected')->willReturn(true);
        $connection->expects(static::once())->method('close');

        $channel->expects(static::once())->method('close');

        /* @var BaseAmqp $baseAmqp */
        $baseAmqp->setChannel($channel);
        $baseAmqp->__destruct();
    }

    public function testReconnect()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $baseAmqp = static::getMockBuilder(BaseAmqp::class)
            ->setConstructorArgs([$connection])
            ->setMethods(['__destruct'])
            ->getMock();

        $connection->expects(static::once())->method('isConnected')->willReturn(true);
        $connection->expects(static::once())->method('reconnect');

        /** @var BaseAmqp $baseAmqp */
        static::assertEquals($baseAmqp, $baseAmqp->reconnect());
    }

    public function testReconnectWhenConnected()
    {
        $connection = static::getMockBuilder(AbstractConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $baseAmqp = static::getMockBuilder(BaseAmqp::class)
            ->setConstructorArgs([$connection])
            ->setMethods(['__destruct'])
            ->getMock();

        $connection->expects(static::once())->method('isConnected')->willReturn(false);
        $connection->expects(static::never())->method('reconnect');

        /** @var BaseAmqp $baseAmqp */
        static::assertEquals($baseAmqp, $baseAmqp->reconnect());
    }
}
