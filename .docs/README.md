# Contributte OAuth2Client

## Setup

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
services:
	- Contributte\OAuth2Client\Flow\Google\GoogleProvider([
		clientId:
		clientSecret:
	])
	- Contributte\OAuth2Client\Flow\Google\GoogleAuthCodeFlow
```

### Facebook

- Implemented package [league/oauth2-facebook](https://github.com/thephpleague/oauth2-facebook)
- [Credentials source](https://developers.facebook.com/docs/facebook-login/overview)
- Flow registration
```neon
services:
	- Contributte\OAuth2Client\Flow\Facebook\FacebookProvider([
		clientId:
		clientSecret:
		graphApiVersion: v3.2
	])
	- Contributte\OAuth2Client\Flow\Facebook\FacebookAuthCodeFlow
```

### Others

You could implement other providers which support auth code authentication by extending `Contributte\OAuth2Client\Flow\AuthCodeFlow`. Other authentication methods are currently not supported (PR is welcome).

List of all providers is [here](https://github.com/thephpleague/oauth2-client/blob/master/docs/providers/thirdparty.md)

## Integration

This example uses Google as provider with integration through [league/oauth2-google](https://github.com/thephpleague/oauth2-google)

Install package

```bash
composer require league/oauth2-google
```

Get your oauth2 credentials (`clientId` and `clientSecret`) from [Google website](https://developers.google.com/identity/protocols/OpenIDConnect#registeringyourapp)

Register flow

```neon
services:
	- Contributte\OAuth2Client\Flow\Google\GoogleProvider([
		clientId:
		clientSecret:
	])
	- Contributte\OAuth2Client\Flow\Google\GoogleAuthCodeFlow
```

Create a control which can handle authentication and authorization

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

That's all!
