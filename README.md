# WordPress.com provider for OAuth 2.0 Client

This package provides [WordPress.com OAuth 2.0](https://developer.wordpress.com/docs/oauth2/) support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Requirements

This package uses [WordPress.com Connect](https://developer.wordpress.com/docs/wpcc/) to authenticate users with WordPress.com accounts.

Requirements to use this package:
- PHP >= 5.6
- a WordPress client ID and client secret, referred to as `{wordpress-client-id}` and `{wordpress-client-secret}`. Follow the [WordPress Apps](https://developer.wordpress.com/apps/) instructions to create the required credentials

## Installation

Use composer to install:

```sh
composer require layered/oauth2-wordpress-com
```

## Usage

Usage is the same as The League's Abstract OAuth client, using `\Layered\OAuth2\Client\Provider\WordPressCom` as the provider.

### Authorization Code Flow

```php
use Layered\OAuth2\Client\Provider\WordPressCom;

$provider = new WordPressCom([
	'clientId'		=>	'{wordpresscom-client-id}',
	'clientSecret'	=>	'{wordpresscom-client-secret}',
	'redirectUri'	=>	'https://example.com/callback-url',
	'blog'			=>	'https://example.com'		// optional - request auth for a specific blog
]);

if (isset($_GET['error'])) {	// Got an error, probably user denied access
	
	exit('Error: ' . htmlspecialchars($_GET['error_description'] . ' (' . $_GET['error_description'] . ')', ENT_QUOTES, 'UTF-8'));

} elseif (!isset($_GET['code'])) {	// If we don't have an authorization code then get one

	$authUrl = $provider->getAuthorizationUrl();
	$_SESSION['oauth2state'] = $provider->getState();
	header('Location: '. $authUrl);
	exit;

} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {	// Check given state against previously stored one to mitigate CSRF attack

	unset($_SESSION['oauth2state']);
	exit('Invalid state');

} else {

	// Try to get an access token (using the authorization code grant)
	$token = $provider->getAccessToken('authorization_code', [
		'code' => $_GET['code']
	]);

	// If auth was for a single site or global access, token contains extra blog info
	$tokenValues = $token->getValues();
	echo 'Blog ID: ' . $tokenValues['blog_id'] . '<br>';
	echo 'Blog URL: ' . $tokenValues['blog_url'] . '<br>';

	// Get user profile data
	try {

		// We got an access token, let's now get the user's details
		$user = $provider->getResourceOwner($token);

		// Use these details to create a new profile
		printf('Hello %s!', $user->getName());

	} catch (\Exception $e) {

		// Failed to get user details
		exit('Something went wrong: ' . $e->getMessage());
	}

	// Use this to interact with an API on the users behalf
	echo $token->getToken();
}
```

#### Available Options

The `WordPressCom` provider has the following [options](https://developer.wordpress.com/docs/oauth2/#receiving-an-access-token):

- `blog` can be a blog URL or blog ID for a WordPress.com blog or Jetpack site
- `scope` to request access to additional data


## Scopes

When creating the authorization URL, specify the scope your application may authorize. Available scopes for WordPress.com:

- `auth` for authentication only, grants access to /me endpoints
- `global` access to all user's sites and data
- '' (*empty*) access to a single blog, specified in request or chosen by user

#### Get access to user profile

```php
$provider->getAuthorizationUrl([
	'scope'	=>	'auth'
]);
```

#### Get access to user profile & a single blog

```php
$provider->getAuthorizationUrl([
	'scope'	=>	''
]);
```

## Testing

```sh
composer test
```

## Credits

- [Layered](https://github.com/LayeredStudio)
- [All Contributors](https://github.com/LayeredStudio/oauth2-wordpress-com/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/LayeredStudio/oauth2-wordpress-com/blob/master/LICENSE) for more information.
