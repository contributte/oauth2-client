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

	/** @var Component */
	private $component;

	/** @var HttpRequest */
	public $httpRequest;

	/** @var HttpResponse */
	public $httpResponse;

	/**	@var Response */
	public $response;

	public function __construct(Component $component, ?HttpRequest $request = null, ?HttpResponse $response = null)
	{
		parent::__construct();
		$this->injectPrimary(
			null,
			null,
			null,
			$request ?? new HttpRequest(new UrlScript('http://localhost/page')),
			$response ?? new HttpResponse()
		);
		$this->component = $component;
		$this->getComponent('subject');
	}

	protected function createComponentSubject(): Component
	{
		return $this->component;
	}

	public function sendResponse(Response $response): void
	{
		$this->response = $response;
		parent::sendResponse($response);
	}

}
