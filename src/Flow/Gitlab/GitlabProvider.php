<?php declare(strict_types = 1);

namespace Contributte\OAuth2Client\Flow\Gitlab;

use Omines\OAuth2\Client\Provider\Gitlab;

class GitlabProvider extends Gitlab
{

	public function setRedirectUri(string $uri): void
	{
		$this->redirectUri = $uri;
	}

}
