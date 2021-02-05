<?php
namespace Atlassian;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class OAuthWrapper {

	protected $baseUrl;
	protected $sandbox;
	protected $consumerKey;
	protected $privateKey;
	protected $consumerSecret;
	protected $callbackUrl;
	protected $requestTokenUrl = 'oauth';
	protected $accessTokenUrl = 'oauth';
	protected $authorizationUrl = 'OAuth.action?oauth_token=%s';

	protected $tokens;

    /**
     * @var Client
     */
	protected $client;

	public function __construct($baseUrl) {
		$this->baseUrl = $baseUrl;
	}

	public function requestTempCredentials() {
		return $this->requestCredentials(
			$this->requestTokenUrl . '?oauth_callback=' . $this->callbackUrl
		);
	}

	public function requestAuthCredentials($token, $tokenSecret, $verifier) {
		return $this->requestCredentials(
			$this->accessTokenUrl . '?oauth_callback=' . $this->callbackUrl . '&oauth_verifier=' . $verifier,
			$token,
			$tokenSecret
		);
	}

	protected function requestCredentials($url, $token = false, $tokenSecret = false) {
		$client = $this->getClient($token, $tokenSecret);

		$response = $client->post($url);

		return $this->makeTokens($response);
	}

	protected function makeTokens($response) {
		$body = (string) $response->getBody();

		$tokens = array();
		parse_str($body, $tokens);

		if (empty($tokens)) {
			throw new \Exception("An error occurred while requesting oauth token credentials");
		}

		$this->tokens = $tokens;
		return $this->tokens;
	}

    /**
     * @param false $token
     * @param false $tokenSecret
     * @return Client
     */
	public function getClient($token = false, $tokenSecret = false) {
		if (empty($token) && !is_null($this->client)) {
			return $this->client;
		} else {

		    $privateKey = $this->privateKey;

		    $stack = HandlerStack::create();
            $middleware = new Oauth1([
                'consumer_key'     => $this->consumerKey,
                'consumer_secret'  => $this->consumerSecret,
                'token'            => $token ?: $this->tokens['oauth_token'] ?? '',
                'token_secret'     => $tokenSecret ?: $this->tokens['oauth_token_secret'] ?? '',
                'signature_method' => 'RSA-SHA1',
                'private_key_file' => $privateKey,
                'private_key_passphrase' => ''
            ]);
            $stack->push($middleware);
            $this->client = new Client([
                'base_uri' => $this->baseUrl,
                'handler' => $stack,
                'auth' => 'oauth'
            ]);
			return $this->client;
		}
	}

	public function makeAuthUrl() {
		return $this->baseUrl . sprintf($this->authorizationUrl, urlencode($this->tokens['oauth_token']));
	}

	public function setConsumerKey($consumerKey) {
		$this->consumerKey = $consumerKey;
		return $this;
	}

	public function setConsumerSecret($consumerSecret) {
		$this->consumerSecret = $consumerSecret;
		return $this;
	}

	public function setCallbackUrl($callbackUrl) {
		$this->callbackUrl = $callbackUrl;
		return $this;
	}

	public function setRequestTokenUrl($requestTokenUrl) {
		$this->requestTokenUrl = $requestTokenUrl;
		return $this;
	}

	public function setAccessTokenUrl($accessTokenUrl) {
		$this->accessTokenUrl = $accessTokenUrl;
		return $this;
	}

	public function setAuthorizationUrl($authorizationUrl) {
		$this->authorizationUrl = $authorizationUrl;
		return $this;
	}

	public function setPrivateKey($privateKey) {
		$this->privateKey = $privateKey;
		return $this;
	}
}
