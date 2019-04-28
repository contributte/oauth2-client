<?php declare(strict_types = 1);

namespace Contributte\OAuth2Client\Flow\Facebook;

use Contributte\OAuth2Client\Flow\AuthCodeFlow;
use Nette\Http\Session;

/**
 * @method FacebookProvider getProvider()
 */
class FacebookAuthCodeFlow extends AuthCodeFlow
{

	public function __construct(FacebookProvider $provider, Session $session)
	{
		parent::__construct($provider, $session);
	}

}
