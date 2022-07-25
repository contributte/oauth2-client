<?php declare(strict_types = 1);

namespace Contributte\OAuth2Client\UI\Components;

use Contributte\OAuth2Client\Flow\AuthCodeFlow;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Nette\Application\UI\Control;
use Nette\Bridges\ApplicationLatte\Template;
use UnexpectedValueException;

class GenericAuthControl extends Control
{

	/** @var AuthCodeFlow */
	private $authCodeFlow;

	/** @var string|null */
	private $redirectUri = null;

	/** @var string|null */
	private $templatePath = null;

	/** @var array<callable>  */
	public $onAuthenticated = [];

	/** @var array<callable>  */
	public $onFailed = [];

	public function __construct(AuthCodeFlow $authCodeFlow, ?string $redirectUri = null)
	{
		$this->authCodeFlow = $authCodeFlow;
		$this->redirectUri = $redirectUri;
	}

	public function setTemplate(string $templatePath): void
	{
		$this->templatePath = $templatePath;
	}

	public function handleAuthenticate(): void
	{
		$this->authenticate();
	}

	public function authenticate(): void
	{
		$this->getPresenter()->redirectUrl(
			$this->authCodeFlow->getAuthorizationUrl(['redirect_uri' => $this->redirectUri])
		);
	}

	public function authorize(): ?ResourceOwnerInterface
	{
		try {
			$accessToken = $this->authCodeFlow->getAccessToken($this->getPresenter()->getHttpRequest()->getQuery());
			if (!$accessToken instanceof AccessToken) {
				throw new UnexpectedValueException();
			}

			$user = $this->authCodeFlow->getProvider()->getResourceOwner($accessToken);
			$this->authenticationSucceed($accessToken, $user);
			return $user;
		} catch (IdentityProviderException $e) {
			$this->authenticationFailed();
		}

		return null;
	}

	protected function authenticationFailed(): void
	{
		$this->onFailed();
	}

	protected function authenticationSucceed(AccessToken $accessToken, ResourceOwnerInterface $user): void
	{
		$this->onAuthenticated($accessToken, $user);
	}

	public function render(): void
	{
		$template = $this->getTemplate();
		if (!$template instanceof Template) {
			throw new UnexpectedValueException();
		}

		$template->render($this->templatePath ?? __DIR__ . '/GenericAuthControl.latte');
	}

}
