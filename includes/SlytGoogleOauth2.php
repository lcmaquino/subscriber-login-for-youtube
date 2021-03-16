<?php

require_once( SLYT_INCLUDES_PATH . '/SlytGoogleUser.php' );
require_once( SLYT_INCLUDES_PATH . '/SlytHttpClient.php' );

class SlytGoogleOauth2
{
    /**
     * The state of the web app.
     * 
     * @var string
     */
    protected $state;

    /**
     * The SlytHttpClient instance.
     *
     * @var SlytHttpClient
     */
    protected $httpClient;

    /**
     * The client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * The redirect URL.
     *
     * @var string
     */
    protected $redirectUri;

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * The custom parameters to be sent with the request.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The type of the encoding in the query.
     *
     * @var int Can be either PHP_QUERY_RFC3986 or PHP_QUERY_RFC1738.
     */
    protected $encodingType = PHP_QUERY_RFC1738;

    /**
     * The cached user instance.
     *
     * @var 
     */
    protected $user;

    /**
     * Create a new GoogleOAuth2Manager instance.
     * 
     * @param  array  $config
     * @return void
     */
    public function __construct($config = [])
    {
        $this->setConfig($config);
        $this->scopes = [
            'openid',
            'email'
        ];
        $this->httpClient = new SlytHttpClient();
        $this->user = null;
    }

    /**
     * Set the state of the web app.
     *
     * @param  string $state
     * @return $this
     */
    protected function setState($state = null)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Get the state of the web app.
     *
     * @return string|null
     */
    protected function getState()
    {
        return $this->state;
    }

    /**
     * Set the SlytHttpClient instance.
     *
     * @param  SlytHttpClient  $client
     * @return $this
     */
    protected function setHttpClient($client = null)
    {
        $this->httpClient = $client;
        return $this;
    }

    /**
     * Get a instance of the SlytHttpClient.
     *
     * @return SlytHttpClient
     */
    protected function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Set the redirect URL.
     *
     * @param  string  $url
     * @return $this
     */
    public function setRedirectUri($url)
    {
        $this->redirectUri = $url;

        return $this;
    }

    /**
     * get the redirect URI.
     *
     * @param  string  $url
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * Set the scopes of the requested access.
     *
     * @param  array $scopes
     * @return $this
     */
    public function scopes($scopes = [])
    {
        $this->scopes = array_unique($scopes);

        return $this;
    }

    /**
     * Get the current scopes.
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Set the configuration of the Google OAuth 2.0 client.
     *
     * The $config parameter should be formatted as:
     *   [
     *     'client_id' => string,
     *     'client_secret' => string,
     *     'redirect_uri' => string
     *   ]
     *
     * @param array  $config
     * @return $this
     */
    public function setConfig($config = []){
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->redirectUri = $config['redirect_uri'];
        return $this;
    }

