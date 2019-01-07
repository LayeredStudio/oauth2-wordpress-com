<?php
namespace Layered\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class WordPressCom extends AbstractProvider
{
	use BearerAuthorizationTrait;

	/**
	 * @var string If set, this will be sent to WordPress.com as the "blog" parameter.
	 * @link https://developer.wordpress.com/docs/oauth2/#receiving-an-access-token
	 */
	protected $blog;

	/**
	 * Get authorization url to begin OAuth flow
	 *
	 * @return string
	 */
	public function getBaseAuthorizationUrl()
	{
		return 'https://public-api.wordpress.com/oauth2/authorize';
	}

	/**
	 * Get access token url to retrieve token
	 *
	 * @return string
	 */
	public function getBaseAccessTokenUrl(array $params)
	{
		return 'https://public-api.wordpress.com/oauth2/token';
	}

	protected function getAuthorizationParameters(array $options)
	{
		$params = array_merge(
			parent::getAuthorizationParameters($options),
			array_filter([
				'blog'	=>	$this->blog
			])
		);
		return $params;
	}

	/**
	 * Get provider url to fetch user details
	 *
	 * @return string
	 */
	public function getResourceOwnerDetailsUrl(AccessToken $token)
	{
		return 'https://public-api.wordpress.com/rest/v1/me';
	}

	/**
	 * Default scope for WordPress.com. Possible values:
	 * - 'auth' for authentication only, grants access to /me endpoints
	 * - 'global' access to all user's sites
	 * - '' (empty) access to a single blog, specified in request or chosen by user
	 * @link https://developer.wordpress.com/docs/oauth2/#receiving-an-access-token
	 * @link https://developer.wordpress.com/docs/wpcc/
	 *
	 * @return array
	 */
	protected function getDefaultScopes()
	{
		return ['auth'];
	}

	/**
	 * Check a provider response for errors.
	 *
	 * @return void
	 */
	protected function checkResponse(ResponseInterface $response, $data)
	{
		if (!empty($data['error'])) {
			$message = $data['error_description'] . ' (' . $data['error'] . ')';
			throw new IdentityProviderException($message, 0, $data);
		}
	}

	/**
	 * Generate a user object from a successful user details request.
	 *
	 * @return League\OAuth2\Client\Provider\ResourceOwnerInterface
	 */
	protected function createResourceOwner(array $response, AccessToken $token)
	{
		return new WordPressComUser($response);
	}
}
