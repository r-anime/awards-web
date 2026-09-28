<?php

namespace App\SocialiteProviders\AniList;

use Laravel\Socialite\Two\User;
use Override;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'ANILIST';

    protected $scopes = [];

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://anilist.co/api/v2/oauth/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getTokenUrl(): string
    {
        return 'https://anilist.co/api/v2/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getTokenFields($code)
    {
        return [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUrl,
            'code'          => $code,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getUserByToken($token)
    {
        
        $query = '{
            Viewer {
                id
                name
                avatar {
                    medium
                }
                createdAt
            }
        }
        ';

        $response = $this->getHttpClient()->post(
            'https://graphql.anilist.co',
            [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'query' => $query
                ]
            ]
        );
        
        return json_decode((string) $response->getBody(), true)['data']['Viewer'];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function mapUserToObject(array $user): User
    {
        $avatar = null;
        if(!empty($user['avatar']['medium'])) {
            $avatar = $user['avatar']['medium'];
        }

        return (new User)->setRaw($user)->map([
            'id' => $user['id'], 'nickname' => $user['name'], 'name' => $user['name'],
            'email' => null, 'avatar' => $avatar, 'createdutc' => $user['createdAt']
        ]);
    }
}
