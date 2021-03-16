<?php

require_once( SLYT_INCLUDES_PATH . '/SlytSession.php' );
require_once( SLYT_INCLUDES_PATH . '/SlytYouTubeOauth2.php' );
require_once( SLYT_INCLUDES_PATH . '/SlytQuery.php' );
require_once( SLYT_INCLUDES_PATH . '/SlytOption.php' );
require_once( SLYT_ADMIN_PATH  . '/SlytUser.php' );
require_once( SLYT_ADMIN_PATH . '/SlytSetting.php' );

class SlytPlugin
{
    /**
     * The SlytSession instance.
     *
     * @var SlytSession
     */
    protected $session;

    /**
     * The SlytOption instance.
     *
     * @var SlytOption
     */
    protected $option;
    
    /**
     * The SlytQuery instance.
     *
     * @var SlytQuery
     */
    protected $query;

    /**
     * The SlytSetting instance.
     *
     * @var SlytSetting
     */
    protected $setting;

    /**
     * The SlytUser instance.
     *
     * @var SlytUser
     */
    protected $user;

    /**
     * The SlytYouTubeOauth2 instance.
     *
     * @var SlytYouTubeOauth2
     */
    protected $yt;

    /**
     * The SlytPlugin instance.
     *
     * @return void
     */
    public function __construct() {
        $this->setting = new SlytSetting();
        $this->option = $this->setting->get_option();
        $this->query = $this->setting->get_query();
        $this->user = new SlytUser();
        $this->yt = $this->user->get_yt();
        $this->session = new SlytSession();
        $this->session->start();

        /** Register hooks. */
        register_activation_hook( SLYT_PATH_FILE, array( 'SlytPlugin', 'activation' ) );
        register_deactivation_hook( SLYT_PATH_FILE, array( 'SlytPlugin', 'deactivation' ) );

        /** Add actions. */
        add_action( 'init', array($this, 'load_plugin_textdomain') );
        add_action( 'admin_init', array($this, 'load_plugin_textdomain') );
    }

    /**
     * Add the filters and actions for the plugin.
     *
     * @return void
     */
    public function run() {
        /** Add filters. */
        add_filter( 'query_vars' , array( $this, 'query_vars'  ), 10, 1 );
        add_filter( 'pre_get_avatar_data' , array( $this->user, 'pre_get_avatar_data'  ), 10, 2 );
        add_filter( 'login_message' , array( $this, 'login_message'  ), 10, 1 );

        /** Add actions. */
        add_action( 'init', array( $this, 'parse_query' ) );
        add_action( 'login_head', array( $this, 'login_head' ) );
        add_action( 'delete_user', array( $this->user, 'before_delete' ) );
        add_action( 'show_user_profile', array( $this->user, 'edit_profile' ) );
        add_action( 'edit_user_profile', array( $this->user, 'edit_profile' ) );
    }

    /**
     * Get the full uri for some plugin asset.
     *
     * @param string $path
     * @param boolean $return
     * @return string
     */
    public function asset( $path = '', $return = false ){
        $uri = plugins_url( $this->option->get_plugin_slug() . '/' . $path );
        if ($return) {
            return $uri;
        }else{
            echo $uri;
        }
    }

    /**
     * Get the Google authentication uri.
     *
     * @param boolean $return
     * @return string
     */
    public function auth_uri( bool $return = false ) {
        $state = $this->session->state();

        $ap = $this->option->get( 'google_client_approval_prompt', 0 ) === 1 ? 'force' : 'auto';
        $params = [
            'approval_prompt' => $ap,
            'access_type' => 'offline',
        ];

        if ( $this->option->get( 'user_profile' ) ) {
            $scopes = $this->yt->getScopes();
            array_push( $scopes, 'profile' );
            $this->yt->scopes( $scopes );
        }

        $auth_uri = $this->yt->with( $params )->redirect( $state );

        if ( $return ){
            return $auth_uri;
        }else{
            echo $auth_uri;
        }
    }

    /**
     * Create the plugin options in WP db and starts a PHP session.
     *
     * @return void
     */
    public static function activation(){
        //Create options.
        $option = new SlytOption();
        $option->default();

        //Starts PHP Session
        SlytSession::start();
    }

    /**
     * Destroy the PHP session.
     *
     * @return void
     */
    public static function deactivation() {
        //Delete options and users metadata: see uninstall.php.

        //Destroy PHP Session
        SlytSession::destroy();
    }

    /**
     * Load the translation for the plugin.
     *
     * @return void
     */
    public static function load_plugin_textdomain() {
        $option = new SlytOption();
        load_plugin_textdomain( $option->get_plugin_slug(), false, '/' . $option->get_plugin_slug() . '/languages' );
    }

