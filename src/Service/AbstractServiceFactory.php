<?php

namespace RabbitMqModule\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;

class AbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var string
     */
    protected $configKey = 'rabbitmq_module';

    /**
     * Determine if we can create a service with name.
     *
     * @param ContainerInterface $serviceLocator
     * @param                         $requestedName
     *
     * @return bool
     */
    public function canCreate(ContainerInterface $serviceLocator, $requestedName)
    {
        return false !== $this->getFactoryMapping($serviceLocator, $requestedName);
    }

    /**
     * @param ContainerInterface $serviceLocator
     * @param string                                       $name
     *
     * @return bool|array
     */
    private function getFactoryMapping(ContainerInterface $serviceLocator, $name)
    {
        $matches = [];

        $pattern = sprintf('/^%s\.(?P<serviceType>[a-z0-9_]+)\.(?P<serviceName>[a-z0-9_]+)$/', $this->configKey);
        if (!preg_match($pattern, $name, $matches)) {
            return false;
        }
        /** @var array $config */
        $config = $serviceLocator->get('Configuration');
        $serviceType = $matches['serviceType'];
        $serviceName = $matches['serviceName'];

        $moduleConfig = $config[$this->configKey];
        $factoryConfig = $moduleConfig['factories'];

        if (!isset($factoryConfig[$serviceType], $moduleConfig[$serviceType][$serviceName])) {
            return false;
        }

        return [
            'serviceType' => $serviceType,
            'serviceName' => $serviceName,
            'factoryClass' => $factoryConfig[$serviceType],
        ];
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
        $mappings = $this->getFactoryMapping($container, $requestedName);

        if (!$mappings)
        {
            throw new ServiceNotFoundException();
        }

        $factoryClass = $mappings['factoryClass'];
        /* @var $factory \RabbitMqModule\Service\AbstractFactory */
        $factory = new $factoryClass($mappings['serviceName']);

        return $factory($container, $requestedName, $options);
    }
}
