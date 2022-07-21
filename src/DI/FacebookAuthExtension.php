<?php declare(strict_types = 1);

namespace Contributte\OAuth2Client\DI;

use Contributte\OAuth2Client\Flow\Facebook\FacebookAuthCodeFlow;
use Contributte\OAuth2Client\Flow\Facebook\FacebookProvider;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @property-read stdClass $config
 */
class FacebookAuthExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'clientId' => Expect::string()->required(),
			'clientSecret' => Expect::string()->required(),
			'graphApiVersion' => Expect::string()->required(),
			'options' => Expect::array(),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$providerOptions = [
			'clientId' => $config->clientId,
			'clientSecret' => $config->clientSecret,
			'graphApiVersion' => $config->graphApiVersion,
		];

		if (isset($config->options)) {
			$providerOptions = array_merge($config->options, $providerOptions);
		}

		$builder->addDefinition($this->prefix('provider'))
			->setFactory(FacebookProvider::class, [$providerOptions]);

		$builder->addDefinition($this->prefix('authCodeFlow'))
			->setFactory(FacebookAuthCodeFlow::class, ['@' . $this->prefix('provider')]);
	}

}
