<?php

namespace Library;

class Library
{
    /**
     * @var string Base URL of all API requests
     */
    protected $baseUrl = 'https://request-website-url.com';

    /**
     * @var string URL a user visits to authorize an access token
     */
    protected $auth = 'https://request-website-url.com/oauth/authorize';

    /**
     * @var string URL used to request an access token
     */
    protected $tokenUri = 'https://request-website-url.com/oauth/authorize';


    /**
     * @var string
     */
    protected $client_id;

    /**
     * @var string
     */
    protected $client_secret;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var string
     */
    protected $contentType = 'application/json';

    /**
     * @var array Cache for services so they aren't created multiple times
     */
    protected $apis = array();

    /**
     * @var boolean Determines if API calls should be logged
     */
    protected $debug = false;

    /**
     * @var Http\ClientInterface
     */
    protected $httpClient;

    /**
     * @var boolean
     */
    public $needsEmptyKey = true;

    /**
     * @var Token
     */
    protected $token;

    /**
     * @var stirng
     */
    protected $perPage = 15;

    /**
     * @param array $config
     */
    public function __construct($config = array())
    {
        if (isset($config['client_id'])) {
            $this->client_id = $config['client_id'];
        }

        if (isset($config['client_secret'])) {
            $this->client_secret = $config['client_secret'];
        }

        if (isset($config['redirectUri'])) {
            $this->redirectUri = $config['redirectUri'];
        }

        if (isset($config['debug'])) {
            $this->debug = $config['debug'];
        }
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @param string $auth
     *
     * @return string
     */
    public function setAuth($auth)
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * @return string
     */
    public function getTokenUri()
    {
        return $this->tokenUri;
    }

    /**
     * @param string $tokenUri
     */
    public function setTokenUri($tokenUri)
    {
        $this->tokenUri = $tokenUri;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @param string $client_id
     *
     * @return string
     */
    public function setClientId($client_id)
    {
        $this->client_id = $client_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     *
     * @return string
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @return string
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @param string $Pagination per page
     *
     * @return string
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;

        return $this->perPage;
    }

    /**
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @param string $redirectUri
     *
     * @return string
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param Token $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return Http\ClientInterface
     */
    public function getHttpClient()
    {
        return new Http\CurlClient;
    }

    /**
     * @return string
     */
    public function getAuthorizationUrl($state = null)
    {
        $params = array(
            'client_id'     => $this->client_id,
            'redirect_uri'  => $this->redirectUri,
            'response_type' => 'code',
            'scope'         => 'authentication'
        );

        if ( ! is_null($state) && $state !== null && is_string($state)) {
            $params['state'] = (string)$state;
        }

        return $this->auth . '?' . http_build_query($params);
    }

    /**
     * @param string $code
     *
     * @return array
     * @throws LibException
     */
    public function requestAccessToken()
    {
        $params = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'scope' => 'authentication',
        ];

        $client = $this->getHttpClient();

        $tokenInfo = $client->request('POST', $this->tokenUri, [
            'body'    => http_build_query($params),
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']
        ]);

        $this->checkError($tokenInfo);
        $this->setToken(new Token(json_decode($tokenInfo, true)));

        return $this->getToken();
    }

    /**
     * @return array
     * @throws LibException
     */
    public function refreshAccessToken()
    {
        $headers = array(
            'Authorization' => 'Basic ' . base64_encode($this->client_id . ':' . $this->client_secret),
            'Content-Type'  => 'application/x-www-form-urlencoded'
        );

        $params = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->getToken()->getRefreshToken(),
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'scope' => 'authentication',
        );

        $client = $this->getHttpClient();

        $tokenInfo = $client->request('POST', $this->tokenUri, ['body' => http_build_query($params), 'headers' => $headers]);
        $this->checkError($tokenInfo);
        $this->setToken(new Token(json_decode($tokenInfo, true)));

        return $this->getToken();
    }

    /*
    * Check Errors
    */
    public function checkError($tokenInfo)
    {
        $token_array = json_decode($tokenInfo, true);
        if (isset($token_array['error']))
            throw new \UnexpectedValueException(sprintf($token_array['error'].': '.$token_array['message']));
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $params
     *
     * @throws LibraryException
     * @return mixed
     */
    public function restfulRequest($method, $url, $params = array())
    {
        $client = $this->getHttpClient();
        $full_params = [];
        $full_params['body'] = json_encode($params);

        $full_params['headers'] = array(
            'Content-Type' => $this->getContentType(),
            'client-id' => $this->client_id,
            'Authorization' => 'Bearer '.$this->getToken()->getAccessToken(),
        );

        $response = $client->request($method, $url, $full_params);

        return json_decode($response, true);
    }

    /**
     * @param $name
     *
     * @throws \UnexpectedValueException
     * @return mixed
     */
    public function __get($name)
    {
        $services = array(
            'customers',
            'profiles',
        );

        if (method_exists($this, $name) and in_array($name, $services)) {
            return $this->{$name}();
        }

        throw new \UnexpectedValueException(sprintf('Invalid service: %s', $name));
    }


    /**
     * @return \Library\Api\CustomersService
     */
    public function customers()
    {
        return $this->getRestApi('CustomersService');
    }

    /**
     * @return \Library\Api\ProfileService
     */
    public function profiles()
    {
        return $this->getRestApi('ProfileService');
    }

    /**
     * Returns the requested class name, optionally using a cached array so no
     * object is instantiated more than once during a request.
     *
     * @param string $class
     *
     * @return mixed
     */
    public function getRestApi($class)
    {
        $class = '\Library\Api\\' . $class;

        return new $class($this);
    }

}
