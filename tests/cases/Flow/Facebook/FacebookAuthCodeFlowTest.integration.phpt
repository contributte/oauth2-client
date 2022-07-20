<?php declare(strict_types = 1);

namespace Tests\Cases\Flow;

use Contributte\OAuth2Client\Flow\Facebook\FacebookAuthCodeFlow;
use Contributte\OAuth2Client\Flow\Facebook\FacebookProvider;
use Mockery;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Ninjify\Nunjuck\Toolkit;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

Toolkit::test(function (): void {
	$provider = new FacebookProvider([
		'clientId' => 'foo',
		'clientSecret' => 'bar',
		'graphApiVersion' => 'v3.2',
	]);

	$sessionSection = Mockery::mock(SessionSection::class);
	$sessionSection->shouldReceive('offsetSet');

	$session = Mockery::mock(Session::class);
	$session->shouldReceive('getSection')
		->andReturn($sessionSection);

	$flow = new FacebookAuthCodeFlow($provider, $session);

	Assert::same(
		'https://www.facebook.com/v3.2/dialog/oauth?state=myState&redirect_uri=https%3A%2F%2Flocalhost%2Fredirect_uri' .
		'&scope=public_profile%2Cemail&response_type=code&approval_prompt=auto&client_id=foo',
		$flow->getAuthorizationUrl('https://localhost/redirect_uri', ['state' => 'myState'])
	);
});
