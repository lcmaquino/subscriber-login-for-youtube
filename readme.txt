=== Subscriber Login for YouTube ===
Contributors: lcmaquino
Tags: oauth2, youtube-api-v3, youtube sign in
Donate link: https://www.professoraquino.com.br/ajude
Requires at least: 4.9
Tested up to: 5.7
Stable tag: 1.0.3
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Introduction

Subscriber Login for YouTube is a WordPress plugin for Google OAuth 2.0 authentication with YouTube Google account.

### Features

- Authentication — get the user coming from Google authentication;
- Get user information — get the user data from their Google account;
- Refresh token — refresh the user's access token;
- Revoke token — revoke the user's access token;
- Check if an user is subscribed on a given YouTube channel;

For more information about Google OAuth 2.0, please see [Using OAuth 2.0 for Web Server Applications](https://developers.google.com/identity/protocols/oauth2/web-server)

## Requirements

- PHP 7.0 or later;
- WordPress 4.9 or later;

## Installation

### Automatic installation

1. Open the WordPress admin screen for your site. Navigate to "Plugins > Add New", and search for Subscriber Login for YouTube.
2. Click the "Install Now" button.
3. Then activate the Subscriber Login for YouTube plugin.
4. Go to the "Settings > Subscriber Login for YouTube" to set up your Google OAuth 2.0 client and YouTube channel information.
5. Test the configuration by clicking the sign in button on WordPress login page.

### Manual installation

1. Download [Subscriber Login for YouTube](https://downloads.wordpress.org/plugin/subscriber-login-for-youtube.zip).
2. Upload Subscriber Login for YouTube through "Plugins > Add New > Upload" admin screen or upload subscriber-login-for-youtube folder to the `/wp-content/plugins/` directory.
3. Activate the Subscriber Login for YouTube plugin through the 'Plugins' menu in WordPress.
4. Go to the "Settings > Subscriber Login for YouTube" to set up your Google OAuth 2.0 client and YouTube channel information.
5. Test the configuration by clicking the sign in button on WordPress login page.

## Setting

Before using Subscriber Login for YouTube, you need to set up an Google project for web application in [Google Console](https://console.developers.google.com/). It's a **Google requirement** for users to login! The web application project will provide: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and `GOOGLE_REDIRECT_URI`.

Activate the "YouTube Data API v3" in your project and add the scopes `openid`, `email`, and `https://www.googleapis.com/auth/youtube.readonly` for its consent screen. See more details in [Setting up OAuth 2.0](https://support.google.com/cloud/answer/6158849?hl=en).

### Access Scopes

The scopes are used by Google to limit your application access to the user account data. Read more about [YouTube Data API](https://developers.google.com/youtube/v3/guides/auth/server-side-web-apps?hl=pt-br) scopes.

## Usage

After you activated the plugin, then the plugin will automatically:

* add the "Sign in with YouTube" button to the WordPress login page. See screenshot #1.
* add the "Revoke access" link to the WordPress profile page. See screenshot #2.

== Screenshots ==

1. "Sign in with YouTube" button on the main WP login page.
2. "Revoke access" link on the WordPress profile page.
3. Subscriber Login for YouTube on the WordPress Settings page.

== Changelog ==

= 1.0.3 =
* Update version on `includes/SlytOption.php`.

= 1.0.2 =
* Add assets directory for release on WordPress Plugin Directory.
* Change plugin license to GPLv2 or later to match with WordPress license.
* Update readme.txt.

= 1.0.1 =
* Fix issues in WordPress Plugin Review #1.

= 1.0.0 =
* Initial plugin version.

## License

Subscriber Login for YouTube is open-sourced software licensed under the [GPL v2.0 or later](https://github.com/lcmaquino/subscriber-login-for-youtube/blob/main/LICENSE).