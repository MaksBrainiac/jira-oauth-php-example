<?php

namespace Services;

use Atlassian\OAuthWrapper;
use GuzzleHttp\Client;

class JiraAuthService
{
    /**
     * @var OAuthWrapper
     */
    private $oauth;

    /**
     * @return OAuthWrapper
     */
    public function getOauth()
    {
        if ($this->oauth == null) {
            $this->oauth = new \Atlassian\OAuthWrapper(config('services.jira.path'));
            $this->oauth->setPrivateKey(storage_path('app/keys/jira_privatekey.pem'))
                ->setConsumerKey('OauthKey')
                ->setConsumerSecret('')
                ->setRequestTokenUrl('plugins/servlet/oauth/request-token')
                ->setAuthorizationUrl('plugins/servlet/oauth/authorize?oauth_token=%s')
                ->setAccessTokenUrl('plugins/servlet/oauth/access-token')
                ->setCallbackUrl(route('callback'));
        }
        return $this->oauth;
    }

    public function getClient()
    {
        if (config('services.jira.account')) {
            $user = User::find(config('services.jira.account'));
            return $this->getOauth()->getClient($user->oauth_token, $user->oauth_token_secret);
        }
        else {
            return new Client([
                'base_uri' => config('services.jira.path'),
                'timeout'  => 10.0,
                'auth' => [config('services.jira.login'), config('services.jira.password')]
            ]);
        }
    }

}
