<?php

namespace RabbitMqModule\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use PhpAmqpLib\Connection\AbstractConnection;
use RabbitMqModule\RpcClient;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use RabbitMqModule\Options\RpcClient as Options;
use InvalidArgumentException;

/**
 * Class RpcClientFactory
 * @package RabbitMqModule\Service
 */
class RpcClientFactory extends AbstractFactory
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
     * @return RpcClient
     *
     * @throws InvalidArgumentException
     */
    protected function createClient(ContainerInterface $serviceLocator, Options $options)
    {
        /** @var AbstractConnection $connection */
        $connection = $serviceLocator->get(sprintf('%s.connection.%s', $this->configKey, $options->getConnection()));
        $producer = new RpcClient($connection);
        $producer->setSerializer($options->getSerializer());

        return $producer;
    }

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return RpcClient
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var $options Options */
        $options = $this->getOptions($container, 'rpc_client');

        return $this->createClient($container, $options);
    }
}
