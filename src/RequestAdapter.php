<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider;

use OAuth2\RequestInterface;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Http\Header\ContentType;

/**
 * Wraps a ZF request object to create an OAuth2 library request object.
 */
class RequestAdapter implements RequestInterface
{
    /**
     * @var HttpRequest
     */
    private $httpRequest;

    /**
     * @var array
     */
    private $data;


    /**
     * @param HttpRequest $httpRequest
     */
    public function __construct(HttpRequest $httpRequest)
    {
        $this->httpRequest = $httpRequest;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function query($name, $default = null)
    {
        return $this->httpRequest->getQuery($name, $default);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function request($name, $default = null)
    {
        //The RequestInterface expects this method to return values from a form submission or from
        //the decoded JSON body
        if ($this->data === null)
        {
            /* @var $contentType ContentType */
            $contentType = $this->httpRequest->getHeaders('Content-type');
            $mediaType = $contentType ? $contentType->getMediaType() : null;

            if ($mediaType == 'application/x-www-form-urlencoded'
                && ($this->httpRequest->isPut() || $this->httpRequest->isDelete()))
            {
                parse_str($this->httpRequest->getContent(), $this->data);
            }
            else if ($mediaType == 'application/json'
                && ($this->httpRequest->isPost() || $this->httpRequest->isPut()
                    || $this->httpRequest->isDelete()))
            {
                $this->data = json_decode($this->httpRequest->getContent(), true);
            }
            else
            {
                $this->data = $this->httpRequest->getPost()->toArray();
            }
        }

        return (isset($this->data[$name]) ? $this->data[$name] : $default);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function server($name, $default = null)
    {
        return $this->httpRequest->getServer($name, $default);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function headers($name, $default = null)
    {
        //The RequestInterface expects this method to also return PHP_AUTH variables
        if (strpos($name, 'PHP_AUTH_') === 0)
            return $this->server($name, $default);

        $header = $this->httpRequest->getHeaders($name);
        if (!$header)
            return $default;

        return $header->getFieldValue();
    }

    /**
     * @return array
     */
    public function getAllQueryParameters()
    {
        return $this->httpRequest->getQuery()->toString();
    }
}