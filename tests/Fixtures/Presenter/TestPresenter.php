<?php declare(strict_types = 1);

namespace Tests\Fixtures\Presenter;

use Nette\Application\Response;
use Nette\Application\UI\Component;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Request as HttpRequest;
use Nette\Http\Response as HttpResponse;
use Nette\Http\UrlScript;
use ReflectionMethod;

final class TestPresenter extends Presenter
{

	public Response $response;

	private Component $component;

	public function __construct(Component $component, ?HttpRequest $request = null, ?HttpResponse $response = null)
	{
		parent::__construct();

		$httpRequest = $request ?? new HttpRequest(new UrlScript('http://localhost/page'));
		$httpResponse = $response ?? new HttpResponse();

		$this->injectPrimaryCompat($httpRequest, $httpResponse);
		$this->component = $component;
		$this->getComponent('subject');
	}

	public function sendResponse(Response $response): void
	{
		$this->response = $response;

		parent::sendResponse($response);
	}

	protected function createComponentSubject(): Component
	{
		return $this->component;
	}

	/**
	 * Compatibility wrapper for injectPrimary across Nette versions
	 */
	private function injectPrimaryCompat(IRequest $httpRequest, IResponse $httpResponse): void
	{
		$method = new ReflectionMethod(Presenter::class, 'injectPrimary');
		$params = $method->getParameters();
		$firstParamName = $params[0]->getName();

		if ($firstParamName === 'httpRequest') {
			// Nette 3.2.x older signature
			$this->injectPrimary($httpRequest, $httpResponse);
		} else {
			// Nette 3.2.x newer signature with $context first
			$this->injectPrimary(null, $httpRequest, $httpResponse);
		}
	}

}
