<?php declare(strict_types = 1);

namespace Tests\Cases\Flow;

use Contributte\OAuth2Client\Flow\Google\GoogleAuthCodeFlow;
use Contributte\OAuth2Client\Flow\Google\GoogleProvider;
use Mockery;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Ninjify\Nunjuck\Toolkit;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

Toolkit::test(function (): void {
	$provider = new GoogleProvider([
		'clientId' => 'foo',
		'clientSecret' => 'bar',
	]);

	$sessionSection = Mockery::mock(SessionSection::class);
	$sessionSection->shouldReceive('offsetSet');

	$session = Mockery::mock(Session::class);
	$session->shouldReceive('getSection')
		->andReturn($sessionSection);

	$flow = new GoogleAuthCodeFlow($provider, $session);

	Assert::same(
		'https://accounts.google.com/o/oauth2/v2/auth?state=myState&redirect_uri=https%3A%2F%2Flocalhost%2Fredirect_uri' .
		'&scope=openid%20email%20profile&response_type=code&client_id=foo',
		$flow->getAuthorizationUrl('https://localhost/redirect_uri', ['state' => 'myState'])
	);
});
