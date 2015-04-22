<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider\Config;

use Codeacious\OAuth2Provider\Exception\ConfigurationException;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\Server;

class GrantTypeFactory extends Factory
{
    /**
     * @var string
     */
    protected $grantTypeNamespace = '\OAuth2\GrantType\\';


    /**
     * @param mixed $config
     * @param mixed $name
     * @param Server|null $server
     * @return GrantTypeInterface
     *
     * @throws ConfigurationException
     */
    public function create($config, $name=null, Server $server=null)
    {
        //If the config value is a string, assume that it's a grant type name, a class name, or a
        //service name
        if (is_string($config))
        {
            if (class_exists($config))
                $config = array('class' => $config);
            else if (($obj = $this->resolveReference($config)))
                return $obj;
            else
                $config = array('name' => $config);
        }

        //See if it's a preconfigured object or a closure
        if (($obj = $this->resolveReference($config)))
            return $obj;

        //Otherwise, try to manually instantiate a class
        if (is_array($config))
        {
            //Determine name, if missing
            if (!isset($config['name']))
            {
                if (is_string($name))
                    $config['name'] = $name;
                else if (isset($config['class']))
                    $config['name'] = $this->camelCaseToUnderscore($config['class']);
            }

            //Determine class, if missing
            if (isset($config['name']) && !isset($config['class']))
            {
                $config['class'] = $this->grantTypeNamespace
                    .$this->underscoreToCamelCase($config['name']);
            }

            //Call constructor with the appropriate parameters
            if (isset($config['class']) && class_exists($config['class']))
            {
                $storage = null;
                if (isset($config['storage']))
                    $storage = $this->resolveReference($config['storage']);
                if (!$storage && $server && isset($config['name']))
                    $storage = $server->getStorage($config['name']);

                $class = $config['class'];
                if ($storage && isset($config['options']))
                    return new $class($storage, $config['options']);
                if ($storage)
                    return new $class($storage);
                return new $class();
            }
        }

        throw new ConfigurationException('Unable to find or instantiate grant type '
            .$name.' from configuration '.print_r($config, true));
    }
}