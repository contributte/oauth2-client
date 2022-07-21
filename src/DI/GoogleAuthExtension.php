<?php declare(strict_types = 1);

namespace Contributte\OAuth2Client\DI;

use Contributte\OAuth2Client\Flow\Google\GoogleAuthCodeFlow;
use Contributte\OAuth2Client\Flow\Google\GoogleProvider;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @property-read stdClass $config
 */
class GoogleAuthExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'clientId' => Expect::string()->required(),
			'clientSecret' => Expect::string()->required(),
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
		];

		if (isset($config->options)) {
			$providerOptions = array_merge($config->options, $providerOptions);
		}

		$builder->addDefinition($this->prefix('provider'))
			->setFactory(GoogleProvider::class, [$providerOptions]);

		$builder->addDefinition($this->prefix('authCodeFlow'))
			->setFactory(GoogleAuthCodeFlow::class, ['@' . $this->prefix('provider')]);
	}

}
