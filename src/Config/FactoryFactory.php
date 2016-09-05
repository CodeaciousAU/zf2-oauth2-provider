<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @version $Id$
 */

namespace Codeacious\OAuth2Provider\Config;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FactoryFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @param string $name
     * @param string $requestedName
     * @return Factory
     */
    public function createService(ServiceLocatorInterface $services, $name=null, $requestedName=null)
    {
        if (empty($requestedName) || !class_exists($requestedName))
            return null;

        return new $requestedName($services);
    }
}