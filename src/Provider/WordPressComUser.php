<?php 
namespace Layered\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class WordPressComUser implements ResourceOwnerInterface
{
	use ArrayAccessorTrait;

	/**
	 * Raw response
	 *
	 * @var array
	 */
	protected $response;

	/**
	 * Creates new resource owner.
	 */
	public function __construct(array $response)
	{
		$this->response = $response;
	}

	/**
	 * Get resource owner id
	 *
	 * @return string|null
	 */
	public function getId()
	{
		return $this->getValueByKey($this->response, 'ID');
	}

	/**
	 * Get resource owner email
	 *
	 * @return string|null
	 */
	public function getEmail()
	{
		return $this->getValueByKey($this->response, 'email');
	}

	/**
	 * Get resource owner name
	 *
	 * @return string|null
	 */
	public function getDisplayName()
	{
		return $this->getValueByKey($this->response, 'display_name');
	}

	/**
	 * Alias for @getDisplayName()
	 *
	 * @return string|null
	 */
	public function getName()
	{
		return $this->getValueByKey($this->response, 'display_name');
	}

	/**
	 * Get resource owner username
	 *
	 * @return string|null
	 */
	public function getUsername()
	{
		return $this->getValueByKey($this->response, 'username');
	}

	/**
	 * Get resource owner link
	 *
	 * @return string|null
	 */
	public function getProfileUrl()
	{
		return $this->getValueByKey($this->response, 'profile_URL');
	}

	/**
	 * Get resource owner avatar url
	 *
	 * @return string|null
	 */
	public function getAvatarUrl()
	{
		return $this->getValueByKey($this->response, 'avatar_URL');
	}

	/**
	 * Get resource owner primary blog ID
	 *
	 * @return string|null
	 */
	public function getBlogId()
	{
		return $this->getValueByKey($this->response, 'primary_blog');
	}

	/**
	 * Get resource owner primary blog URL
	 *
	 * @return string|null
	 */
	public function getBlogUrl()
	{
		return $this->getValueByKey($this->response, 'primary_blog_url');
	}

	/**
	 * Get resource owner language
	 *
	 * @return string|null
	 */
	public function getLanguage()
	{
		return $this->getValueByKey($this->response, 'language');
	}

	/**
	 * Return all of the owner details available as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->response;
	}
}
