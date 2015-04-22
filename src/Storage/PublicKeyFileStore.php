<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @copyright Copyright 2015 Codeacious Pty Ltd
 */

namespace Codeacious\OAuth2Provider\Storage;

use OAuth2\Storage\PublicKeyInterface;
use RuntimeException;

/**
 * OAuth2 storage adapter which loads signing keys from the filesystem.
 */
class PublicKeyFileStore implements PublicKeyInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var string
     */
    private $publicKey;


    /**
     * @param array $options
     */
    public function __construct(array $options=[])
    {
        $this->options = array_merge([
            'private_key' => '',
            'public_key' => '',
            'algorithm' => 'RS256',
        ], $options);
    }

    /**
     * @param string $client_id Optional client ID, allowing for client-specific keys
     * @return string The PEM-encoded public key
     */
    public function getPublicKey($client_id = null)
    {
        if (empty($this->publicKey))
        {
            if (($file = $this->options['public_key']))
            {
                $this->publicKey = file_get_contents($file);
                if ($this->publicKey === false)
                    throw new RuntimeException('Unable to read public key file '.$file);
            }
        }
        if (empty($this->publicKey))
            throw new RuntimeException('No public key was configured');
        return $this->publicKey;
    }

    /**
     * @param string $client_id Optional client ID, allowing for client-specific keys
     * @return string The PEM-encoded private key
     */
    public function getPrivateKey($client_id = null)
    {
        if (empty($this->privateKey))
        {
            if (($file = $this->options['private_key']))
            {
                $this->privateKey = file_get_contents($file);
                if ($this->privateKey === false)
                    throw new RuntimeException('Unable to read private key file '.$file);
            }
        }
        if (empty($this->privateKey))
            throw new RuntimeException('No private key was configured');
        return $this->privateKey;
    }

    /**
     * @param string $client_id Optional client ID
     * @return string Name of the algorithm
     */
    public function getEncryptionAlgorithm($client_id = null)
    {
        return $this->options['algorithm'];
    }
}