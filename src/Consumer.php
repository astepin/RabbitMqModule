<?php

namespace RabbitMqModule;

use PhpAmqpLib\Message\AMQPMessage;

class Consumer extends BaseConsumer
{
    /**
     * Purge the queue.
     *
     * @return $this
     */
    public function purgeQueue()
    {
        $this->getChannel()->queue_purge($this->getOptions()->getQueue()->getName(), true);

        return $this;
    }

    public function processMessage(AMQPMessage $msg)
    {
        $processFlag = call_user_func($this->getOptions()->getCallback(), $msg);
        $this->handleProcessMessage($msg, $processFlag);
    }

    protected function handleProcessMessage(AMQPMessage $msg, $processFlag)
    {
        $channel = $msg->delivery_info['channel'];
        /** @var string $deliveryTag */
        $deliveryTag = $msg->delivery_info['delivery_tag'];
        if ($processFlag === ConsumerInterface::MSG_REJECT_REQUEUE || false === $processFlag) {
            // Reject and requeue message to RabbitMQ
            $channel->basic_reject($deliveryTag, true);
        } elseif ($processFlag === ConsumerInterface::MSG_SINGLE_NACK_REQUEUE) {
            // NACK and requeue message to RabbitMQ
            $channel->basic_nack($deliveryTag, false, true);
        } elseif ($processFlag === ConsumerInterface::MSG_REJECT) {
            // Reject and drop
            $channel->basic_reject($deliveryTag, false);
        } else {
            // Remove message from queue only if callback return not false
            $channel->basic_ack($deliveryTag);
        }
        $this->maybeStopConsumer();
    }
}