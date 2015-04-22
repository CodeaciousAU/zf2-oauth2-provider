<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider\MvcAuth;

use Codeacious\OAuth2Provider\Authentication\AccessTokenAdapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\PhpEnvironment\Request as HttpRequest;

/**
 * Factory to create the AuthenticationListener service.
 *
 * Designed as a substitute for the DefaultAuthenticationListenerFactory provided with the
 * zfcampus/zf-mvc-auth package.
 */
class AuthenticationListenerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('config');
        $authConfig = $config['zf-mvc-auth'];
        if (isset($authConfig['authentication']['oauth2provider'])
            && $services->has('Request')
            && ($services->get('Request') instanceof HttpRequest))
        {
            $adapter = new AccessTokenAdapter(
                $services->get($authConfig['authentication']['oauth2provider'])
            );

            $listener = new AuthenticationListener($adapter);

            if (isset($authConfig['authorization']['uri_whitelist']))
                $listener->setUriWhitelist($authConfig['authorization']['uri_whitelist']);

            return $listener;
        }
        else
        {
            //Fall back to the default factory
            $factory = new \ZF\MvcAuth\Factory\DefaultAuthenticationListenerFactory();
            return $factory->createService($services);
        }
    }
}