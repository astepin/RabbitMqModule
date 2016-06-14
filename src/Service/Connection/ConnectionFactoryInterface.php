<?php

namespace RabbitMqModule\Service\Connection;

use RabbitMqModule\Options\Connection as ConnectionOptions;

/**
 * Interface ConnectionFactoryInterface
 * @package RabbitMqModule\Service\Connection
 */
interface ConnectionFactoryInterface
{
    /**
     * @param ConnectionOptions $options
     * @return mixed
     */
    public function createConnection(ConnectionOptions $options);
}
