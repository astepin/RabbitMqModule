<?php

namespace RabbitMqModule\Controller;

use Zend\Console\ColorInterface;

/**
 * Class StdInProducerController
 *
 * @package RabbitMqModule\Controller
 */
class StdInProducerController extends AbstractConsoleController
{
    /**
     * Default action if none provided
     *
     * @return \Zend\Console\Response
     */
    public function indexAction()
    {
        /** @var \Zend\Console\Request $request */
        $request = $this->getRequest();
        /** @var \Zend\Console\Response $response */
        $response = $this->getResponse();

        $producerName = $request->getParam('name');
        $route = $request->getParam('route', '');
        $msg = $request->getParam('msg');

        $serviceName = sprintf('rabbitmq_module.producer.%s', $producerName);

        if (!$this->container->has($serviceName)) {
            $this->getConsole()->writeLine(
                sprintf('No producer with name "%s" found', $producerName),
                ColorInterface::RED
            );
            $response->setErrorLevel(1);

            return $response;
        }

        /** @var \RabbitMqModule\Producer $producer */
        $producer = $this->container->get($serviceName);
        $producer->publish($msg, $route);

        return $response;
    }
}
