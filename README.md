# Jakebooy/DiscordProvider

## 1. Installation

    composer require jakebooy2/discord-provider

## 2. Service Provider
Add `\SocialiteProviders\Manager\ServiceProvider::class` to your `providers[]` array in `config\app.php`.
For example:
```
'providers' => [
    // a whole bunch of providers
    // remove 'Laravel\Socialite\SocialiteServiceProvider',
    \SocialiteProviders\Manager\ServiceProvider::class, // add
];
```
## 3. Event Listener
-   Add `SocialiteProviders\Manager\SocialiteWasCalled` event to your `listen[]` array in `app/Providers/EventServiceProvider`.

-   Add your listeners (i.e. the ones from the providers) to the `SocialiteProviders\Manager\SocialiteWasCalled[]` that you just created.

-   The listener that you add for this provider is `'SocialiteProviders\\Discord\\DiscordExtendSocialite@handle',`.

-   Note: You do not need to add anything for the built-in socialite providers unless you override them with your own providers.
For example:
```
/**
 * The event handler mappings for the application.
 *
 * @var array
 */
protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
        // add your listeners (aka providers) here
        'Jakebooy\\DiscordProvider\\DiscordExtendSocialite@handle',
    ],
];
```
You will need to add an entry to the services configuration file so that after config files are cached for usage in production environment (Laravel command `artisan config:cache`) all config is still available.

## 4. Add to `config/services.php`.

```
'discord' => [
    'client_id' => env('DISCORD_KEY'),
    'client_secret' => env('DISCORD_SECRET'),
    'redirect' => env('DISCORD_REDIRECT_URI'),
    'bot_token' => env('DISCORD_BOT_TOKEN'),
],
```

## 5. Usage
Using the provider is as simple as just providing the discord driver with Socialite.
#### Retrieving the Access Token Response Body

Laravel Socialite by default only allows access to the `access_token`. Which can be accessed via the `\Laravel\Socialite\User->token` public property. Sometimes you need access to the whole response body which may contain items such as a `refresh_token`.

You can get the access token response body, after you called the `user()` method in Socialite, by accessing the property `$user->accessTokenResponseBody`;

```
$user = Socialite::driver('discord')->user();
$accessTokenResponseBody = $user->accessTokenResponseBody;
```

#### Logging In
```
use Socialite;

public function login(){
  return Socialite::with('discord')->redirect();
}
public function confirm(){
  $user = Socialite::driver('discord')->user();
  //
}
```
#### Refreshing the Token
```
use Socialite;

public function refresh($token){
  $response = Socialite::driver('discord')->refreshToken($token);
  // $response->access_token
}
