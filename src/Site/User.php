<?php
namespace Lcmaquino\SubscriberLoginForYouTube\Site;

use \WP_User;

class User
{
    /**
     * The Query instance.
     *
     * @var Query
     */
    protected $query;

    /**
     * The Option instance.
     *
     * @var Option
     */
    protected $option;

    /**
     * The YouTubeOauth2 instance.
     *
     * @var YouTubeOauth2
     */
    protected $yt;

    /**
     * The User instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->option = new Option();
        $this->query = new Query();
        $this->yt = new YouTubeOauth2($this->option->getOauth2Config());
    }

    /**
     * Get the YouTubeOauth2 instance.
     *
     * @var YouTubeOauth2
     */
    public function getYt()
    {
        return $this->yt;
    }

    /**
     * Log the user in.
     *
     * @param WP_User $wpUser
     * @return void
     */
    public function login(WP_User $wpUser)
    {
        wp_set_current_user($wpUser->ID, $wpUser->user_login);
        wp_set_auth_cookie($wpUser->ID, true);
        do_action('wp_login', $wpUser->user_login, $wpUser);
        wp_safe_redirect(site_url());
    }

    /**
     * Insert a user into the database.
     *
     * @param GoogleUser $gu
     * @return WP_User
     */
    public function insert(GoogleUser $gu = null)
    {
        $wpUser = null;
        if ($gu) {
            $randomPassword = wp_generate_password(12, false);
            $userdata = array(
                'user_pass' => $randomPassword,
                'user_login' => $gu->getEmail(),
                'user_nicename' => str_replace('@', '-', $gu->getEmail()),
                'user_email' => $gu->getEmail(),
                'first_name' => $gu->getGivenName(),
                'last_name' => $gu->getFamilyName(),
                'role' => $this->option->get('default_role', 'subscriber'),
                'locale' => str_replace('-', '_', $gu->getLocale())
            );
            $wpUserId = wp_insert_user($userdata);
            if (!is_wp_error($wpUserId)) {
                $wpUser = get_user_by('id', $wpUserId);
            }

            if ($wpUser) {
                add_user_meta($wpUser->ID, $this->option->addName('picture'), $gu->getPicture(), true);
                add_user_meta($wpUser->ID, $this->option->addName('token'), $gu->getToken());
                if ($gu->getRefreshToken()) {
                    add_user_meta($wpUser->ID, $this->option->addName('refresh_token'), $gu->getRefreshToken(), true);
                }

                $expiresIn = intval(current_time('timestamp')) + 3600 ;
                add_user_meta($wpUser->ID, $this->option->addName('expires_in'), date('Y-m-d H:i:s', $expiresIn), true);
            }
        }

        return $wpUser;
    }

    /**
     * Update a user in the database.
     *
     * @param GoogleUser $gu
     * @return WP_User
     */
    public function update(GoogleUser $gu = null)
    {
        $wpUser = null;
        if ($gu) {
            $wpUser = $this->get($gu->getEmail());
            if ($wpUser) {
                $userdata = array(
                    'ID' => $wpUser->ID,
                    'first_name' => $gu->getGivenName(),
                    'last_name' => $gu->getFamilyName(),
                    'display_name' => trim($gu->getGivenName() . ' ' . $gu->getFamilyName()),
                    'role' => $this->option->get('default_role', 'subscriber'),
                    'locale' => str_replace('-', '_', $gu->getLocale())
                );
                wp_update_user($userdata);
                update_user_meta($wpUser->ID, $this->option->addName('picture'), $gu->getPicture());
                update_user_meta($wpUser->ID, $this->option->addName('token'), $gu->getToken());
                if ($gu->getRefreshToken()) {
                    update_user_meta($wpUser->ID, $this->option->addName('refresh_token'), $gu->getRefreshToken());
                }

                $expiresIn = intval(current_time('timestamp')) + 3600 ;
                update_user_meta($wpUser->ID, $this->option->addName('expires_in'), date('Y-m-d H:i:s', $expiresIn));
            }
        }

        return $wpUser;
    }

