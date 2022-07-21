<?php declare(strict_types = 1);

use Contributte\OAuth2Client\DI\FacebookAuthExtension;
use Contributte\OAuth2Client\Flow\Facebook\FacebookAuthCodeFlow;
use Contributte\OAuth2Client\Flow\Facebook\FacebookProvider;
use Nette\Bridges\HttpDI\HttpExtension;
use Nette\Bridges\HttpDI\SessionExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('http', new HttpExtension())
			->addExtension('session', new SessionExtension())
			->addExtension('facebook', new FacebookAuthExtension())
			->addConfig([
				'facebook' => [
					'clientId' => 'd5sa4d5',
					'clientSecret' => 'as5dd4sa6d54a6s5d4',
					'graphApiVersion' => 'v11.0',
				],
			]);
	}, uniqid());
	/** @var Container $container */
	$container = new $class();

	// Services created
	Assert::type(FacebookProvider::class, $container->getService('facebook.provider'));
	Assert::type(FacebookAuthCodeFlow::class, $container->getService('facebook.authCodeFlow'));
	Assert::type(FacebookProvider::class, $container->getService('facebook.authCodeFlow')->getProvider());
});

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('http', new HttpExtension())
			->addExtension('session', new SessionExtension())
			->addExtension('facebook', new FacebookAuthExtension())
			->addConfig([
				'facebook' => [
					'clientId' => 'd5sa4d5',
					'clientSecret' => 'as5dd4sa6d54a6s5d4',
					'graphApiVersion' => 'v11.0',
					'options' => [
						'redirectUri' => 'https//localhost/redirect',
					],
				],
			]);
	}, uniqid());
	/** @var Container $container */
	$container = new $class();

	// Services created
	Assert::contains('redirect_uri=https%2F%2Flocalhost%2Fredirect', $container->getService('facebook.provider')->getAuthorizationUrl());
});

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('http', new HttpExtension())
			->addExtension('session', new SessionExtension())
			->addExtension('facebook', new FacebookAuthExtension())
			->addExtension('facebook2', new FacebookAuthExtension())
			->addConfig([
				'facebook' => [
					'clientId' => 'd5sa4d5',
					'clientSecret' => 'as5dd4sa6d54a6s5d4',
					'graphApiVersion' => 'v11.0',
				],
				'facebook2' => [
					'clientId' => 'd5sa4d5',
					'clientSecret' => 'as5dd4sa6d54a6s5d4',
					'graphApiVersion' => 'v10.0',
				],
			]);
	}, uniqid());
	/** @var Container $container */
	$container = new $class();

	// Services created
	Assert::type(FacebookProvider::class, $container->getService('facebook.provider'));
	Assert::type(FacebookAuthCodeFlow::class, $container->getService('facebook.authCodeFlow'));
	Assert::type(FacebookProvider::class, $container->getService('facebook.authCodeFlow')->getProvider());
	Assert::contains('/v11.0/', $container->getService('facebook.authCodeFlow')->getProvider()->getBaseAuthorizationUrl());
	Assert::type(FacebookProvider::class, $container->getService('facebook2.provider'));
	Assert::type(FacebookAuthCodeFlow::class, $container->getService('facebook2.authCodeFlow'));
	Assert::type(FacebookProvider::class, $container->getService('facebook2.authCodeFlow')->getProvider());
	Assert::contains('/v10.0/', $container->getService('facebook2.authCodeFlow')->getProvider()->getBaseAuthorizationUrl());
});
