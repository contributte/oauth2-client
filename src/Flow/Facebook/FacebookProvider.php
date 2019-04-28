<?php declare(strict_types = 1);

namespace Contributte\OAuth2Client\Flow\Facebook;

use League\OAuth2\Client\Provider\Facebook;

class FacebookProvider extends Facebook
{

	public function setRedirectUri(string $uri): void
	{
		$this->redirectUri = $uri;
	}

}
