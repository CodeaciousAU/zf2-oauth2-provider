<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider\MvcAuth;

use ZF\MvcAuth\MvcAuthEvent;
use ZF\MvcAuth\Identity\AuthenticatedIdentity;
use ZF\MvcAuth\Identity\GuestIdentity;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Authentication\Adapter\AdapterInterface;

/**
 * Event listener which authenticates HTTP requests using an adapter.
 *
 * For use with the zfcampus/zf-mvc-auth package.
 */
class AuthenticationListener
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var string[]
     */
    private $uriWhitelist = array();


    /**
     * Constructor.
     *
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return string[]
     */
    public function getUriWhitelist()
    {
        return $this->uriWhitelist;
    }

    /**
     * Provide a list of regular expression patterns which denote relative URIs that don't require
     * any authentication.
     *
     * @param string[] $uriWhitelist
     * @return AuthenticationListener
     */
    public function setUriWhitelist($uriWhitelist)
    {
        $this->uriWhitelist = $uriWhitelist;
        return $this;
    }

    /**
     * Listen to authentication events
     *
     * @param MvcAuthEvent $mvcAuthEvent
     * @return mixed
     */
    public function __invoke(MvcAuthEvent $mvcAuthEvent)
    {
        $mvcEvent = $mvcAuthEvent->getMvcEvent();
        $request  = $mvcEvent->getRequest();
        $response = $mvcEvent->getResponse();

        //Skip authentication for console requests or OPTIONS requests
        if (!$request instanceof HttpRequest || $request->isOptions())
            return null;

        //Skip authentication if the requested URI is on the whitelist
        $relPath = $this->_getRelativePath($request);
        foreach ($this->getUriWhitelist() as $pattern)
        {
            $regex = '/'.str_replace('/', '\/', $pattern).'/';
            if (preg_match($regex, $relPath))
                return null;
        }

        //Provide our auth adapter with the request and response objects if it needs them
        if (is_callable(array($this->adapter, 'setRequest')))
            $this->adapter->setRequest($request);
        if (is_callable(array($this->adapter, 'setResponse')))
            $this->adapter->setResponse($response);

        //Ask the adapter to authenticate
        $authService = $mvcAuthEvent->getAuthenticationService();
        $authResult = $authService->authenticate($this->adapter);
        $mvcAuthEvent->setAuthenticationResult($authResult);

        //Create the identity object
        if ($authResult->isValid())
        {
            //Create MvcAuth identity
            $resultIdentity = $authResult->getIdentity();
            $identity = new AuthenticatedIdentity($resultIdentity);
            $identity->setName((string)$resultIdentity);
        }
        else
            $identity = new GuestIdentity();

        $mvcEvent->setParam('ZF\MvcAuth\Identity', $identity);
        return $identity;
    }

    /**
     * Get the URI path of the request, relative to the application base URL.
     *
     * @param HttpRequest $request
     * @return string
     */
    protected function _getRelativePath(HttpRequest $request)
    {
        $basePath = $request->getBasePath();
        $relPath = $request->getUri()->getPath();
        if (!empty($basePath) && strpos($relPath, $basePath) === 0)
            $relPath = substr($relPath, strlen($basePath));
        return $relPath;
    }
}