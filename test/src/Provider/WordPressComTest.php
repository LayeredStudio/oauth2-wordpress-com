<?php 
namespace Layered\OAuth2\Client\Test\Provider;

use Layered\OAuth2\Client\Provider\WordPressCom;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;
use Mockery as m;

final class WordPressComTest extends TestCase
{
	protected $provider;

	protected function setUp()
	{
		$this->provider = new WordPressCom([
			'clientId'		=>	'mock_client_id',
			'clientSecret'	=>	'mock_client_secret',
			'redirectUri'	=>	'none'
		]);
	}

	public function testAuthorizationUrl()
	{
		$url = $this->provider->getAuthorizationUrl();
		$uri = parse_url($url);
		parse_str($uri['query'], $query);

		$this->assertArrayHasKey('client_id', $query);
		$this->assertArrayHasKey('redirect_uri', $query);
		$this->assertArrayHasKey('state', $query);
		$this->assertArrayHasKey('scope', $query);
		$this->assertArrayHasKey('response_type', $query);
		$this->assertArrayHasKey('approval_prompt', $query);
		$this->assertNotEmpty($this->provider->getState());

		$this->assertEquals('/oauth2/authorize', $uri['path']);
	}

	public function testGetBaseAccessTokenUrl()
	{
		$url = $this->provider->getBaseAccessTokenUrl([]);
		$uri = parse_url($url);

		$this->assertEquals('/oauth2/token', $uri['path']);
	}

	public function testGetAccessToken()
	{
		$mockToken = $this->getMockToken();

		$response = m::mock('Psr\Http\Message\ResponseInterface');
		$response->shouldReceive('getBody')->andReturn(json_encode($mockToken));
		$response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
		$response->shouldReceive('getStatusCode')->andReturn(200);

		$client = m::mock('GuzzleHttp\ClientInterface');
		$client->shouldReceive('send')->once()->andReturn($response);
		$this->provider->setHttpClient($client);

		$token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

		$this->assertInstanceOf(AccessTokenInterface::class, $token);
		$this->assertEquals('mock_access_token', $token->getToken());
		$this->assertNull($token->getExpires());
		$this->assertNull($token->getRefreshToken());
		$this->assertNull($token->getResourceOwnerId());
		$this->assertArrayHasKey('blog_id', $token->getValues());
		$this->assertArrayHasKey('blog_url', $token->getValues());
	}

	public function testResourceOwnerDetailsUrl()
	{
		$url = $this->provider->getResourceOwnerDetailsUrl($this->getMockToken());
		$this->assertEquals('https://public-api.wordpress.com/rest/v1/me', $url);
	}

	public function testUserData()
	{
		$mockUser = [
			'ID'				=>	12345,
			'display_name'		=>	'Mock Name',
			'username'			=>	'mockuser',
			'email'				=>	'mockuser@example.com',
			'primary_blog'		=>	9999999,
			'primary_blog_url'	=>	'https://example.com',
			'avatar_URL'		=>	'https://gravatar.com/avatar/17404A596CBD0D1E6C7D23FCD845AB82'
		];

		$userResponse = m::mock('Psr\Http\Message\ResponseInterface');
		$userResponse->shouldReceive('getBody')->andReturn(json_encode($mockUser));
		$userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
		$userResponse->shouldReceive('getStatusCode')->andReturn(200);

		$client = m::mock('GuzzleHttp\ClientInterface');
		$client->shouldReceive('send')->once()->andReturn($userResponse);
		$this->provider->setHttpClient($client);

		$user = $this->provider->getResourceOwner($this->getMockToken());

		$this->assertInstanceOf(ResourceOwnerInterface::class, $user);
		$this->assertEquals($mockUser['ID'], $user->getId());
		$this->assertEquals($mockUser['username'], $user->getUsername());
		$this->assertEquals($mockUser['email'], $user->getEmail());
		$this->assertNotEmpty($user->getAvatarUrl());
	}

	/**
	 * @expectedException \League\OAuth2\Client\Provider\Exception\IdentityProviderException
	 */
	public function testUserDataFails()
	{
		$userResponse = m::mock('Psr\Http\Message\ResponseInterface');
		$userResponse->shouldReceive('getBody')->andReturn('{"error": "invalid_request","error_description": "Unknown request"}');
		$userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
		$userResponse->shouldReceive('getStatusCode')->andReturn(rand(400,600));

		$client = m::mock('GuzzleHttp\ClientInterface');
		$client->shouldReceive('send')->once()->andReturn($userResponse);
		$this->provider->setHttpClient($client);

		$user = $this->provider->getResourceOwner($this->getMockToken());
	}

	protected function getMockToken(): AccessToken
	{
		return new AccessToken([
			'access_token'	=>	'mock_access_token',
			'scope'			=>	'auth',
			'token_type'	=>	'bearer',
			'blog_id'		=>	9999999,
			'blog_url'		=>	'https://example.com'
		]);
	}

	public function tearDown()
	{
		m::close();
		parent::tearDown();
	}

}
