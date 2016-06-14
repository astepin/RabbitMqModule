<?php

namespace RabbitMqModule\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use RabbitMqModule\ConsumerInterface;
use RabbitMqModule\RpcServer;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use RabbitMqModule\Options\RpcServer as Options;
use InvalidArgumentException;

/**
 * Class RpcServerFactory
 * @package RabbitMqModule\Service
 */
class RpcServerFactory extends AbstractFactory
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
     * @return RpcServer
     */
    protected function createServer(ContainerInterface $serviceLocator, Options $options)
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
        $server = new RpcServer($connection);
        $server->setQueueOptions($options->getQueue());
        $server->setExchangeOptions($options->getExchange());
        $server->setConsumerTag($options->getConsumerTag() ?: sprintf('PHPPROCESS_%s_%s', gethostname(), getmypid()));
        $server->setAutoSetupFabricEnabled($options->isAutoSetupFabricEnabled());
        $server->setCallback($callback);
        $server->setIdleTimeout($options->getIdleTimeout());
        $server->setSerializer($options->getSerializer());

        if ($options->getQos()) {
            $server->setQosOptions(
                $options->getQos()->getPrefetchSize(),
                $options->getQos()->getPrefetchCount()
            );
        }

        return $server;
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
        $options = $this->getOptions($container, 'rpc_server');

        return $this->createServer($container, $options);
    }
}
