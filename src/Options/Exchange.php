<?php

namespace RabbitMqModule\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Class Exchange
 * @package RabbitMqModule\Options
 */
class Exchange extends AbstractOptions
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var bool
     */
    protected $passive = false;
    /**
     * @var bool
     */
    protected $durable = true;
    /**
     * @var bool
     */
    protected $autoDelete = false;
    /**
     * @var bool
     */
    protected $internal = false;
    /**
     * @var bool
     */
    protected $noWait = false;
    /**
     * @var bool
     */
    protected $declare = true;
    /**
     * @var array
     */
    protected $arguments = [];
    /**
     * @var int
     */
    protected $ticket = 0;
    /**
     * @var ExchangeBind[]
     */
    protected $exchangeBinds = [];

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPassive()
    {
        return $this->passive;
    }

    /**
     * @param bool $passive
     *
     * @return $this
     */
    public function setPassive($passive)
    {
        $this->passive = $passive;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDurable()
    {
        return $this->durable;
    }

    /**
     * @param bool $durable
     *
     * @return $this
     */
    public function setDurable($durable)
    {
        $this->durable = $durable;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoDelete()
    {
        return $this->autoDelete;
    }

    /**
     * @param bool $autoDelete
     *
     * @return $this
     */
    public function setAutoDelete($autoDelete)
    {
        $this->autoDelete = $autoDelete;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInternal()
    {
        return $this->internal;
    }

    /**
     * @param bool $internal
     *
     * @return $this
     */
    public function setInternal($internal)
    {
        $this->internal = $internal;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNoWait()
    {
        return $this->noWait;
    }

    /**
     * @param bool $noWait
     *
     * @return $this
     */
    public function setNoWait($noWait)
    {
        $this->noWait = $noWait;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeclare()
    {
        return $this->declare;
    }

    /**
     * @param bool $declare
     *
     * @return $this
     */
    public function setDeclare($declare)
    {
        $this->declare = $declare;

        return $this;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     *
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return int
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param int $ticket
     *
     * @return $this
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * @return ExchangeBind[]
     */
    public function getExchangeBinds()
    {
        return $this->exchangeBinds;
    }

    /**
     * @param array|ExchangeBind[] $exchangeBinds
     *
     * @return $this
     */
    public function setExchangeBinds(array $exchangeBinds)
    {
        $this->exchangeBinds = [];
        foreach ($exchangeBinds as $bind) {
            $this->addExchangeBind($bind);
        }

        return $this;
    }

    /**
     * @param array|ExchangeBind $bind
     *
     * @return $this
     */
    public function addExchangeBind($bind)
    {
        if (is_array($bind)) {
            $bind = new ExchangeBind($bind);
        }
        if (!$bind instanceof ExchangeBind) {
            throw new \InvalidArgumentException('Invalid exchange bind options');
        }
        $this->exchangeBinds[] = $bind;

        return $this;
    }
}
