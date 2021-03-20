<?php

class SlytOption
{
    /**
     * Plugin version.
     * 
     * @var string
     */
    protected const PLUGIN_VERSION = '1.0.3';

    /**
     * Plugin name.
     * 
     * @var string
     */
    protected const PLUGIN_NAME = 'Subscriber Login for YouTube';

    /**
     * Plugin slug.
     * 
     * @var string
     */

    protected const PLUGIN_SLUG = 'subscriber-login-for-youtube';

    /**
     * Option name.
     * 
     * @var string
     */
    protected const OPTION_NAME = 'subscriber_login_for_youtube';

    /**
     * Default value for the option.
     * 
     * @var array
    */
    protected const DEFAULT = array(
        'youtube_channel_id' => '',
        'youtube_channel_title' => '',
        'youtube_channel_uri' => '',
        'google_client_id' => '',
        'google_client_secret' => '',
        'google_client_redirect_uri' => '',
        'google_client_approval_prompt' => 1,
        'user_profile' => 0,
        'default_role' => 'subscriber'
    );

    /**
     * The option stored in WP db.
     *
     * @var array
     */
    protected $option;

    /**
     * Instance of SlytOption.
     *
     * @return void
     */
    public function __constructor() {
        $this->option = get_option( self::OPTION_NAME );
    }

    /**
     * Set the option as the default values.
     *
     * @return void
     */
    public function default() {
        return current_user_can( 'manage_options' ) && update_option( self::OPTION_NAME, self::DEFAULT, false );
    }

    /**
     * Get the option stored in WP db.
     *
     * @return array
     */
    public function all() {
        $this->option = get_option( self::OPTION_NAME );
        foreach( self::DEFAULT as $key => $value ) {
            $value = isset( $this->option[$key] ) ? $this->option[$key] : $value;
            $this->option[$key] = $value;
        }

        return $this->option;
    }

    /**
     * Update the option.
     *
     * @param array $option
     * @return bool
     */
    public function update( $option = [] ) {
        return current_user_can( 'manage_options' ) && update_option( self::OPTION_NAME, $option, false );
    }

    /**
     * Delete the option.
     *
     * @return bool
     */
    public function delete() {
        return current_user_can( 'manage_options' ) && delete_option( self::OPTION_NAME );
    }

    /**
     * Get the option with $key.
     *
     * @param string $key 
     * @param mixed $default
     * @return mixed
     */
    public function get( $key = '', $default = null ) {
        $option = $this->all();

        return $this->exists( $key ) ? $option[$key] : $default;
    }

    /**
     * Set the option with $key.
     *
     * @param string $key 
     * @param mixed $value
     * @return bool
     */
    public function set( $key = '', $value = null ) {
        $option = $this->all();
        $option[$key] = $value;
        
        return $this->update( $option );
    }

    /**
     * Check if $key is an option key.
     *
     * @param string $key 
     * @return bool
     */
    public function exists($key = '') {
        return array_key_exists( $key, self::DEFAULT );
    }

    /**
     * Get the plugin slug.
     *
     * @return string
     */
    public function get_plugin_slug() {
        return self::PLUGIN_SLUG;
    }

    /**
     * Get the plugin name.
     *
     * @return string
     */
    public function get_plugin_name() {
        return self::PLUGIN_NAME;
    }

    /**
     * Get the option name.
     *
     * @return string
     */
    public function get_name() {
        return self::OPTION_NAME;
    }

    /**
     * Get the plugin version.
     *
     * @return string
     */
    public function get_version() {
        return self::PLUGIN_VERSION;
    }

    /**
     * Get the default option.
     *
     * @return array
     */
    public function get_default() {
        return self::DEFAULT;
    }

    /**
     * Get the option stored in WP db.
     *
     * @return array
     */
    public function get_option() {
        return $this->all();
    }

    /**
     * Get the OAuth 2.0 options.
     *
     * @return array
     */
    public function get_oauth2_config(){
        $option = $this->all();
		$config = [
			'client_id' => $option['google_client_id'],
			'client_secret' => $option['google_client_secret'],
			'redirect_uri' => $option['google_client_redirect_uri'],
			'youtube_channel_id' => $option['youtube_channel_id']
		];

		return $config;
	}

    /**
     * Get the YouTube channel information.
     *
     * @return array
     */
    public function get_youtube_channel_info(){
        $option = $this->all();
		$info = [
            'youtube_channel_id' => $option['youtube_channel_id'],
			'youtube_channel_title' => $option['youtube_channel_title'],
			'youtube_channel_uri' => $option['youtube_channel_uri']
		];

		return $info;
	}

    /**
     * Check if the value of some option is an integer.
     *
     * @param string $key
     * @return bool
     */
    public function is_int( $key = '' ) {
        return is_int( self::DEFAULT[$key] );
    }

    /**
     * Check if the value of some option is a string.
     *
     * @param string $key
     * @return bool
     */
    public function is_string( $key = '' ) {
        return is_string( self::DEFAULT[$key] );
    }

    /**
     * Add the option name as a prefix of $text.
     *
     * @param string $text
     * @return string
     */
    public function add_name( $text = '' ) {
		return self::OPTION_NAME . '_' . $text;
	}

    /**
     * Remove the option name of $text.
     *
     * @param string $text
     * @return string
     */
	public function remove_name($text = '') {
		return str_replace( self::OPTION_NAME . '_', '', $text );
	}
}
