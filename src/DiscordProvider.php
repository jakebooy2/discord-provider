<?php

namespace Jakebooy\DiscordProvider;

use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class DiscordProvider extends AbstractProvider {

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
        $response = $this->getHttpClient()->post(
            $this->getTokenUrl(), [
                'form_params' => [
                    'client_id' => \config('services.discord.client_id'),
                    'client_secret' => \config('services.discord.client_secret'),
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $token,
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
    * {@inheritdoc}
    */
    public function getUserByToken($token){
        $response = $this->getHttpClient()->get(
            "https://discordapp.com/api/users/@me", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);
        $res = json_decode($response->getBody()->getContents(), true);
        $res['token'] = $token;
        return $res;
    }

    public function getUserGuildsByToken($token){
        $response = $this->getHttpClient()->get(
            "https://discordapp.com/api/users/@me/guilds", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    public function getGuildChannelsByToken($guild, $token){
        $response = $this->getHttpClient()->get(
            sprintf("https://discordapp.com/api/guilds/%s/channels", \config('services.discord.bot_token')), [
                'Authorization' => 'Bot ' . $bot_token,
            ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getGuildRolesByToken($guild, $token){
        $response = $this->getHttpClient()->get(
            sprintf("https://discordapp.com/api/guilds/%s/roles", \config('services.discord.bot_token')), [
                'Authorization' => 'Bot ' . $bot_token,
            ]);

        return json_decode($response->getBody()->getContents(), true);
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
            'avatar' => (is_null($user['avatar'])) ? null : sprintf('https://cdn.discordapp.com/avatars/%s/%s.jpg', $user['id'], $user['avatar']),        ]);
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
