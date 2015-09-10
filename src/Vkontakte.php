<?php

namespace J4k\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User as OAuthUser;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\VkontakteProviderException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\VkontakteResourceOwner;
use Psr\Http\Message\ResponseInterface;


class Vkontakte extends AbstractProvider
{
    protected $scopes = ['email'];
    protected $responseType = 'json';

    public function getBaseAuthorizationUrl()
    {
        return 'https://oauth.vk.com/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://oauth.vk.com/access_token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $fields = [
            'nickname',
            'screen_name',
            'sex',
            'bdate',
            'city',
            'country',
            'timezone',
            'photo_50',
            'photo_100',
            'photo_200_orig',
            'has_mobile',
            'contacts',
            'education',
            'online',
            'counters',
            'relation',
            'last_seen',
            'status',
            'can_write_private_message',
            'can_see_all_posts',
            'can_see_audio',
            'can_post',
            'universities',
            'schools',
            'verified', ];

        //dd($token);
        $userId = $token->getResourceOwnerId();
        $tokenValue = $token->getToken();
        return "https://api.vk.com/method/users.get?user_id={$userId}&fields="
            .implode($this->getScopeSeparator(), $fields)."&access_token={$tokenValue}";
    }

    protected function getDefaultScopes()
    {
        return $this->scopes;
    }
    
    public function getAccessToken($grant = 'authorization_code', array $params = [])
    {
        /*if (is_string($grant)) {
            // PascalCase the grant. E.g: 'authorization_code' becomes 'AuthorizationCode'
            $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $grant)));
            $grant = 'League\\OAuth2\\Client\\Grant\\'.$className;
            if (! class_exists($grant)) {
                throw new \InvalidArgumentException('Unknown grant "'.$grant.'"');
            }
            $grant = new $grant();
        } elseif (! $grant instanceof GrantInterface) {
            $message = get_class($grant).' is not an instance of League\OAuth2\Client\Grant\GrantInterface';
            throw new \InvalidArgumentException($message);
        }

        $defaultParams = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => $grant,
        ];

        $requestParams = $grant->prepRequestParams($defaultParams, $params);

        try {
            switch (strtoupper($this->method)) {
                case 'GET':
                    // @codeCoverageIgnoreStart
                    // No providers included with this library use get but 3rd parties may
                    $client = $this->getHttpClient();
                    $client->setBaseUrl($this->getBaseAccessTokenUrl() . '?' . $this->httpBuildQuery($requestParams, '', '&'));
                    $request = $client->get(null, null, $requestParams)->send();
                    $response = $request->getBody();
                    break;
                    // @codeCoverageIgnoreEnd
                case 'POST':
                    $client = $this->getHttpClient();
                    $client->setBaseUrl($this->getBaseAccessTokenUrl());
                    $request = $client->post(null, null, $requestParams)->send();
                    $response = $request->getBody();
                    break;
                // @codeCoverageIgnoreStart
                default:
                    throw new \InvalidArgumentException('Neither GET nor POST is specified for request');
                // @codeCoverageIgnoreEnd
            }
        } catch (BadResponseException $e) {
            // @codeCoverageIgnoreStart
            $response = $e->getResponse()->getBody();
            // @codeCoverageIgnoreEnd
        }

        switch ($this->responseType) {
            case 'json':
                $result = json_decode($response, true);

                if (JSON_ERROR_NONE !== json_last_error()) {
                    $result = [];
                }

                break;
            case 'string':
                parse_str($response, $result);
                break;
        }

        if (isset($result['error']) && ! empty($result['error'])) {
            // @codeCoverageIgnoreStart
            throw new IDPException($result);
            // @codeCoverageIgnoreEnd
        }

        $result = $this->prepareAccessTokenResult($result);

        $accessToken = $grant->handleResponse($result);
        */
        var_dump('OK');
        $accessToken = parent::getAccessToken($grant, $params);

        //dd($accessToken);
        // Add email from response
        if (!empty($result['email'])) {
            $accessToken->email = $result['email'];
        }
        return $accessToken;
    }

    public function userDetails($response, AccessToken $token)
    {
        $response = $response->response[0];
        $user = new OAuthUser();
        $email = (isset($token->email)) ? $token->email : null;
        $location = (isset($response->country)) ? $response->country : null;
        $description = (isset($response->status)) ? $response->status : null;

        $user->exchangeArray([
            'uid' => $response->uid,
            'nickname' => $response->nickname,
            'name' => $response->screen_name,
            'firstname' => $response->first_name,
            'lastname' => $response->last_name,
            'email' => $email,
            'location' => $location,
            'description' => $description,
            'imageUrl' => $response->photo_200_orig,
        ]);
        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        $response = $response->response[0];
        return $response->uid;
    }

    public function userEmail($response, AccessToken $token)
    {
        return (isset($token->email)) ? $token->email : null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        $response = $response->response[0];
        return [$response->first_name, $response->last_name];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                $data['error_description'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        //var_dump($response);
        return new VkontakteResourceOwner($response);
    }

    protected function getAccessTokenResourceOwnerId()
    {
        return 'user_id';
    }

    protected function prepareAccessTokenResponse(array $result)
    {
        //dd(parent::prepareAccessTokenResponse($result));
        return parent::prepareAccessTokenResponse($result);
    }
} 
