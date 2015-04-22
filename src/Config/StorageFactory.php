<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider\Config;

use Codeacious\OAuth2Provider\Exception\ConfigurationException;

class StorageFactory extends Factory
{
    /**
     * @param mixed $config
     * @param mixed $name
     * @return object
     *
     * @throws ConfigurationException
     */
    public function create($config, $name=null)
    {
        //See if the config value is a class name
        if (is_string($config) && class_exists($config))
            $config = array('class' => $config);

        //See if it's a service name, a preconfigured object or a closure
        if (($obj = $this->resolveReference($config)))
            return $obj;

        //Try to manually instantiate a class
        if (is_array($config) && isset($config['class']) && class_exists($config['class']))
        {
            $class = $config['class'];
            if (isset($config['options']))
                return new $class($config['options']);
            return new $class();
        }

        throw new ConfigurationException('Unable to find or instantiate storage provider '
            .$name.' from configuration '.print_r($config, true));
    }
}