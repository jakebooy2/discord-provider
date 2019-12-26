<?php

namespace Jakebooy\DiscordProvider;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class DiscordProvider extends AbstractProvider implements ProviderInterface {

    /**
    * {@inheritdoc}
    */
    const IDENTIFIER = "DISCORD";

    /**
    * {@inheritdoc}
    */
    protected $scopes = [
        'identify',
        'guilds'
    ];

    /**
     * Get Discord Endpoint URLs
     */
     protected $endpoint = array([
        'user' => 'https://discordapp.com/api/users/@me',
        'user:guilds' => 'https://discordapp.com/api/users/@me/guilds',
        'guild' => 'https://discordapp.com/api/guilds/%s',
        'guild:channels' => 'https://discordapp.com/api/guilds/%s/channels',
        'guild:roles' => 'https://discordapp.com/api/guilds/%s/roles'
     ]);

    /**
    * {@inheritdoc}
    */
    protected $scopeSeparator = ' ';

    /**
    * {@inheritdoc}
    */
    public function getAuthUrl($state){
        return $this->buildAuthUrlFromBase(
            'https://discordapp.com/api/oauth2/authorize', $state
        );
    }

    /**
    * {@inheritdoc}
    */
    public function getTokenUrl(){
        return 'https://discordapp.com/api/oauth2/token';
    }

    public function refreshToken($token){
        $response = $this->getHttpClient()->get(
            $this->getTokenUrl(),
            'data' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $token,
                'redirect_uri' => \config('services.discord.refresh_redirect'),
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        );

        return json_decode($response()->getBody()->getContents(), true);
    }

    /**
    * {@inheritdoc}
    */
    public function getUserByToken($token){
        $response = $this->getHttpClient()->get(
            $endpoint['user'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]
        );

        return json_decode($response()->getBody()->getContents(), true);
    }

    public function getGuildChannelsByToken($guild, $token){
        $response = $this->getHttpClient()->get(
            sprintf($endpoint['guild:channels'], \config('services.discord.bot_token')), [
                'Authorization' => 'Bot ' . $bot_token,
            ],
        );

        return json_decode($response()->getBody()->getContents(), true);
    }

    public function getGuildRolesByToken($guild, $token){
        $response = $this->getHttpClient()->get(
            sprintf($endpoint['guild:roles'], \config('services.discord.bot_token')), [
                'Authorization' => 'Bot ' . $bot_token,
            ],
        );

        return json_decode($response()->getBody()->getContents(), true);
    }


    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user){
        return (new User())->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => sprintf('%s#%d', $user['username'], $user['discriminator']),
            'name' => $user['username'],
            'guilds' => $this->getUserGuildsByToken($user['token']),
            'token' => array("token" => $user['token'], "expires_in" => $user['expires_in']),
            'refresh_token' => $user['refresh_token'],
            'avatar' => (is_null($user['avatar'])) ? sprintf("https://cdn.discordapp.com/embed/avatars/%d.png", intval($user['discriminator']) % 5))  : sprintf('https://cdn.discordapp.com/avatars/%s/%s.jpg', $user['id'], $user['avatar']),
        ]);
    }


    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code){
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }






}
