<?php

namespace RabbitMqModule\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use InvalidArgumentException;
use RabbitMqModule\Service\Connection\ConnectionFactoryInterface;
use RuntimeException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use RabbitMqModule\Options\Connection as ConnectionOptions;
use RabbitMqModule\Service\Connection;

/**
 * Class ConnectionFactory
 * @package RabbitMqModule\Service
 */
class ConnectionFactory extends AbstractFactory
{
    /**
     * @var array
     */
    protected $factoryMap = [
        'stream' => Connection\StreamConnectionFactory::class,
        'socket' => Connection\SocketConnectionFactory::class,
        'ssl' => Connection\SSLConnectionFactory::class,
        'lazy' => Connection\LazyConnectionFactory::class,
    ];

    /**
     * @return array
     */
    public function getFactoryMap()
    {
        return $this->factoryMap;
    }

    /**
     * @param array $factoryMap
     *
     * @return $this
     */
    public function setFactoryMap(array $factoryMap)
    {
        $this->factoryMap = $factoryMap;

        return $this;
    }

    /**
     * Get the class name of the options associated with this factory.
     *
     * @return string
     */
    public function getOptionsClass()
    {
        return ConnectionOptions::class;
    }

    /**
     * @param ContainerInterface $serviceLocator
     * @param string                  $type
     *
     * @return ConnectionFactoryInterface
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function getFactory(ContainerInterface $serviceLocator, $type)
    {
        $map = $this->getFactoryMap();
        if (!array_key_exists($type, $map)) {
            throw new InvalidArgumentException(sprintf('Options type "%s" not valid', $type));
        }

        $className = $map[$type];
        $factory = $serviceLocator->get($className);
        if (!$factory instanceof ConnectionFactoryInterface) {
            throw new RuntimeException(
                sprintf('Factory for type "%s" must be an instance of ConnectionFactoryInterface', $type)
            );
        }

        return $factory;
    }

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return \PhpAmqpLib\Channel\AbstractChannel
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {
        /* @var $options ConnectionOptions */
        $options = $this->getOptions($container, 'connection');
        $factory = $this->getFactory($container, $options->getType());

        return $factory->createConnection($options);
    }
}
