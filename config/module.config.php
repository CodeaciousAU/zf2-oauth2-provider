<?php

return array(
    'service_manager' => array(
        'invokables' => array(
            'Codeacious\OAuth2Provider\Config\ClientAssertionTypeFactory'
                    => 'Codeacious\OAuth2Provider\Config\ClientAssertionTypeFactory',
            'Codeacious\OAuth2Provider\Config\GrantTypeFactory'
                    => 'Codeacious\OAuth2Provider\Config\GrantTypeFactory',
            'Codeacious\OAuth2Provider\Config\ResponseTypeFactory'
                    => 'Codeacious\OAuth2Provider\Config\ResponseTypeFactory',
            'Codeacious\OAuth2Provider\Config\ScopeUtilFactory'
                    => 'Codeacious\OAuth2Provider\Config\ScopeUtilFactory',
            'Codeacious\OAuth2Provider\Config\ServerFactory'
                    => 'Codeacious\OAuth2Provider\Config\ServerFactory',
            'Codeacious\OAuth2Provider\Config\StorageFactory'
                    => 'Codeacious\OAuth2Provider\Config\StorageFactory',
            'Codeacious\OAuth2Provider\Config\TokenTypeFactory'
                    => 'Codeacious\OAuth2Provider\Config\TokenTypeFactory',
        ),
    ),
);