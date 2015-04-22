<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider\Config;

use Codeacious\OAuth2Provider\Exception\ConfigurationException;
use OAuth2\Server;

class ServerFactory extends Factory
{
    /**
     * @param mixed $config
     * @param mixed $name
     * @return Server
     *
     * @throws ConfigurationException
     */
    public function create($config, $name=null)
    {
        //Gather storage
        $storage = array();
        if (isset($config['storage']) && is_array($config['storage']))
        {
            $storageFactory = $this->getConfigFactory('StorageFactory');
            foreach ($config['storage'] as $key => $val)
                $storage[$key] = $storageFactory->create($val, $key);
        }

        //Gather response types
        $responseTypes = array();
        if (isset($config['response_types']) && is_array($config['response_types']))
        {
            $responseTypeFactory = $this->getConfigFactory('ResponseTypeFactory');
            foreach ($config['response_types'] as $key => $val)
                $responseTypes[$key] = $responseTypeFactory->create($val, $key);
        }

        //Gather options
        $options = array();
        if (isset($config['options']) && is_array($config['options']))
            $options = $config['options'];

        //Get chosen token type
        $tokenType = null;
        if (isset($config['token_type']))
        {
            $tokenTypeFactory = $this->getConfigFactory('TokenTypeFactory');
            $tokenType = $tokenTypeFactory->create($config['token_type']);
        }

        //Get chosen scope utility
        $scopeUtil = null;
        if (isset($config['scope_util']))
        {
            $scopeUtilFactory = $this->getConfigFactory('ScopeUtilFactory');
            $scopeUtil = $scopeUtilFactory->create($config['scope_util']);
        }

        //Get chosen client assertion type
        $clientAssertionType = null;
        if (isset($config['client_assertion_type']))
        {
            $clientAssertionTypeFactory = $this->getConfigFactory('ClientAssertionTypeFactory');
            $clientAssertionType = $clientAssertionTypeFactory->create(
                $config['client_assertion_type']
            );
        }

        //Create server
        $class = '\OAuth2\Server';
        if (isset($config['server_class']))
        {
            $class = $config['server_class'];
            if (!class_exists($class))
            {
                throw new ConfigurationException('Class "'.$class.'" not found for config key '
                    .'server_class');
            }
        }
        /* @var $server Server */
        $server = new $class(
            $storage,
            $options,
            array(),
            $responseTypes,
            $tokenType,
            $scopeUtil,
            $clientAssertionType
        );

        //Add grant types
        if (isset($config['grant_types']) && is_array($config['grant_types']))
        {
            $grantTypeFactory = $this->getConfigFactory('GrantTypeFactory');
            foreach ($config['grant_types'] as $key => $val)
            {
                $grantType = $grantTypeFactory->create($val, $key, $server);
                $server->addGrantType($grantType, $key);
            }
        }

        //Override OAuth2 library components if specified
        if (isset($config['authorize_controller']))
        {
            $server->setAuthorizeController(
                $this->objectFromParam($config, 'authorize_controller')
            );
        }
        if (isset($config['resource_controller']))
        {
            $server->setResourceController(
                $this->objectFromParam($config, 'resource_controller')
            );
        }
        if (isset($config['token_controller']))
        {
            $server->setTokenController(
                $this->objectFromParam($config, 'token_controller')
            );
        }

        return $server;
    }

    /**
     * @param string $name
     * @return Factory
     */
    protected function getConfigFactory($name)
    {
        return $this->getServiceLocator()->get('Codeacious\OAuth2Provider\Config\\'.$name);
    }

    /**
     * @param array $config
     * @param string $param
     * @return object
     */
    protected function objectFromParam($config, $param)
    {
        $obj = $this->resolveReference($config[$param]);
        if (!$obj)
        {
            throw new ConfigurationException('Invalid service name, class or object for config '
                .'parameter '.$param);
        }
        return $obj;
    }
}