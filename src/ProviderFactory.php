<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider;

use Codeacious\OAuth2Provider\Exception\ConfigurationException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProviderFactory implements FactoryInterface
{
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
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $services, $name=null, $requestedName=null)
    {
        $config = array();

        if ($services->has('Config'))
        {
            $c = $services->get('Config');
            if (isset($c[$this->serviceConfigKey])
                && is_array($c[$this->serviceConfigKey]))
            {
                $config = $c[$this->serviceConfigKey];
            }
        }

        /* @var $serverFactory Config\Factory */
        $serverFactory = $services->get('Codeacious\OAuth2Provider\Config\ServerFactory');
        $server = $serverFactory->create($config, $this->serviceConfigKey);

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


    const DEFAULT_SERVICE_CONFIG_KEY = 'oauth2provider';
    const DEFAULT_PROVIDER_CLASS = '\Codeacious\OAuth2Provider\Provider';
}