    /**
     * Check if the current user is subscribed to the given YouTube channel.
     *
     * @return void
     */
    public function check_subscription() {
        $user = get_userdata( get_current_user_id() );
        if ( $user && in_array( $this->option->get( 'default_role', 'subscriber' ), $user->roles, true ) ) {
            $this->user->update_token( $user->ID );
            $token = get_user_meta( $user->ID , $this->option->add_name( 'token' ), true );
            $isUserSubscribed = false;
            if ( !empty( $token ) ) {
                $isUserSubscribed = $this->yt->isUserSubscribed( $token );
            }

            if ( empty( $token ) || !$isUserSubscribed ) {
                wp_logout();
            }
        }
    }

    /**
     * Action fired after WordPress has finished loading.
     *
     * @return void
     */
    public function parse_query( ) {
        if ( $this->run_login() ) {
            $code = isset( $_GET['code'] ) ? $_GET['code'] : null;
            if ( !empty($code) ) {
                $state = isset( $_GET['state'] ) ? $_GET['state'] : null;;
                $session_state = $this->session->pop( 'state' );
                $yt_user = $this->yt->user($code, $state, $session_state);
                if( $yt_user ) {
                    $isUserSubscribed = $this->yt->isUserSubscribed( $yt_user->getToken() );
                    $wp_user = null;
                    if( $isUserSubscribed ){
                        if ( email_exists( $yt_user->getEmail() ) === false ) {
                            $wp_user = $this->user->insert( $yt_user );
                        }else{
                            $wp_user = $this->user->update( $yt_user );
                        }
                    }else{
                        $youtube_channel = $this->option->get_youtube_channel_info();
                        /* translators: %s: it's an URI address. */
                        $message = sprintf( __( "You need to subscribe to <a href=\"%1\$s\">%2\$s</a> for login.", 'subscriber-login-for-youtube' ), $youtube_channel['youtube_channel_uri'], $youtube_channel['youtube_channel_title'] );
                        $this->session->set( $this->option->add_name( 'user_not_subscribed' ), $message );
                        $this->yt->revokeToken( $yt_user->getToken() );
                    }
    
                    if ( $wp_user ) {
                        $this->user->login( $wp_user );
                        exit();
                    }
                }
            }
        }

        if ( is_user_logged_in() ) {
            $this->check_subscription();
            $action_value = isset( $_GET[$this->query->get_action_key()] ) ? $_GET[$this->query->get_action_key()] : null;
            $user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : 0;
            if ( $this->query->is_valid_action( $action_value ) &&
                ( $user_id == get_current_user_id() || current_user_can( 'edit_users' ) )
                ) {
                $this->user->revoke( $user_id );
            }
        }
    }

    /**
     * Action fired in the login page header after scripts are enqueued.
     *
     * @return void
     */
    public function login_head() {
        $css = $this->asset('public/css/style_login.css', true);
        $slug = $this->option->get_plugin_slug();
        echo "<link rel='stylesheet' id='{$slug}-public-css' href='{$css}' media='all' />";
    }

    /**
     * Filters the message to display above the login form.
     *
     * @param string $message
     * @return string
     */
    public function login_message( $message ) {
        $msg = $this->youtube_sign_in_button();
        $errors = $this->get_errors( true );

        return $msg . $errors . $message;
    }

    /**
     * Get the html code for the youtube sign in button.
     * It apply the filter 'youtube_sign_in_button' so the user
     * can customize the sign in button.
     *  
     * @return string
     */
    public function youtube_sign_in_button() {
        $icon = $this->asset( 'public/images/yt_icon.svg', true );
        $auth_uri = $this->auth_uri( true );
        $link_text = __( 'Sign in with YouTube', 'subscriber-login-for-youtube' );
        $html = '';
        $html = apply_filters( 'youtube_sign_in_button', $html, $icon, $auth_uri, $link_text );
        if ( empty( $html ) ) {
            $html = '<div id="sign-in-youtube">';
            $html .= '<a href="' . $auth_uri . '"><img id="youtube-icon" src="' . $icon . '" alt="' . __( 'YouTube icon', 'subscriber-login-for-youtube' ) . '">' . $link_text . '</a>';
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Get the html code for the login message.
     *
     * @param boolean $return
     * @return string
     */
    public function get_errors( $return = false ){
        $html = '';
        $error_msg = $this->session->pop( $this->option->add_name( 'user_not_subscribed' ) );
        if ( !empty( $error_msg ) ) {
            $html = '<div class="login message">' . wpautop( $error_msg ) . '</div>';
        }

        if ( $return ) {
            return $html;
        }else{
            echo $html;
        }
    }
   
    /**
     * Filters the query variables allowed before processing.
     *
     * @param array $vars
     * @return array
     */
    public function query_vars( $vars ) {
        array_push(
            $vars,
            $this->query->get_login_key(),
            'state', 
            'code',
            'scope',
            'authuser',
            'prompt'
        );

        return $vars;
    }

    /**
     * Check if it should run the login process.
     *
     * @return bool
     */
    public function run_login(){
        $login_value = isset( $_GET[$this->query->get_login_key()] ) ? $_GET[$this->query->get_login_key()] : null;

        return ( !is_user_logged_in() && $this->query->is_valid_login( $login_value ) );
    }
}
