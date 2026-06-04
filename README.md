![](https://heatbadger.now.sh/github/readme/contributte/oauth2-client/)

<p align=center>
    <a href="https://github.com/contributte/oauth2-client/actions"><img src="https://badgen.net/github/checks/contributte/oauth2-client"></a>
    <a href="https://coveralls.io/r/contributte/oauth2-client"><img src="https://badgen.net/coveralls/c/github/contributte/oauth2-client"></a>
    <a href="https://packagist.org/packages/contributte/oauth2-client"><img src="https://badgen.net/packagist/dm/contributte/oauth2-client"></a>
    <a href="https://packagist.org/packages/contributte/oauth2-client"><img src="https://badgen.net/packagist/v/contributte/oauth2-client"></a>
</p>
<p align=center>
    <a href="https://packagist.org/packages/contributte/oauth2-client"><img src="https://badgen.net/packagist/php/contributte/oauth2-client"></a>
    <a href="https://github.com/contributte/oauth2-client"><img src="https://badgen.net/github/license/contributte/oauth2-client"></a>
    <a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
    <a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
    <a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
    Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

Integration of league/oauth2-client into Nette framework.

## Versions

|  State   | Version    |  Branch      | Nette    |  PHP     |
|----------|------------|--------------|----------|----------|
|  dev     |  `^0.4`    |  `master`    |  `3.0+`  |  `>=7.2` |
|  stable  |  `^0.3`    |  `master`    |  `3.0+`  |  `>=7.2` |

## Installation

Install package

```bash
composer require contributte/oauth2-client
```

## Supported flows

Take a look at [integration](#integration) for usage

### Google

- Implemented package [league/oauth2-google](https://github.com/thephpleague/oauth2-google)
- [Credentials source](https://developers.google.com/identity/protocols/OpenIDConnect#registeringyourapp)
- Flow registration

```neon
google:
	clientId: '...'
	clientSecret: '...'
	options:
		# optionally additional options passed to GoogleProvider

extensions:
	google: Contributte\OAuth2Client\DI\GoogleAuthExtension
```

### Facebook

- Implemented package [league/oauth2-facebook](https://github.com/thephpleague/oauth2-facebook)
- [Credentials source](https://developers.facebook.com/docs/facebook-login/overview)
- Flow registration
```neon
facebook:
	clientId: '...'
	clientSecret: '...'
	graphApiVersion: 'v14.0'
	options:
		 # optionally additional options passed to FacebookProvider

extensions:
	facebook: Contributte\OAuth2Client\DI\FacebookAuthExtension
```

### Gitlab

- Implemented package [omines/oauth2-gitlab](https://github.com/omines/oauth2-gitlab)
- [Credentials source](https://docs.gitlab.com/ee/integration/oauth_provider.html)
- Flow registration
```neon
gitlab:
	clientId: '...'
	clientSecret: '...'
	domain: 'https://gitlab.com'
	options:
		 # optionally additional options passed to GitlabProvider

extensions:
	gitlab: Contributte\OAuth2Client\DI\GitlabAuthExtension
```

### Others

You could implement other providers which support auth code authentication by extending `Contributte\OAuth2Client\Flow\AuthCodeFlow`. Other authentication methods are currently not supported (PR is welcome).

List of all providers is [here](https://github.com/thephpleague/oauth2-client/blob/master/docs/providers/thirdparty.md)

## Integration

This example uses Google as provider with integration through [league/oauth2-google](https://github.com/thephpleague/oauth2-google)

### Install package

```bash
composer require league/oauth2-google
```

Get your oauth2 credentials (`clientId` and `clientSecret`) from [Google website](https://developers.google.com/identity/protocols/OpenIDConnect#registeringyourapp)

### Register flow

```neon
google:
	clientId: '...'
	clientSecret: '...'
	options:
		# optionally additional options passed to GoogleProvider

extensions:
	google: Contributte\OAuth2Client\DI\GoogleAuthExtension
```

### A) Create custom control

Create custom control which can handle authentication and authorization.

```php
use Contributte\OAuth2Client\Flow\Google\GoogleAuthCodeFlow;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GoogleUser;
use Nette\Application\UI\Control;

class GoogleButton extends Control
{

	/** @var GoogleAuthCodeFlow */
	private $flow;

	public function __construct(GoogleAuthCodeFlow $flow)
	{
		parent::__construct();
		$this->flow = $flow;
	}

	public function authenticate(string $authorizationUrl): void
	{
		$this->presenter->redirectUrl(
		  $this->flow->getAuthorizationUrl($authorizationUrl)
		);
	}

	public function authorize(array $parameters = null): void
	{
		try {
			$parameters = $parameters ?? $this->getPresenter()->getHttpRequest()->getQuery();
			$accessToken = $this->flow->getAccessToken($parameters);
		} catch (IdentityProviderException $e) {
			// TODO - Identity provider failure, cannot get information about user
		}

		/** @var GoogleUser $owner */
		$owner = $this->flow->getProvider()->getResourceOwner($accessToken);

		// TODO - try sign in user with it's email ($owner->getEmail())
	}

}
```

Add control to sign presenter

```php
use Nette\Application\UI\Presenter;
use Contributte\OAuth2Client\Flow\Google\GoogleAuthCodeFlow;

class SignPresenter extends Presenter
{

	/** @inject */
	public GoogleAuthCodeFlow $googleAuthCodeFlow;

	public function actionGoogleAuthenticate(): void
	{
		$this['googleButton']->authenticate($this->presenter->link('//:Sign:googleAuthorize'));
	}

	public function actionGoogleAuthorize(): void
	{
		$this['googleButton']->authorize();
	}

	protected function createComponentGoogleButton(): GoogleButton
	{
		return new GoogleButton($this->googleAuthCodeFlow);
	}

}
```

Create link to authentication action

```latte
<a href="{plink :Front:Sign:googleAuthenticate}">Sign in with Google</a>
```

### B) Use `GenericAuthControl`

Add `GenericAuthControl` control to sign presenter

```php
use Nette\Application\UI\Presenter;
use Contributte\OAuth2Client\Flow\Google\GoogleAuthCodeFlow;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;

class SignPresenter extends Presenter
{

	public function actionGoogleAuthenticate(): void
	{
		$this['googleButton']->authenticate();
	}

	public function actionGoogleAuthorize(): void
	{
		$this['googleButton']->authorize();
	}

	protected function createComponentGoogleButton(): GoogleButton
	{
		$authControl = new GenericAuthControl(
			$this->googleAuthFlow,
			$this->presenter->link('//:Sign:googleAuthorize')
		);
		$authControl->setTemplate(__DIR__ . "/googleAuthLatte.latte");
		$authControl->onAuthenticate[] = function(AccessToken $accessToken, GoogleUser $user) {
			// TODO - try sign in user with it's email ($owner->getEmail())
		}
		$authControl->onFail[] = function() {
			// TODO - Identity provider failure, cannot get information about user
		}
		return $authControl;
	}

}
```

Create custom template for authentication control.

```latte
<a href="{link authenticate!}">Sign in with Google</a>
```

Use control in presenter template.

```latte
{control googleButton}
```

Or create link to authentication action in presenter template

```latte
<a href="{plink :Front:Sign:googleAuthenticate}">Sign in with Google</a>
```

That's all!

## Development

See [how to contribute](https://contributte.org) to this package. This package is currently maintained by these authors.

<a href="https://github.com/f3l1x">
    <img width="80" height="80" src="https://avatars.githubusercontent.com/f3l1x">
</a>

-----

Consider to [support](https://contributte.org/partners) **contributte** development team.
Also thank you for using this package.
