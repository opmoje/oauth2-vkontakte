<?php

namespace J4k\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\VkontakteProviderException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Vkontakte
 * @package J4k\OAuth2\Client\Provider
 */
class Vkontakte extends AbstractProvider
{
    protected $scopes = ['email'];
    protected $responseType = 'json';

    /**
     * @var bool version of vkontakte api
     */
    protected $version = '5.83';

    
    /**
     * Vkontakte constructor.
     * @param array $options
     * @param array $collaborators
     * @throws VkontakteProviderException
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
    }

    /**
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://oauth.vk.com/authorize';
    }

    /**
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params = [])
    {
        return 'https://oauth.vk.com/access_token';
    }

    /**
     * @param AccessToken $token
     * @return string
     */
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
            'verified'
        ];

        $userId = $token->getResourceOwnerId();
        $tokenValue = $token->getToken();
        return "https://api.vk.com/method/users.get?user_id={$userId}&fields="
            . implode($this->getScopeSeparator(), $fields) . "&access_token={$tokenValue}&v=" . $this->version;
    }

    /**
     * @return array
     */
    protected function getDefaultScopes()
    {
        return $this->scopes;
    }

    /**
     * @param string $grant
     * @param array $params
     * @return AccessToken
     */
    public function getAccessToken($grant = 'authorization_code', array $params = [])
    {
        return parent::getAccessToken($grant, $params);
    }

    /**
     * @param ResponseInterface $response
     * @param array|string $data
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            throw new IdentityProviderException(
                $data['error_msg'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * @param array $response
     * @param AccessToken $token
     * @return VkontakteResourceOwner|\League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new VkontakteResourceOwner($response, $token);
    }

    /**
     * @return string
     */
    protected function getAccessTokenResourceOwnerId()
    {
        return 'user_id';
    }

    /**
     * @param array $response
     * @param \League\OAuth2\Client\Grant\AbstractGrant $grant
     * @return AccessToken
     */
    protected function createAccessToken(array $response, \League\OAuth2\Client\Grant\AbstractGrant $grant)
    {
        $token = parent::createAccessToken($response, $grant);
        if (!empty($response['email'])) {
            $token->email = $response['email'];
        }
        return $token;
    }
}
