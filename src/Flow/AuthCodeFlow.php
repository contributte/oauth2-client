<?php declare(strict_types = 1);

namespace Contributte\OAuth2Client\Flow;

use Contributte\OAuth2Client\Exception\Logical\InvalidArgumentException;
use Contributte\OAuth2Client\Exception\Runtime\PossibleCsrfAttackException;
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
	 * @param mixed[] $options
	 */
	public function getAuthorizationUrl(array $options = []): string
	{
		$session = $this->session->getSection(self::SESSION_NAMESPACE);

		$url = $this->provider->getAuthorizationUrl($options);

		$session['state'] = $this->provider->getState();

		return $url;
	}

	/**
	 * @param mixed[] $parameters
	 * @return AccessToken|AccessTokenInterface
	 * @throws IdentityProviderException
	 */
	public function getAccessToken(array $parameters): AccessTokenInterface
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

			throw new PossibleCsrfAttackException();
		}

		// Try to get an access token (using the authorization code grant)
		$token = $this->provider->getAccessToken('authorization_code', ['code' => $parameters['code']]);

		return $token;
	}

	public function getProvider(): AbstractProvider
	{
		return $this->provider;
	}

}
