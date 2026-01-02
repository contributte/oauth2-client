<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\OAuth2Client\DI\GoogleAuthExtension;
use Contributte\OAuth2Client\Flow\Google\GoogleAuthCodeFlow;
use Contributte\OAuth2Client\Flow\Google\GoogleProvider;
use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Nette\Bridges\HttpDI\HttpExtension;
use Nette\Bridges\HttpDI\SessionExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('http', new HttpExtension())
			->addExtension('session', new SessionExtension())
			->addExtension('google', new GoogleAuthExtension())
			->addConfig([
				'google' => [
					'clientId' => 'd5sa4d5',
					'clientSecret' => 'as5dd4sa6d54a6s5d4',
				],
			]);
	}, uniqid());
	/** @var Container $container */
	$container = new $class();

	// Services created
	Assert::type(GoogleProvider::class, $container->getService('google.provider'));
	Assert::type(GoogleAuthCodeFlow::class, $container->getService('google.authCodeFlow'));
	Assert::type(GoogleProvider::class, $container->getService('google.authCodeFlow')->getProvider());
});

Toolkit::test(function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('http', new HttpExtension())
			->addExtension('session', new SessionExtension())
			->addExtension('google', new GoogleAuthExtension())
			->addConfig([
				'google' => [
					'clientId' => 'd5sa4d5',
					'clientSecret' => 'as5dd4sa6d54a6s5d4',
					'options' => [
						'redirectUri' => 'https//localhost/redirect',
					],
				],
			]);
	}, uniqid());
	/** @var Container $container */
	$container = new $class();

	// Services created
	Assert::contains('redirect_uri=https%2F%2Flocalhost%2Fredirect', $container->getService('google.provider')->getAuthorizationUrl());
});

Toolkit::test(function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('http', new HttpExtension())
			->addExtension('session', new SessionExtension())
			->addExtension('google', new GoogleAuthExtension())
			->addExtension('google2', new GoogleAuthExtension())
			->addConfig([
				'google' => [
					'clientId' => 'd5sa4d5',
					'clientSecret' => 'as5dd4sa6d54a6s5d4',
					'options' => [
						'scopes' => ['scope1'],
					],
				],
				'google2' => [
					'clientId' => 'd5sa4d5',
					'clientSecret' => 'as5dd4sa6d54a6s5d4',
					'options' => [
						'scopes' => ['scope2'],
					],
				],
			]);
	}, uniqid());
	/** @var Container $container */
	$container = new $class();

	// Services created
	Assert::type(GoogleProvider::class, $container->getService('google.provider'));
	Assert::type(GoogleAuthCodeFlow::class, $container->getService('google.authCodeFlow'));
	Assert::type(GoogleProvider::class, $container->getService('google.authCodeFlow')->getProvider());
	Assert::contains('scope1', $container->getService('google.authCodeFlow')->getProvider()->getAuthorizationUrl());
	Assert::type(GoogleProvider::class, $container->getService('google2.provider'));
	Assert::type(GoogleAuthCodeFlow::class, $container->getService('google2.authCodeFlow'));
	Assert::type(GoogleProvider::class, $container->getService('google2.authCodeFlow')->getProvider());
	Assert::contains('scope2', $container->getService('google2.authCodeFlow')->getProvider()->getAuthorizationUrl());
});
