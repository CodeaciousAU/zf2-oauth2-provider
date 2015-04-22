<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider\Config;

use Codeacious\OAuth2Provider\Exception\ConfigurationException;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Filter\Word\UnderscoreToCamelCase;
use Zend\Filter\Word\CamelCaseToUnderscore;

abstract class Factory implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $services;


    /**
     * @param ServiceLocatorInterface $services
     */
    public function __construct(ServiceLocatorInterface $services=null)
    {
        $this->services = $services;
    }

    /**
     * @param ServiceLocatorInterface $services
     * @return void
     */
    public function setServiceLocator(ServiceLocatorInterface $services)
    {
        $this->services = $services;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->services;
    }

    /**
     * @param mixed $config
     * @param mixed $name
     * @return object
     *
     * @throws ConfigurationException
     */
    public abstract function create($config, $name=null);

    /**
     * @param string $reference
     * @return object|null
     */
    protected function resolveReference($reference)
    {
        if ($this->services && is_string($reference) && $this->services->has($reference))
            return $this->services->get($reference);
        if (is_string($reference) && class_exists($reference))
            return new $reference();
        if (is_callable($reference))
            return call_user_func($reference, $this->services);
        if (is_object($reference))
            return $reference;

        return null;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function underscoreToCamelCase($value)
    {
        $filter = new UnderscoreToCamelCase();
        return $filter->filter($value);
    }

    /**
     * @param string $value
     * @return string
     */
    protected function camelCaseToUnderscore($value)
    {
        if (strpos($value, '\\') !== false)
        {
            $parts = explode('\\', $value);
            $value = array_pop($parts);
        }
        $filter = new CamelCaseToUnderscore();
        return $filter->filter($value);
    }
}