<?php declare(strict_types = 1);

namespace Tests\Cases\Flow;

use Contributte\OAuth2Client\Flow\AuthCodeFlow;
use Contributte\OAuth2Client\UI\Components\GenericAuthControl;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use Mockery;
use Nette\Application\AbortException;
use Nette\Application\Responses\RedirectResponse;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Ninjify\Nunjuck\Toolkit;
use Tester\Assert;
use Tester\Environment;
use Tests\Fixtures\Presenter\TestPresenter;

require_once __DIR__ . '/../../../bootstrap.php';

Environment::bypassFinals();

Toolkit::test(function (): void {
	$flow = Mockery::mock(AuthCodeFlow::class);
	$flow->shouldReceive('getAUthorizationUrl')
		->with(['redirect_uri' => 'https://localhost/redirect'])
		->andReturn('https://localhost/auth');

	$authControl = new GenericAuthControl($flow, 'https://localhost/redirect');
	$presenter = new TestPresenter($authControl);

	Assert::exception(function () use ($authControl) {
		$authControl->authenticate();
	}, AbortException::class);

	/**	@var RedirectResponse $response */
	$response = $presenter->response;
	Assert::type(RedirectResponse::class, $response);
	Assert::equal('https://localhost/auth', $response->getUrl());
});

Toolkit::test(function (): void {
	$token = Mockery::mock(AccessToken::class);

	$provider = Mockery::mock(AbstractProvider::class);
	$provider->shouldReceive('getResourceOwner')
		->andReturn(new GenericResourceOwner([], 1));

	$flow = Mockery::mock(AuthCodeFlow::class);
	$flow->shouldReceive('getAccessToken')
		->with(['code' => '123'])
		->andReturn($token);
	$flow->shouldReceive('getProvider')
		->andReturn($provider);

	$request = new Request(new UrlScript('https://localhost/redirect?code=123'));

	$events = [];

	$authControl = new GenericAuthControl($flow, 'https://localhost/redirect');
	$authControl->onAuthenticated[] = function ($accessToken, $user) use (&$events) {
		$events[] = ['onAuthenticated', $accessToken, $user];
	};
	$authControl->onFailed[] = function () use (&$events) {
		$events[] = ['onFailed'];
	};
	new TestPresenter($authControl, $request);

	$user = $authControl->authorize();

	Assert::type(GenericResourceOwner::class, $user);

	Assert::count(1, $events);
	Assert::equal('onAuthenticated', $events[0][0]);
	Assert::type(AccessToken::class, $events[0][1]);
	Assert::type(GenericResourceOwner::class, $events[0][2]);
});

Toolkit::test(function (): void {
	$flow = Mockery::mock(AuthCodeFlow::class);
	$flow->shouldReceive('getAccessToken')
		->andThrow(new IdentityProviderException('error', 1, null));

	$request = new Request(new UrlScript('https://localhost/redirect?code=123'));

	$events = [];

	$authControl = new GenericAuthControl($flow, 'https://localhost/redirect');
	$authControl->onAuthenticated[] = function ($accessToken, $user) use (&$events) {
		$events[] = ['onAuthenticated', $accessToken, $user];
	};
	$authControl->onFailed[] = function () use (&$events) {
		$events[] = ['onFailed'];
	};
	new TestPresenter($authControl, $request);

	$user = $authControl->authorize();

	Assert::null($user);

	Assert::count(1, $events);
	Assert::equal('onFailed', $events[0][0]);
});
