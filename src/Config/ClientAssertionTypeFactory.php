<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider\Config;

use Codeacious\OAuth2Provider\Exception\ConfigurationException;
use OAuth2\ClientAssertionType\ClientAssertionTypeInterface;

class ClientAssertionTypeFactory extends Factory
{
    /**
     * @param mixed $config
     * @param mixed $name
     * @return ClientAssertionTypeInterface
     *
     * @throws ConfigurationException
     */
    public function create($config, $name=null)
    {
        $clientAssertionType = $this->resolveReference($config);
        if (!$clientAssertionType)
        {
            throw new ConfigurationException('Unable to find or instantiate client assertion type '
                .'from configuration '.print_r($config, true));
        }
        return $clientAssertionType;
    }
}