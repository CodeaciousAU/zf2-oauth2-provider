# oauth2-provider

A simple and clean Zend Framework 2 wrapper for the oauth2-server-php library.

The main features:
- It allows OAuth2 Server instances to be configured via the ZF Service Manager
- It allows the OAuth2 Server to work with ZF Request and Response objects
- It is flexible and minimally invasive, leaving you to manage URLs and routing however you wish
- You can accept OAuth 2.0 access tokens via [Zend\Authentication] (http://framework.zend.com/manual/current/en/modules/zend.authentication.intro.html)
- You can accept OAuth 2.0 access tokens via [ZF\MvcAuth] (https://github.com/zfcampus/zf-mvc-auth) 


## Installation

1. Use Composer to add the package `codeacious/oauth2-provider` to your project.
2. Add `Codeacious\OAuth2Provider` to the list of modules to load in your `config/application.config.php`


## Provider instantiation

The central class is `Codeacious\OAuth2Provider\Provider`, which wraps an `OAuth2\Server` instance. You interact with the Provider from your own controllers to perform OAuth functions.

There are several ways to instantiate a Provider, depending on whether you want to use the Service Manager and whether your project requires more than one Provider.

### Service manager - single instance

`Codeacious\OAuth2Provider\ProviderFactory` is a service factory that creates a single Provider and looks for configuration options under the `oauth2provider` key in your app config.

For example, put this in your `config/autoload/global.php` or `module/Application/config/module.config.php`:
```php
    'service_manager' => [
        'factories' => [
            //Register the factory with whatever service name you like
            'MyOAuth2Provider' => 'Codeacious\OAuth2Provider\ProviderFactory',
        ],
    ],
    
    'oauth2provider' => [
        //Configure the provider here
    ],
```
From your controller class, you can retrieve the service:
```php
    $provider = $this->getServiceLocator()->get('MyOAuth2Provider');
```

### Service manager - multiple instances

`Codeacious\OAuth2Provider\ProviderAbstractFactory` is an abstract factory that allows you to define any number of Providers just by placing uniquely named keys under the `oauth2providers` key in your app config.

For example, put this in your `config/autoload/global.php` or `module/Application/config/module.config.php`:
```php
    'service_manager' => [
        'abstract_factories' => [
            'Codeacious\OAuth2Provider\ProviderAbstractFactory',
        ],
    ],
    
    'oauth2provider' => [
        'MyMainProvider' => [
            //Configure the first provider here
        ],
        'MySecondaryProvider' => [
            //Configure another provider here
        ],
    ],
```
From your controller class, you can retrieve the services using the names you chose:
```php
    $mainProvider = $this->getServiceLocator()->get('MyMainProvider');
    $secondaryProvider = $this->getServiceLocator()->get('MySecondaryProvider');
```

### The manual way

You can create a Provider yourself by passing in a preconfigured OAuth server, and your current Request.

```php
    //Create and configure a Server as per the oauth2-server-php docs
    $server = new \OAuth2\Server();
    $server->addStorage(...);
    $server->addGrantType(...);
    
    //Create a Provider
    $provider = new \Codeacious\OAuth2Provider\Provider($server, $this->getRequest());
```


## Configuration

When using the service factories, you can configure the OAuth2 server from your ZF application config array. You can configure storage, request types and most other options using this mechanism.

### Example: OAuth token issuer with PDO storage

```php
    'oauth2provider' => [,
        'storage' => [
            [
                'class' => 'OAuth2\Storage\Pdo',
                'options' => [
                    'dsn' => 'mysql:host=localhost;dbname=testdb',
                    'username' => 'user',
                    'password' => 'secret',
                ],
            ]
        ],
        'options' => [
            'allow_implicit' => true,
            'auth_code_lifetime' => 60,
            'access_lifetime' => 3600,
            'refresh_token_lifetime' => 1209600,
        ],
    ],
```

### Example: Custom storage using services

```php
    'service_manager' => [
        'invokables' => [
            'MyAccessTokenStorage' => 'MyApp\Storage\AccessToken',
            'MyClientStorage' => 'MyApp\Storage\ClientCredentials',
        ],
    ],
    
    'oauth2provider' => [
        'storage' => [
            //The storage types are inferred from the interfaces these objects implement
            'MyAccessTokenStorage',
            'MyClientStorage',
        ],
    ],
```


### Example: Assigning storage objects to specific storage types

```php
    'oauth2provider' => [
        'storage' => [
            'access_token' => 'MyDatabaseStorageService',
            
            'client_credentials' => [
                'class' => 'OAuth2\Storage\Memory',
                'options' => [
                    'client_credentials' => [
                        'client1' => [
                            'client_id' => 'client1',
                            'client_secret' => 'abcdefgh',
                            'redirect_uri' => 'http://localhost',
                        ],
                    ],
                ],
            ],
        ],
    ],
```

### Example: Accepting JWT tokens using a public key stored in a file

```php
    'oauth2provider' => [
        'storage' => 
            'public_key' => [
                'class' => 'Codeacious\OAuth2Provider\Storage\PublicKeyFileStore',
                'options' => [
                    'public_key' => './config/keys/publickey.pem',
                    'algorithm' => 'RS256',
                ],
            ],
        ],
        'options' => [
            'use_jwt_access_tokens'  => true,
            'www_realm' => 'My Application',
        ],
    ],
```


## Usage

The oauth2-provider module does not implement any controllers or register any routes. Leaving these aspects to your application gives you more control and flexibility.

### Implementing a token endpoint

Create a controller and set up URL routing as you normally would in Zend Framework. In the action handler for your token action, retrieve the provider you configured in the Service Manager, and call `handleTokenRequest()`. For example:

```php
class OAuthController extends AbstractActionController
{
    public function tokenAction()
    {
        return $this->getServiceLocator()->get('MyOAuth2Provider')
            ->handleTokenRequest()
            ->makeHttpResponse();
    }
}
```

### Implementing an authorization endpoint

The Authorization endpoint requires you to implement a login form or other mechanism to establish the user's identity. Once you've done that, you can call `handleAuthorizeRequest()` to let the provider take over. Here's a simplistic example:

```php
    public function authorizationAction()
    {
        $provider = $this->getServiceLocator()->get('MyOAuth2Provider');
        
        //Reject the request if it does not comply with OAuth 2.0 rules
        if (!$provider->validateAuthorizeRequest())
            return $provider->makeHttpResponse();
            
        //If the user has submitted the logon form, validate their password
        $view = new ViewModel();
        if ($this->getRequest()->isPost())
        {
            $userId = $this->params()->fromPost('user_id');
            $password = $this->params()->fromPost('password');
            if ($this->_passwordIsCorrect($userId, $password))
            {
                return $provider
                    ->handleAuthorizeRequest(true, $userId)
                    ->makeHttpResponse();
            }
            else
                $view->message = 'Your user ID or password was incorrect.';
        }
        return $view;
    }
    
    protected function _passwordIsCorrect($userId, $password)
    {
        //Your logic
    }
```

### Accepting tokens

```php
    public function myApiEndpointAction()
    {
        //Authenticate
        $provider = $this->getServiceLocator()->get('MyOAuth2Provider');
        if (!$provider->verifyResourceRequest())
            return $provider->makeHttpResponse();
        
        //Get the authenticated user
        $userId = $provider->getIdentity()->getUserId();
        
        //Your logic here
    }
```

### Accepting tokens using ZF\MvcAuth

```php
    'service_manager' => [
        'factories' => [
            //Override the authentication listener from the zf-mvc-auth package
            'ZF\MvcAuth\Authentication\DefaultAuthenticationListener' => 'Codeacious\OAuth2Provider\MvcAuth\AuthenticationListenerFactory',
        ],
    ],
    
    'zf-mvc-auth' => [
        'authentication' => [
            //Tell the authentication listener where to find the Provider instance
            'oauth2provider' => 'MyOAuth2Provider',
        ],
    ],
```
