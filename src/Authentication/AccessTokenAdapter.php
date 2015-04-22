<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider\Authentication;

use Codeacious\OAuth2Provider\Provider;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result as AuthResult;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;

/**
 * ZF authentication adapter which authenticates an HTTP request using an OAuth 2.0 access token.
 */
class AccessTokenAdapter implements AdapterInterface
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var HttpResponse
     */
    private $response;


    /**
     * @param Provider $provider
     */
    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return HttpRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param HttpRequest $request
     * @return AccessTokenAdapter
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return HttpResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param HttpResponse $response
     * @return AccessTokenAdapter
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be
     *    performed
     */
    public function authenticate()
    {
        if (!($tokenData = $this->provider->getAccessTokenData()))
            return $this->_failureResult();

        return new AuthResult(AuthResult::SUCCESS, new AccessTokenIdentity($tokenData));
    }

    /**
     * Set HTTP headers to prompt the client for authentication, and return a failed authentication
     * result.
     *
     * @return \Zend\Authentication\Result
     */
    protected function _failureResult()
    {
        $oAuthResponse = $this->provider->getOAuthResponse();

        $this->getResponse()
            ->setStatusCode($oAuthResponse->getStatusCode())
            ->getHeaders()->addHeaders($oAuthResponse->getHttpHeaders());

        if ($oAuthResponse->getParameter('error'))
        {
            $description = $oAuthResponse->getParameter('error_description');
            return new AuthResult(AuthResult::FAILURE_CREDENTIAL_INVALID, null,
                array($description ?: 'Unable to authenticate token'));
        }
        else
        {
            return new AuthResult(AuthResult::FAILURE_IDENTITY_AMBIGUOUS, null,
                array('No access token was presented'));
        }
    }
}