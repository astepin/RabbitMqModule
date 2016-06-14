<?php

namespace RabbitMqModule\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use PhpAmqpLib\Connection\AbstractConnection;
use RabbitMqModule\Producer;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use RabbitMqModule\Options\Producer as Options;
use InvalidArgumentException;

/**
 * Class ProducerFactory
 * @package RabbitMqModule\Service
 */
class ProducerFactory extends AbstractFactory
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
     * @return Producer
     *
     * @throws InvalidArgumentException
     */
    protected function createProducer(ContainerInterface $serviceLocator, Options $options)
    {
        /** @var AbstractConnection $connection */
        $connection = $serviceLocator->get(sprintf('%s.connection.%s', $this->configKey, $options->getConnection()));
        $producer = new Producer($connection);
        $producer->setExchangeOptions($options->getExchange());
        if ($options->getQueue()) {
            $producer->setQueueOptions($options->getQueue());
        }
        $producer->setAutoSetupFabricEnabled($options->isAutoSetupFabricEnabled());

        return $producer;
    }

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return Producer
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {
        /* @var $options Options */
        $options = $this->getOptions($container, 'producer');

        return $this->createProducer($container, $options);
    }
}
