<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider;

use OAuth2\Server as OAuthServer;
use OAuth2\RequestInterface as OAuthRequest;
use OAuth2\Response as OAuthResponse;

use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;

use Codeacious\OAuth2Provider\Authentication\AccessTokenIdentity;

/**
 * ZF2 wrapper for OAuth2\Server
 */
class Provider
{
    /**
     * @var HttpRequest
     */
    private $httpRequest;

    /**
     * @var OAuthServer
     */
    private $oAuthServer;

    /**
     * @var OAuthRequest
     */
    private $oAuthRequest;

    /**
     * @var OAuthResponse
     */
    private $oAuthResponse;


    /**
     * @param OAuthServer $oAuthServer
     * @param HttpRequest $httpRequest
     */
    public function __construct(OAuthServer $oAuthServer, HttpRequest $httpRequest)
    {
        $this->oAuthServer = $oAuthServer;
        $this->httpRequest = $httpRequest;
    }

    /**
     * @return HttpRequest
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    /**
     * @return OAuthServer
     */
    public function getOAuthServer()
    {
        return $this->oAuthServer;
    }

    /**
     * @return OAuthRequest
     */
    public function getOAuthRequest()
    {
        if (!$this->oAuthRequest)
            $this->oAuthRequest = new RequestAdapter($this->getHttpRequest());
        return $this->oAuthRequest;
    }

    /**
     * @return OAuthResponse
     */
    public function getOAuthResponse()
    {
        if (!$this->oAuthResponse)
            $this->oAuthResponse = new OAuthResponse();
        return $this->oAuthResponse;
    }

    /**
     * @return bool
     */
    public function validateAuthorizeRequest()
    {
        return $this->getOAuthServer()->validateAuthorizeRequest($this->getOAuthRequest(),
            $this->getOAuthResponse());
    }

    /**
     * @param bool $isAuthorized
     * @param string|null $userId
     * @return Provider
     */
    public function handleAuthorizeRequest($isAuthorized, $userId = null)
    {
        $this->getOAuthServer()->handleAuthorizeRequest($this->getOAuthRequest(),
            $this->getOAuthResponse(), $isAuthorized, $userId);
        return $this;
    }

    /**
     * @return Provider
     */
    public function handleTokenRequest()
    {
        $this->getOAuthServer()->handleTokenRequest($this->getOAuthRequest(),
            $this->getOAuthResponse());
        return $this;
    }

    /**
     * @return Provider
     */
    public function handleUserInfoRequest()
    {
        $this->getOAuthServer()->handleUserInfoRequest($this->getOAuthRequest(),
            $this->getOAuthResponse());
        return $this;
    }

    /**
     * @param string $scope The scope name that is required to be present. If null, the scope of
     *    the access token is not checked.
     * @return Provider
     */
    public function verifyResourceRequest($scope = null)
    {
        $this->getOAuthServer()->verifyResourceRequest($this->getOAuthRequest(),
            $this->getOAuthResponse(), $scope);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getAccessTokenData()
    {
        return $this->getOAuthServer()->getAccessTokenData($this->getOAuthRequest(),
            $this->getOAuthResponse());
    }

    /**
     * Retrieve information about the user making the current request (if they supplied a valid
     * access token).
     *
     * @return AccessTokenIdentity|null
     */
    public function getIdentity()
    {
        if (!($data = $this->getAccessTokenData()))
            return null;

        return new AccessTokenIdentity($data);
    }

    /**
     * @return HttpResponse
     */
    public function makeHttpResponse()
    {
        return new ResponseAdapter($this->getOAuthResponse());
    }
}