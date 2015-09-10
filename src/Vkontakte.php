<?php

namespace J4k\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User as OAuthUser;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\AbstractProvider;
use J4k\OAuth2\Client\Provider\Exception\VkontakteProviderException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
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
            'first_name',
            'last_name',
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
            'verified'];

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
        $accessToken = parent::getAccessToken($grant, $params);
        return $accessToken;
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
        return new VkontakteResourceOwner($response, $token);
    }

    protected function getAccessTokenResourceOwnerId()
    {
        return 'user_id';
    }

    protected function createAccessToken(array $response, \League\OAuth2\Client\Grant\AbstractGrant $grant)
    {
        $token = parent::createAccessToken($response, $grant);
        if (!empty($response['email'])) {
            $token->email = $response['email'];
        }
        return $token;
    }
}
