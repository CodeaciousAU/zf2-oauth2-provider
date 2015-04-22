<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider\Authentication;

use DateTime;

/**
 * Encapsulates details about an authenticated access token.
 */
class AccessTokenIdentity
{
    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string[]
     */
    private $scopes = [];

    /**
     * @var DateTime
     */
    private $expiryDate;

    /**
     * @var string Optional associated ID token (for OpenID Connect)
     */
    private $idToken;


    /**
     * @param array $tokenData
     */
    public function __construct(array $tokenData)
    {
        if (isset($tokenData['user_id']))
            $this->userId = $tokenData['user_id'];
        if (isset($tokenData['client_id']))
            $this->clientId = $tokenData['client_id'];
        if (isset($tokenData['scope']))
            $this->scopes = explode(' ', $tokenData['scope']);
        if (isset($tokenData['expires']) && is_numeric($tokenData['expires']))
            $this->expiryDate = new DateTime('@'.$tokenData['expires']);
        if (isset($tokenData['id_token']))
            $this->idToken = $tokenData['id_token'];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'user_'.$this->userId;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return string[]
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param string $scope
     * @return bool
     */
    public function hasScope($scope)
    {
        return in_array($scope, $this->scopes);
    }

    /**
     * @return DateTime|null
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * @return string|null Optional associated ID token (for OpenID Connect)
     */
    public function getIdToken()
    {
        return $this->idToken;
    }
}