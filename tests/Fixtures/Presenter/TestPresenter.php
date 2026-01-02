<?php declare(strict_types = 1);

namespace Tests\Fixtures\Presenter;

use Nette\Application\Response;
use Nette\Application\UI\Component;
use Nette\Application\UI\Presenter;
use Nette\Http\Request as HttpRequest;
use Nette\Http\Response as HttpResponse;
use Nette\Http\UrlScript;

final class TestPresenter extends Presenter
{

	public HttpRequest $httpRequest;

	public HttpResponse $httpResponse;

	public Response $response;

	private Component $component;

	public function __construct(Component $component, ?HttpRequest $request = null, ?HttpResponse $response = null)
	{
		parent::__construct();

		$httpRequest = $request ?? new HttpRequest(new UrlScript('http://localhost/page'));
		$httpResponse = $response ?? new HttpResponse();

		$this->injectPrimary(
			$httpRequest,
			$httpResponse
		);
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

}
