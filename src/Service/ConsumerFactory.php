<?php

namespace RabbitMqModule\Service;

use RabbitMqModule\Consumer;
use RabbitMqModule\ConsumerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use RabbitMqModule\Options\Consumer as Options;
use InvalidArgumentException;

class ConsumerFactory extends AbstractFactory
{
    /**
     * Get the class name of the options associated with this factory.
     *
     * @return string
     */
    public function getOptionsClass()
    {
        return Options::class;
    }

    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return Consumer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $options Options */
        $options = $this->getOptions($serviceLocator, 'consumer');

        return $this->createConsumer($serviceLocator, $options);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param Options                 $options
     *
     * @throws InvalidArgumentException
     *
     * @return Consumer
     */
    protected function createConsumer(ServiceLocatorInterface $serviceLocator, Options $options)
    {
        $callback = $options->getCallback();
        if (is_string($callback)) {
            $callback = $serviceLocator->get($callback);
        }
        if ($callback instanceof ConsumerInterface) {
            $callback = [$callback, 'execute'];
        }
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Invalid callback provided');
        }

        /** @var \PhpAmqpLib\Connection\AbstractConnection $connection */
        $connection = $serviceLocator->get(sprintf('%s.connection.%s', $this->configKey, $options->getConnection()));
        $consumer = new Consumer($connection);
        $consumer->setQueueOptions($options->getQueue());
        $consumer->setExchangeOptions($options->getExchange());
        $consumer->setConsumerTag($options->getConsumerTag() ?: sprintf('PHPPROCESS_%s_%s', gethostname(), getmypid()));
        $consumer->setAutoSetupFabricEnabled($options->isAutoSetupFabricEnabled());
        $consumer->setCallback($callback);
        $consumer->setIdleTimeout($options->getIdleTimeout());

        if ($options->getQos()) {
            $consumer->setQosOptions(
                $options->getQos()->getPrefetchSize(),
                $options->getQos()->getPrefetchCount()
            );
        }

        return $consumer;
    }
}
