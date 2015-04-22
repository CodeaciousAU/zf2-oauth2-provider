<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider\Config;

use Codeacious\OAuth2Provider\Exception\ConfigurationException;
use OAuth2\ScopeInterface;

class ScopeUtilFactory extends Factory
{
    /**
     * @param mixed $config
     * @param mixed $name
     * @return ScopeInterface
     *
     * @throws ConfigurationException
     */
    public function create($config, $name=null)
    {
        $scopeUtil = $this->resolveReference($config);
        if (!$scopeUtil)
        {
            throw new ConfigurationException('Unable to find or instantiate scope utility from '
                .'configuration '.print_r($config, true));
        }
        return $scopeUtil;
    }
}