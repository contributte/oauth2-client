<?php declare(strict_types = 1);

namespace Tests\Cases\Flow;

use Contributte\OAuth2Client\Exception\Logical\InvalidArgumentException;
use Contributte\OAuth2Client\Exception\Runtime\PossibleCsrfAttackException;
use Contributte\Tester\Toolkit;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Mockery;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Tester\Assert;
use Tests\Fixtures\Flow\TestAuthCodeFlow;
use Tests\Fixtures\Provider\TestProvider;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	Assert::exception(function (): void {
		$session = Mockery::mock(Session::class);
		$flow = new TestAuthCodeFlow(new TestProvider(), $session);

		$flow->getAccessToken([]);
	}, InvalidArgumentException::class, 'Missing "code" parameter');
});

Toolkit::test(function (): void {
	Assert::exception(function (): void {
		$session = Mockery::mock(Session::class);
		$flow = new TestAuthCodeFlow(new TestProvider(), $session);

		$flow->getAccessToken(['code' => 'foo']);
		$flow->getAccessToken([]);
	}, InvalidArgumentException::class, 'Missing "state" parameter');
});

Toolkit::test(function (): void {
	Assert::exception(function (): void {
		$sessionSection = Mockery::mock(SessionSection::class);
		$sessionSection->shouldReceive('offsetExists')
			->andReturn(true);
		$sessionSection->shouldReceive('offsetGet')
			->andReturn('baz');
		$sessionSection->shouldReceive('offsetUnset');

		$session = Mockery::mock(Session::class);
		$session->shouldReceive('getSection')
			->andReturn($sessionSection);

		$flow = new TestAuthCodeFlow(new TestProvider(), $session);

		$flow->getAccessToken(['code' => 'foo', 'state' => 'bar']);
	}, PossibleCsrfAttackException::class);
});

Toolkit::test(function (): void {
	$token = Mockery::mock(AccessTokenInterface::class);

	$provider = Mockery::mock(AbstractProvider::class);
	$provider->shouldReceive('getAccessToken')
		->with('authorization_code', ['code' => 'foo'])
		->andReturn($token);

	$sessionSection = Mockery::mock(SessionSection::class);
	$sessionSection->shouldReceive('offsetExists')
		->andReturn(false);

	$session = Mockery::mock(Session::class);
	$session->shouldReceive('getSection')
		->andReturn($sessionSection);

	$flow = new TestAuthCodeFlow($provider, $session);

	Assert::same($token, $flow->getAccessToken(['code' => 'foo', 'state' => 'bar']));
});

Toolkit::test(function (): void {
	$token = Mockery::mock(AccessTokenInterface::class);

	$provider = Mockery::mock(AbstractProvider::class);
	$provider->shouldReceive('getAccessToken')
		->with('authorization_code', ['code' => 'foo', 'redirect_uri' => 'https://localhost/redirect'])
		->andReturn($token);

	$sessionSection = Mockery::mock(SessionSection::class);
	$sessionSection->shouldReceive('offsetExists')
		->with('state')
		->andReturn(false);
	$sessionSection->shouldReceive('offsetExists')
		->with('redirect_uri')
		->andReturn(true);
	$sessionSection->shouldReceive('offsetGet')
		->with('redirect_uri')
		->andReturn('https://localhost/redirect');

	$session = Mockery::mock(Session::class);
	$session->shouldReceive('getSection')
		->andReturn($sessionSection);

	$flow = new TestAuthCodeFlow($provider, $session);

	Assert::same($token, $flow->getAccessToken(['code' => 'foo', 'state' => 'bar']));
});

Toolkit::test(function (): void {
	$token = Mockery::mock(AccessTokenInterface::class);

	$provider = Mockery::mock(AbstractProvider::class);
	$provider->shouldReceive('getAccessToken')
		->with('authorization_code', ['code' => 'foo', 'redirect_uri' => 'https://localhost/redirect_explicit'])
		->andReturn($token);

	$sessionSection = Mockery::mock(SessionSection::class);
	$sessionSection->shouldReceive('offsetExists')
		->with('state')
		->andReturn(false);
	$sessionSection->shouldReceive('offsetExists')
		->with('redirect_uri')
		->andReturn(true);
	$sessionSection->shouldReceive('offsetGet')
		->with('redirect_uri')
		->andReturn('https://localhost/redirect');

	$session = Mockery::mock(Session::class);
	$session->shouldReceive('getSection')
		->andReturn($sessionSection);

	$flow = new TestAuthCodeFlow($provider, $session);

	Assert::same($token, $flow->getAccessToken(['code' => 'foo', 'state' => 'bar'], 'https://localhost/redirect_explicit'));
});

Toolkit::test(function (): void {
	$provider = Mockery::mock(AbstractProvider::class);
	$provider->shouldReceive('getAuthorizationUrl')
		->with([])
		->andReturn('foo');
	$provider->shouldReceive('getState');

	$sessionSection = Mockery::mock(SessionSection::class);
	$sessionSection->shouldReceive('offsetSet');

	$session = Mockery::mock(Session::class);
	$session->shouldReceive('getSection')
		->andReturn($sessionSection);

	$flow = new TestAuthCodeFlow($provider, $session);

	Assert::same('foo', $flow->getAuthorizationUrl());
});

Toolkit::test(function (): void {
	$provider = Mockery::mock(AbstractProvider::class);
	$provider->shouldReceive('getAuthorizationUrl')
		->with(['redirect_uri' => 'https://localhost/redirect_uri'])
		->andReturn('foo');
	$provider->shouldReceive('getState');

	$sessionSection = Mockery::mock(SessionSection::class);
	$sessionSection->shouldReceive('offsetSet');

	$session = Mockery::mock(Session::class);
	$session->shouldReceive('getSection')
		->andReturn($sessionSection);

	$flow = new TestAuthCodeFlow($provider, $session);

	Assert::same('foo', $flow->getAuthorizationUrl(['redirect_uri' => 'https://localhost/redirect_uri']));
});

Toolkit::test(function (): void {
	$provider = Mockery::mock(AbstractProvider::class);
	$provider->shouldReceive('getAuthorizationUrl')
		->with(['redirect_uri' => 'https://localhost/redirect_uri'])
		->andReturn('foo');
	$provider->shouldReceive('getState');

	$sessionSection = Mockery::mock(SessionSection::class);
	$sessionSection->shouldReceive('offsetSet');

	$session = Mockery::mock(Session::class);
	$session->shouldReceive('getSection')
		->andReturn($sessionSection);

	$flow = new TestAuthCodeFlow($provider, $session);

	Assert::same('foo', $flow->getAuthorizationUrl('https://localhost/redirect_uri'));
});

Toolkit::test(function (): void {
	$provider = Mockery::mock(AbstractProvider::class);
	$provider->shouldReceive('getAuthorizationUrl')
		->with(['state' => 'myState', 'redirect_uri' => 'https://localhost/redirect_uri'])
		->andReturn('foo');
	$provider->shouldReceive('getState');

	$sessionSection = Mockery::mock(SessionSection::class);
	$sessionSection->shouldReceive('offsetSet');

	$session = Mockery::mock(Session::class);
	$session->shouldReceive('getSection')
		->andReturn($sessionSection);

	$flow = new TestAuthCodeFlow($provider, $session);

	Assert::same('foo', $flow->getAuthorizationUrl('https://localhost/redirect_uri', ['state' => 'myState']));
});
