<?php declare(strict_types = 1);

namespace Tests\Contributte\OAuth2Client\Unit\Flow;

use Contributte\OAuth2Client\Exception\Logical\InvalidArgumentException;
use Contributte\OAuth2Client\Exception\Runtime\PossibleCsrfAttackException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Mockery;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use PHPUnit\Framework\TestCase;
use Tests\Contributte\OAuth2Client\Fixtures\Flow\TestAuthCodeFlow;
use Tests\Contributte\OAuth2Client\Fixtures\Provider\TestProvider;

class AuthCodeFlowTest extends TestCase
{

	public function testTokenMissingParameters1(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Missing "code" parameter');

		$session = Mockery::mock(Session::class);
		$flow = new TestAuthCodeFlow(new TestProvider(), $session);

		$flow->getAccessToken([]);
	}

	public function testTokenMissingParameters2(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Missing "state" parameter');

		$session = Mockery::mock(Session::class);
		$flow = new TestAuthCodeFlow(new TestProvider(), $session);

		$flow->getAccessToken(['code' => 'foo']);
	}

	public function testTokenCsrf(): void
	{
		$this->expectException(PossibleCsrfAttackException::class);

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
	}

	public function testTokenOk(): void
	{
		$token = Mockery::mock(AccessTokenInterface::class);

		$provider = Mockery::mock(AbstractProvider::class);
		$provider->shouldReceive('getAccessToken')
			->andReturn($token);

		$sessionSection = Mockery::mock(SessionSection::class);
		$sessionSection->shouldReceive('offsetExists')
			->andReturn(false);

		$session = Mockery::mock(Session::class);
		$session->shouldReceive('getSection')
			->andReturn($sessionSection);

		$flow = new TestAuthCodeFlow($provider, $session);

		$this->assertSame($token, $flow->getAccessToken(['code' => 'foo', 'state' => 'bar']));
	}

	public function testAuthUrl(): void
	{
		$provider = Mockery::mock(AbstractProvider::class);
		$provider->shouldReceive('getAuthorizationUrl')
			->andReturn('foo');
		$provider->shouldReceive('getState');

		$sessionSection = Mockery::mock(SessionSection::class);
		$sessionSection->shouldReceive('offsetSet');

		$session = Mockery::mock(Session::class);
		$session->shouldReceive('getSection')
			->andReturn($sessionSection);

		$flow = new TestAuthCodeFlow($provider, $session);

		$this->assertSame('foo', $flow->getAuthorizationUrl());
	}

}
