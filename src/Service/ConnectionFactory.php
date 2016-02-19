<?php

namespace RabbitMqModule\Service;

use InvalidArgumentException;
use RabbitMqModule\Service\Connection\ConnectionFactoryInterface;
use RuntimeException;
use Zend\ServiceManager\ServiceLocatorInterface;
use RabbitMqModule\Options\Connection as ConnectionOptions;
use RabbitMqModule\Service\Connection;

class ConnectionFactory extends AbstractFactory
{
    /**
     * @var array
     */
    protected $factoryMap = [
        'stream' => Connection\StreamConnectionFactory::class,
        'socket' => Connection\SocketConnectionFactory::class,
        'ssl' => Connection\SSLConnectionFactory::class,
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
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $options ConnectionOptions */
        $options = $this->getOptions($serviceLocator, 'connection');
        $factory = $this->getFactory($serviceLocator, $options->getType());

        return $factory->createConnection($options);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string                  $type
     *
     * @return ConnectionFactoryInterface
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function getFactory(ServiceLocatorInterface $serviceLocator, $type)
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
}
