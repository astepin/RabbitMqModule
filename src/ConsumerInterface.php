<?php

namespace RabbitMqModule;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Interface ConsumerInterface.
 */
interface ConsumerInterface
{
    /**
     * Flag for message ack.
     */
    const MSG_ACK = 1;
    /**
     * Flag single for message nack and requeue.
     */
    const MSG_SINGLE_NACK_REQUEUE = 2;
    /**
     * Flag for reject and requeue.
     */
    const MSG_REJECT_REQUEUE = 0;
    /**
     * Flag for reject and drop.
     */
    const MSG_REJECT = -1;

    /**
     * @param AMQPMessage $message
     *
     * @param Consumer $consumer
     * @return mixed
     */
    public function execute(AMQPMessage $message, Consumer $consumer);
}
