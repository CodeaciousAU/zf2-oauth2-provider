<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider;

use Codeacious\OAuth2Provider\Exception\ConfigurationException;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProviderAbstractFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $serviceConfigKey;


    /**
     * @param string $serviceConfigKey
     */
    public function __construct($serviceConfigKey = self::DEFAULT_SERVICE_CONFIG_KEY)
    {
        $this->serviceConfigKey = $serviceConfigKey;
    }

    /**
     * @param ServiceLocatorInterface $services
     * @param string $name
     * @param string $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name,
                                             $requestedName)
    {
        $config = $this->getConfig($services);
        return (isset($config[$requestedName]) && is_array($config[$requestedName]));
    }

    /**
     * @param ServiceLocatorInterface $services
     * @param string $name
     * @param string $requestedName
     * @return Provider
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $config = $this->getConfig($services);
        $config = $config[$requestedName];

        /* @var $serverFactory Config\Factory */
        $serverFactory = $services->get('Codeacious\OAuth2Provider\Config\ServerFactory');
        $server = $serverFactory->create($config, $requestedName);

        $class = self::DEFAULT_PROVIDER_CLASS;
        if (isset($config['class']))
        {
            $class = $config['class'];
            if (!class_exists($class))
                throw new ConfigurationException('Provider class "'.$class.'" not found');
        }
        /* @var $provider Provider */
        $provider = new $class($server, $services->get('Request'));
        return $provider;
    }

    /**
     * @param ServiceLocatorInterface $services
     * @return array
     */
    private function getConfig(ServiceLocatorInterface $services)
    {
        if ($this->config === null)
        {
            $this->config = array();
            if ($services->has('Config'))
            {
                $config = $services->get('Config');
                if (isset($config[$this->serviceConfigKey])
                    && is_array($config[$this->serviceConfigKey]))
                {
                    $this->config = $config[$this->serviceConfigKey];
                }
            }
        }
        return $this->config;
    }


    const DEFAULT_SERVICE_CONFIG_KEY = 'oauth2providers';
    const DEFAULT_PROVIDER_CLASS = '\Codeacious\OAuth2Provider\Provider';
}