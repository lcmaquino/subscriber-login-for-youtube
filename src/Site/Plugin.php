<?php
namespace Lcmaquino\SubscriberLoginForYouTube\Site;

use Lcmaquino\SubscriberLoginForYouTube\Admin\Setting;

class Plugin
{
    /**
     * The Session instance.
     *
     * @var Session
     */
    protected $session;

    /**
     * The Option instance.
     *
     * @var Option
     */
    protected $option;
    
    /**
     * The Query instance.
     *
     * @var Query
     */
    protected $query;

    /**
     * The Setting instance.
     *
     * @var Setting
     */
    protected $setting;

    /**
     * The User instance.
     *
     * @var User
     */
    protected $user;

    /**
     * The YouTubeOauth2 instance.
     *
     * @var YouTubeOauth2
     */
    protected $yt;

    /**
     * The Plugin instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setting = new Setting();
        $this->option = $this->setting->getOption();
        $this->query = $this->setting->getQuery();
        $this->user = new User();
        $this->yt = $this->user->getYt();
        $this->session = new Session();
        $this->session->start();

        /** Register hooks. */
        register_activation_hook(SLYT_MAIN_FILE, array( $this, 'activation' ));
        register_deactivation_hook(SLYT_MAIN_FILE, array( $this, 'deactivation' ));

