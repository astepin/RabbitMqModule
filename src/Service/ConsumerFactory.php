<?php

namespace RabbitMqModule\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use RabbitMqModule\Consumer;
use RabbitMqModule\ConsumerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
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
     * @param ContainerInterface $serviceLocator
     * @param Options                 $options
     *
     * @throws InvalidArgumentException
     *
     * @return Consumer
     */
    protected function createConsumer(ContainerInterface $serviceLocator, Options $options)
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

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {
        /* @var $options Options */
        $options = $this->getOptions($container, 'consumer');

        return $this->createConsumer($container, $options);
    }
}
