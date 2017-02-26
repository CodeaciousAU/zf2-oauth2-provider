<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider\Config;

use Codeacious\OAuth2Provider\Exception\ConfigurationException;
use OAuth2\TokenType\TokenTypeInterface;

class TokenTypeFactory extends Factory
{
    /**
     * @var string
     */
    protected $tokenTypeNamespace = '\OAuth2\TokenType\\';


    /**
     * @param mixed $config
     * @param mixed $name
     * @return TokenTypeInterface
     *
     * @throws ConfigurationException
     */
    public function create($config, $name=null)
    {
        //If the config value is a string, assume that it's a token type name, a class name, or a
        //service name
        if (is_string($config))
        {
            if (class_exists($config) && (!$this->services || !$this->services->has($config)))
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
            if (!isset($config['name']) && is_string($name))
                $config['name'] = $name;

            //Determine class, if missing
            if (isset($config['name']) && !isset($config['class']))
            {
                $config['class'] = $this->tokenTypeNamespace
                    .$this->underscoreToCamelCase($config['name']);
            }

            //Call constructor with the appropriate parameters
            if (isset($config['class']) && class_exists($config['class']))
            {
                $class = $config['class'];
                if (isset($config['options']))
                    return new $class($config['options']);
                return new $class();
            }
        }

        throw new ConfigurationException('Unable to find or instantiate token type from '
            .'configuration '.print_r($config, true));
    }
}