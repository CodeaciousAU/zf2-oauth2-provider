<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider\Config;

use Codeacious\OAuth2Provider\Exception\ConfigurationException;
use OAuth2\ResponseType\ResponseTypeInterface;

class ResponseTypeFactory extends Factory
{
    /**
     * @param mixed $config
     * @param mixed $name
     * @return ResponseTypeInterface
     *
     * @throws ConfigurationException
     */
    public function create($config, $name=null)
    {
        $responseType = $this->resolveReference($config);
        if (!$responseType)
        {
            throw new ConfigurationException('Unable to find or instantiate response type '
                .$name.' from configuration '.print_r($config, true));
        }
        return $responseType;
    }
}