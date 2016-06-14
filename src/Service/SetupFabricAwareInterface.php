<?php

namespace RabbitMqModule\Service;

/**
 * Interface SetupFabricAwareInterface
 * @package RabbitMqModule\Service
 */
interface SetupFabricAwareInterface
{
    /**
     * @return $this
     */
    public function setupFabric();
}