        /** Add actions. */
        add_action('init', array($this, 'loadPluginTextDomain'));
        add_action('admin_init', array($this, 'loadPluginTextDomain'));
    }

    /**
     * Add the filters and actions for the plugin.
     *
     * @return void
     */
    public function run()
    {
        /** Add filters. */
        add_filter('query_vars', array( $this, 'queryVars'  ), 10, 1);
        add_filter('pre_get_avatar_data', array( $this->user, 'preGetAvatarData'  ), 10, 2);
        add_filter('login_message', array( $this, 'loginMessage'  ), 10, 1);

        /** Add actions. */
        add_action('init', array( $this, 'parseQuery' ));
        add_action('login_enqueue_scripts', array( $this, 'loginEnqueueScripts' ));
        add_action('delete_user', array( $this->user, 'beforeDelete' ));
        add_action('show_user_profile', array( $this->user, 'editProfile' ));
        add_action('edit_user_profile', array( $this->user, 'editProfile' ));
    }

    /**
     * Get the full uri for some plugin resource.
     *
     * @param string $path
     * @param boolean $return
     * @return string
     */
    public function resource($path = '', $return = false)
    {
        $uri = plugins_url($this->option->getPluginSlug() . '/resources'. $path);
        if ($return) {
            return $uri;
        } else {
            echo $uri;
        }
    }

    /**
     * Get the Google authentication uri.
     *
     * @param boolean $return
     * @return string
     */
    public function authUri(bool $return = false)
    {
        $state = $this->session->state();

        $ap = $this->option->get('google_client_approval_prompt', 0) === 1 ? 'force' : 'auto';
        $params = array(
            'approval_prompt' => $ap,
            'access_type' => 'offline',
        );

        if ($this->option->get('user_profile')) {
            $scopes = $this->yt->getScopes();
            array_push($scopes, 'profile');
            $this->yt->scopes($scopes);
        }

        $authUri = $this->yt->with($params)->redirect($state);

        if ($return) {
            return $authUri;
        } else {
            echo $authUri;
        }
    }

    /**
     * Create the plugin options in WP db and starts a PHP session.
     *
     * @return void
     */
    public static function activation()
    {
        //Create options.
        $option = new Option();
        $option->default();

        //Starts PHP Session
        Session::start();
    }

    /**
     * Destroy the PHP session.
     *
     * @return void
     */
    public static function deactivation()
    {
        //Delete options and users metadata: see uninstall.php.

        //Destroy PHP Session
        Session::destroy();
    }

    /**
     * Load the translation for the plugin.
     *
     * @return void
     */
    public static function loadPluginTextDomain()
    {
        $option = new Option();
        load_plugin_textdomain($option->getPluginSlug(), false, '/' . $option->getPluginSlug() . '/languages');
    }

    /**
     * Check if the current user is subscribed to the given YouTube channel.
     *
     * @return void
     */
    public function checkSubscription()
    {
        $user = get_userdata(get_current_user_id());
        if ($user && in_array($this->option->get('default_role', 'subscriber'), $user->roles, true)) {
            $this->user->updateToken($user->ID);
            $token = get_user_meta($user->ID, $this->option->addName('token'), true);
            $isUserSubscribed = false;
            if (!empty($token)) {
                $isUserSubscribed = $this->yt->isUserSubscribed($token);
            }

            if (empty($token) || !$isUserSubscribed) {
                wp_logout();
            }
        }
    }

    /**
     * Action fired after WordPress has finished loading.
     *
     * @return void
     */
    public function parseQuery()
    {
        if ($this->runLogin()) {
            $code = $this->query->get('code');
            if (!empty($code)) {
                $state = $this->query->get('state');
                $session_state = $this->session->pop('state');
                $ytUser = $this->yt->user($code, $state, $session_state);
                if ($ytUser) {
                    $isUserSubscribed = $this->yt->isUserSubscribed($ytUser->getToken());
                    $wpUser = null;
                    if ($isUserSubscribed) {
                        if (email_exists($ytUser->getEmail()) === false) {
                            $wpUser = $this->user->insert($ytUser);
                        } else {
                            $wpUser = $this->user->update($ytUser);
                        }
                    } else {
                        $this->session->set($this->option->addName('user_not_subscribed'), true);
                        $this->yt->revokeToken($ytUser->getToken());
                    }
    
                    if ($wpUser) {
                        $this->user->login($wpUser);
                        exit();
                    }
                }
            }
        }

        if (is_user_logged_in()) {
            $this->checkSubscription();
            $actionValue = $this->query->get($this->query->getActionkey());
            $userId = $this->query->get('user_id', 'int');
            if ($this->query->isValidAction($actionValue) &&
                ($userId == get_current_user_id() || current_user_can('edit_users'))
                ) {
                $this->user->revoke($userId);
            }
        }
    }

    /**
     * Enqueue scripts and styles for the login page.
     *
     * @return void
     */
    public function loginEnqueueScripts()
    {
        $path = '/css/style_site.css';
        $src = $this->resource($path, true);
        $slug = $this->option->getPluginSlug();
        $handle = $slug . '-public';
        wp_enqueue_style($handle, $src, array(), filemtime(SLYT_RESOURCES . $path));
    }

    /**
     * Filters the message to display above the login form.
     *
     * @param string $message
     * @return string
     */
    public function loginMessage($message)
    {
        $msg = $this->youtubeSignInButton();
        $errors = $this->getErrors(true);

        return $msg . $errors . $message;
    }

    /**
     * Get the html code for the youtube sign in button.
     * It apply the filter 'youtube_sign_in_button' so the user
     * can customize the sign in button.
     *
     * @return string
     */
    public function youtubeSignInButton()
    {
        $icon = $this->resource('/images/yt_icon.svg', true);
        $authUri = $this->authUri(true);
        $linkText = __('Sign in with YouTube', 'subscriber-login-for-youtube');
        $html = '';
        $html = apply_filters('youtube_sign_in_button', $html, $icon, $authUri, $linkText);
        if (empty($html)) {
            $html = '<div id="sign-in-youtube">';
            $html .= '<a href="' . $authUri . '"><img id="youtube-icon" src="' . $icon . '" alt="' . __('YouTube icon', 'subscriber-login-for-youtube') . '">' . $linkText . '</a>';
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
    public function getErrors($return = false)
    {
        $html = '';
        $userNotSubscribed = $this->session->pop($this->option->addName('user_not_subscribed'), 'bool');
        if ($userNotSubscribed) {
            $youtubeChannel = $this->option->getYouTubeChannelInfo();
            $message = sprintf(
                /* translators: %s: it's an URI address. */
                __("You need to subscribe to <a href=\"%1\$s\">%2\$s</a> for login.", 'subscriber-login-for-youtube'),
                $youtubeChannel['youtube_channel_uri'],
                $youtubeChannel['youtube_channel_title']
            );
            $message = wp_kses_post(wpautop($message));
            $html = '<div class="login message">' . $message . '</div>';
        }

        if ($return) {
            return $html;
        } else {
            echo $html;
        }
    }
   
    /**
     * Filters the query variables allowed before processing.
     *
     * @param array $vars
     * @return array
     */
    public function queryVars($vars)
    {
        array_push(
            $vars,
            $this->query->getLoginKey(),
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
    public function runLogin()
    {
        $loginValue = $this->query->get($this->query->getLoginKey());

        return (!is_user_logged_in() && $this->query->isValidLogin($loginValue));
    }
}
