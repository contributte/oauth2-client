<?php declare(strict_types = 1);

namespace Contributte\OAuth2Client\Flow;

use Contributte\OAuth2Client\Exception\Logical\InvalidArgumentException;
use Contributte\OAuth2Client\Exception\Runtime\PossibleCsrfAttackException;
use Contributte\OAuth2Client\Exception\RuntimeException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Nette\Http\Session;

abstract class AuthCodeFlow
{

	public const SESSION_NAMESPACE = 'contributte.oauth2client';

	/** @var AbstractProvider */
	protected $provider;

	/** @var Session */
	protected $session;

	public function __construct(AbstractProvider $provider, Session $session)
	{
		$this->provider = $provider;
		$this->session = $session;
	}

	/**
	 * @param string|mixed[]|null $redirectUriOrOptions
	 * @param mixed[] $options
	 */
	public function getAuthorizationUrl($redirectUriOrOptions = null, array $options = []): string
	{
		if (is_array($redirectUriOrOptions)) {
			$options = array_merge($options, $redirectUriOrOptions);
		} elseif (is_string($redirectUriOrOptions)) {
			$options['redirect_uri'] = $redirectUriOrOptions;
		} elseif ($redirectUriOrOptions !== null) { /** @phpstan-ignore-line */
			throw new RuntimeException('Parameter #1 redirectUriOrOptions of getAuthorizationUrl accepts only string or array.');
		}

		$session = $this->session->getSection(self::SESSION_NAMESPACE);

		$url = $this->provider->getAuthorizationUrl($options);

		$session['state'] = $this->provider->getState();
		$session['redirect_uri'] = $options['redirect_uri'] ?? null;

		return $url;
	}

	/**
	 * @param mixed[] $parameters
	 * @return AccessToken|AccessTokenInterface
	 * @throws IdentityProviderException
	 */
	public function getAccessToken(array $parameters, ?string $redirectUri = null): AccessTokenInterface
	{
		if (!isset($parameters['code'])) {
			throw new InvalidArgumentException('Missing "code" parameter');
		}

		if (!isset($parameters['state'])) {
			throw new InvalidArgumentException('Missing "state" parameter');
		}

		$session = $this->session->getSection(self::SESSION_NAMESPACE);

		// Possible CSRF attack
		if (isset($session['state']) && $parameters['state'] !== $session['state']) {
			unset($session['state']);
			unset($session['redirect_uri']);
			throw new PossibleCsrfAttackException();
		}

		$options = array_filter([
			'code' => $parameters['code'],
			'redirect_uri' => $redirectUri ?? $session['redirect_uri'] ?? null,
		]);

		// Try to get an access token (using the authorization code grant)
		return $this->provider->getAccessToken('authorization_code', $options);
	}

	public function getProvider(): AbstractProvider
	{
		return $this->provider;
	}

}
