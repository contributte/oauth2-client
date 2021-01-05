<?php declare(strict_types = 1);

namespace Tests\Fixtures\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class TestProvider extends AbstractProvider
{

	public function getBaseAuthorizationUrl(): string
	{
		// TODO: Implement getBaseAuthorizationUrl() method.
	}

	/**
	 * @param mixed[] $params
	 */
	public function getBaseAccessTokenUrl(array $params): string
	{
		// TODO: Implement getBaseAccessTokenUrl() method.
	}

	public function getResourceOwnerDetailsUrl(AccessToken $token): string
	{
		// TODO: Implement getResourceOwnerDetailsUrl() method.
	}

	/**
	 * @return mixed[]
	 */
	protected function getDefaultScopes(): array
	{
		return [];
	}

	/**
	 * @param mixed[]|string $data Parsed response data
	 * @throws IdentityProviderException
	 */
	protected function checkResponse(ResponseInterface $response, $data): void
	{
		// TODO: Implement checkResponse() method.
	}

	/**
	 * @param mixed[] $response
	 */
	protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
	{
		// TODO: Implement createResourceOwner() method.
	}

}
