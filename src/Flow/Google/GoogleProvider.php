<?php declare(strict_types = 1);

namespace Contributte\OAuth2Client\Flow\Google;

use League\OAuth2\Client\Provider\Google;

class GoogleProvider extends Google
{

	public function setRedirectUri(string $uri): void
	{
		$this->redirectUri = $uri;
	}

}
