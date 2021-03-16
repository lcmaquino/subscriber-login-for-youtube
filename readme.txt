=== Subscriber Login for YouTube ===
Contributors: lcmaquino
Tags: oauth2, youtube-api-v3, youtube sign in
Donate link: https://www.professoraquino.com.br/ajude
Requires PHP: 7.0
Requires at least: 4.9
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

## Introduction

Subscriber Login for YouTube is a WordPress plugin for Google 
OAuth 2.0 authentication with YouTube Google account.

### Features

- Authentication — get the user coming from Google authentication;
- Get user information — get the user data from their Google account;
- Refresh token — refresh the user's access token;
- Revoke token — revoke the user's access token;
- Check if an user is subscribed on a given YouTube channel;

For more information about Google OAuth 2.0, please see 
https://developers.google.com/identity/protocols/oauth2/web-server

## Requirements

- PHP 7.0 or later;
- WordPress 4.9 or later;

## Installation

[Download](https://github.com/lcmaquino/subscriber-login-for-youtube/archive/main.zip) 
the plugin zip file and extract it to the directory:
```
WP_ROOT/wp-content/plugins/subscriber-login-for-youtube
```

Navigate to your WordPress Plugins admin screen and locate the Subscriber Login 
for YouTube in the list. Click the plugin's "Activate" link.

## Setting

Before using Subscriber Login for YouTube, you need to set up an Google project 
for web application in [Google Console](https://console.developers.google.com/). 
It's a **Google requirement** for users to login! The web application project will 
provide: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and `GOOGLE_REDIRECT_URI`.

Activate the "YouTube Data API v3" in your project and add the scopes 
`openid`, `email`, and `https://www.googleapis.com/auth/youtube.readonly` 
for its consent screen. See more details in 
[Setting up OAuth 2.0](https://support.google.com/cloud/answer/6158849?hl=en).

### Access Scopes

The scopes are used by Google to limit your application access to the user account
data. Read more about 
[YouTube Data API](https://developers.google.com/youtube/v3/guides/auth/server-side-web-apps?hl=pt-br)
scopes.

## License

Subscriber Login for YouTube is open-sourced software licensed under the
[GPL v3.0 or later](https://github.com/lcmaquino/subscriber-login-for-youtube/blob/main/LICENSE).