    /**
     * Get the configuration of the Google OAuth 2.0 client.
     *
     * @return void
     */
    public function getConfig(){
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'client_redirect' => $this->redirectUri,
        ];
    }

    /**
     * Set the custom parameters of the request.
     *
     * @param  array  $parameters
     * @return $this
     */
    public function with(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get the custom parameters of the request.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get the authentication URL for Google OAuth 2.0 API.
     *
     * @param  string  $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        $base = 'https://accounts.google.com/o/oauth2/auth';

        return $this->buildAuthUrlFromBase($base, $state);
    }

    /**
     * Get the token URL for the Google OAuth 2.0 API.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    /**
     * Get the user info URL for the Google OAuth 2.0 API.
     *
     * @return string
     */
    protected function getUserInfoUrl()
    {
        return 'https://www.googleapis.com/oauth2/v3/userinfo';
    }

    /**
     * Get the revoke token URL for the Google OAuth 2.0 API.
     *
     * @return string
     */
    protected function getRevokeTokenUrl()
    {
        return 'https://accounts.google.com/o/oauth2/revoke';
    }

    /**
     * Get the GET fields for the code request.
     *
     * @param  string|null  $state
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        $fields = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => $this->formatScopes($this->scopes),
            'response_type' => 'code',
        ];

        if ( $state ) {
            $fields['state'] = $state;
        }

        return array_merge($fields, $this->parameters);
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code = '')
    {
        return [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
        ];
    }

    /**
     * Get the GET fields for the user info request.
     *
     * @param  string  $token
     * @return array
     */
    protected function getUserInfoFields($token = '')
    {
        return [
            'access_token' => $token,
        ];
    }

    /**
     * Get the POST fields for the refresh token request.
     *
     * @param  string  $refresh_token
     * @return array
     */
    protected function getRefreshTokenFields($refresh_token = '')
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token',
        ];
    }

    /**
     * Get the GET fields for the revoke token request.
     *
     * @param  string  $token
     * @return array
     */
    protected function getRevokeTokenFields($token = '')
    {
        return [
            'token' => $token,
        ];
    }

    /**
     * Build the authentication URL from the given base URL and state.
     *
     * @param  string  $url
     * @param  string  $state
     * @return string
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        $query = http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);

        return $url . '?' . $query;
    }

    /**
     * Format the given scopes.
     *
     * @param  array  $scopes
     * @return string
     */
    protected function formatScopes(array $scopes)
    {
        $scopeSeparator = ' ';

        return implode($scopeSeparator, $scopes);
    }

    /**
     * Get the access token response for the given code.
     *
     * @param  string  $code
     * @return array
     */
    protected function getAccessTokenResponse($code = '')
    {
        $response = empty($code) ? null : 
            $this->getHttpClient()->post(
                $this->getTokenUrl(),
                $this->getTokenFields($code)
            );

        return $response;
    }

    /**
     * Get the raw user attributes for the given access token.
     *
     * @param  string  $token
     * @return array
     */
    protected function getUserInfoResponse($token = '')
    {
        $response = empty($token) ? null : 
            $this->getHttpClient()->get(
                $this->getUserInfoUrl(), 
                $this->getUserInfoFields($token)
            );

        return $response;
    }

    /**
     * Get the refresh token response.
     *
     * @param  string  $refresh_token
     * @return array
     */
    protected function getRefreshTokenResponse($refresh_token = '')
    {
        $response = empty($refresh_token) ? null : 
            $this->getHttpClient()->post(
                $this->getTokenUrl(), 
                $this->getRefreshTokenFields($refresh_token)
            );

        return $response;
    }
    
    /**
     * Get the revoke token response.
     *
     * @param  string  $token
     * @return array
     */
    protected function getRevokeTokenResponse($token = '')
    {
        $response = empty($token) ? null :
            $this->getHttpClient()->get(
                $this->getRevokeTokenUrl(),
                $this->getRevokeTokenFields($token)
            );

        return $response;
    }

    /**
     * Map the raw user array to a SlytGoogleUser instance.
     *
     * @param  array  $rawAttributes
     * @return SlytGoogleUser
     */
    protected function mapUserToObject( array $rawAttributes )
    { 
        $attributes = [
            'sub' => null,
            'name' => '',
            'given_name' => '',
            'family_name' => '',
            'locale' => '',
            'email' => null,
            'email_verified' => 0,
            'picture' =>  null,
        ];

        foreach ($attributes as $key => $value) {
            $attributes[$key] = isset( $rawAttributes[$key] ) ? $rawAttributes[$key] : $attributes[$key];
        }

        $gu = new SlytGoogleUser();
        
        return $gu->map($attributes);
    }

    /**
     * Determine if the GoogleOAuth2 is operating with state.
     *
     * @return bool
     */
    protected function usesState()
    {
        return !empty( $this->state );
    }

    /**
     * Determine if the GoogleOAuth2 is operating as stateless.
     *
     * @return bool
     */
    protected function isStateless()
    {
        return !$this->usesState();
    }

    /**
     * Indicates that the GoogleOAuth2 should operate as stateless.
     *
     * @return $this
     */
    public function stateless()
    {
        $this->state = null;

        return $this;
    }

    /**
     * Determine if the current state of the web app has a mismatching "state".
     * 
     * @param  string $input_state
     * @return bool
     */
    protected function hasInvalidState($state = null, $session_state = null)
    {
        return ($state !== $session_state);
    }

    /**
     * Redirect the user from the application to the Google authentication page.
     *
     * @return string
     */
    public function redirect($state = null)
    {
        return $this->getAuthUrl($state);
    }

    /**
     * Get the user coming from Google authentication request.
     *
     * @param string $code
     * @param string $state
     * @param string $session_state
     * @return SlytGoogleUser|null
     */
    public function user( $code = '', $state = null, $session_state = null )
    {
        if ( !empty( $this->user ) ) {
            return $this->user;
        }

        if ( $this->hasInvalidState( $state, $session_state ) ) {
            return null;
        }

        $response = $this->getAccessTokenResponse($code);

        $token = $response && isset( $response['access_token'] ) ? $response['access_token'] : null;

        if( empty( $token ) ) {
            return null;
        }

        $user = $this->getUserFromToken( $token );
        $refresh_token = isset( $response['refresh_token'] ) ? $response['refresh_token'] : null;
        $expires_in = $response['expires_in'];

        return empty( $user ) ? null : 
            $this->user
                ->setRefreshToken( $refresh_token )
                ->setExpiresIn( $expires_in );
    }

    /**
     * Get a SlytGoogleUser instance from a known access token.
     *
     * @param  string  $token
     * @return SlytGoogleUser
     */
    public function getUserFromToken($token = '')
    {
        $response = $this->getUserInfoResponse($token);

        if(empty($response) || isset($response['error'])) {
            return null;
        }

        $this->user = $this->mapUserToObject($response);

        return $this->user->setToken($token);
    }

    /**
     * Refresh the user's token and returns the new one.
     * Returns null if the token was not refreshed.
     * 
     * @param  string  $refresh_token
     * @return string|null
     */
    public function refreshUserToken($refresh_token = ''){
        $response = $this->getRefreshTokenResponse($refresh_token);

        return isset($response['access_token']) ? $response['access_token'] : null;
    }

    /**
     * Revoke the user's access token and refresh token (at the same time).
     * The $token parameter can be the access token or the refresh token.
     * Returns true if the token was revoked and false otherwise.
     * 
     * @param  string  $token
     * @return boolean
     */
    public function revokeToken($token = ''){
        $response = $this->getRevokeTokenResponse($token);
        
        return empty($response);
    }
}