    /**
     * Update the user access token.
     *
     * @param integer $userId
     * @return void
     */
    public function updateToken(int $userId = 0)
    {
        if ($userId) {
            $now = current_time('timestamp');
            $expiresIn = get_user_meta($userId, $this->option->addName('expires_in'), true);
            if (!empty($expiresIn) && strcmp(date('Y-m-d H:i:s', $now), $expiresIn) > 0) {
                $refreshToken = get_user_meta($userId, $this->option->addName('refresh_token'), true);
                $token = $this->yt->refreshUserToken($refreshToken);
                if ($token) {
                    update_user_meta($userId, $this->option->addName('token'), $token);
                    $expiresIn = intval($now) + 3598 ;
                    $expiresIn = date('Y-m-d H:i:s', $expiresIn);
                    update_user_meta($userId, $this->option->addName('expires_in'), $expiresIn);
                }
            }
        }
    }

    /**
     * Revoke the user tokens before the user is deleted from the database.
     *
     * @param integer $userId
     * @return void
     */
    public function beforeDelete(int $userId)
    {
        $refreshToken = get_user_meta($userId, $this->option->addName('refresh_token'), true);
        if ($refreshToken) {
            $this->yt->revokeToken($refreshToken);
        }
    }

    /**
     * Get the user by its email.
     *
     * @param string $email
     * @return WP_User
     */
    public function get(string $email = '')
    {
        $wpUser = get_user_by('email', $email);
        return $wpUser ? $wpUser : null;
    }

    /**
     * Add a section in the user profile page so the user
     * can revoke the website access to their Google account.
     *
     * @param WP_User $profileuser
     * @return void
     */
    public function editProfile(WP_User $profileuser)
    {
        $page = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $page .= $_SERVER['HTTP_HOST'];
        $page .= $_SERVER['PHP_SELF'] . '?' . $this->query->getAction() . '&user_id=' . $profileuser->ID;
        $refreshToken = get_user_meta($profileuser->ID, $this->option->addName('refresh_token'), true);
        $html = '<h2>' . __('Google account', 'subscriber-login-for-youtube') . '</h2>' . PHP_EOL;
        $html .= '<table class="form-table" role="presentation">' . PHP_EOL;
        $html .= '<tr class="user-revoke-access">' . PHP_EOL;
        $html .= '<th>';
        $html .= '<label for="revoke-access">' . __('Revoke access', 'subscriber-login-for-youtube') . '</label>';
        $html .= '</th>' . PHP_EOL;
        $html .= '<td>';
        if ($refreshToken) {
            $html .= '<a id="revoke-access" href="' . $page .'">' . __('Revoke access', 'subscriber-login-for-youtube') . '</a>';
            $html .= '<p id="revoke-access-description" class="description">' . __('Revoke the website access to this Google account.', 'subscriber-login-for-youtube') . '</p>';
        } else {
            $html .= '<p id="revoke-access" class="description">' . __('This Google account was revoked.', 'subscriber-login-for-youtube') . '</p>';
        }
        $html .= '</td>' . PHP_EOL;
        $html .= '</tr>' . PHP_EOL;
        $html .= '</table>' . PHP_EOL;
        echo $html;
    }

    /**
     * Revoke the website access to the user Google account.
     *
     * @param integer $userId
     * @return void
     */
    public function revoke(int $userId = 0)
    {
        $refreshToken = get_user_meta($userId, $this->option->addName('refresh_token'), true);
        if ($refreshToken) {
            $revoked = $this->yt->revokeToken($refreshToken);
            if ($revoked) {
                update_user_meta($userId, $this->option->addName('token'), null);
                update_user_meta($userId, $this->option->addName('refresh_token'), null);
                update_user_meta($userId, $this->option->addName('expires_in'), null);
                if ($userId == get_current_user_id()) {
                    wp_logout();
                }
            }
        }
    }

    /**
     * Set the user avatar as their Google account profile picture.
     *
     * @param array $args
     * @param mixed $idOrEmail
     * @return array
     */
    public function preGetAvatarData(array $args, $idOrEmail)
    {
        $userId = null;
        if (is_int($idOrEmail)) {
            $userId = $idOrEmail;
        } elseif (filter_var($idOrEmail, FILTER_VALIDATE_EMAIL) !== false) {
            $user = get_user_by('email', $idOrEmail);
            if ($user) {
                $userId = $user->ID;
            }
        }

        if ($userId !== null) {
            $picture = get_user_meta($userId, $this->option->addName('picture'), true);
            if (!empty($picture)) {
                $args['url'] = $picture;
            }
        }

        return $args;
    }
}
