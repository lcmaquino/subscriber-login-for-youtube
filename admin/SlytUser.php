<?php

class SlytUser
{
    /**
     * The SlytQuery instance.
     *
     * @var SlytQuery
     */
    protected $query;

    /**
     * The SlytOption instance.
     *
     * @var SlytOption
     */
    protected $option;

    /**
     * The SlytYouTubeOauth2 instance.
     *
     * @var SlytYouTubeOauth2
     */
    protected $yt;

    /**
     * The SlytUser instance.
     *
     * @return void
     */
	public function __construct( ) {
		$this->option = new SlytOption();
        $this->query = new SlytQuery();
        $this->yt = new SlytYouTubeOauth2( $this->option->get_oauth2_config() );
    }

    /**
     * Get the SlytYouTubeOauth2 instance.
     *
     * @var SlytYouTubeOauth2
     */
    public function get_yt() {
        return $this->yt;
    }

	/**
	 * Log the user in.
	 *
	 * @param WP_User $wp_user
	 * @return void
	 */
    public function login( WP_User $wp_user) {
        wp_set_current_user( $wp_user->ID, $wp_user->user_login );
        wp_set_auth_cookie( $wp_user->ID, true );
        do_action( 'wp_login', $wp_user->user_login, $wp_user );
        wp_safe_redirect( site_url() );
    }

	/**
	 * Insert a user into the database.
	 *
	 * @param SlytGoogleUser $gu
	 * @return WP_User
	 */
    public function insert (SlytGoogleUser $gu = null) {
		$wp_user = null;
		if ( $gu ) {
			$random_password = wp_generate_password( 12, false );
			$userdata = array(
				'user_pass' => $random_password,
				'user_login' => $gu->getEmail(),
				'user_nicename' => str_replace( '@', '-', $gu->getEmail() ),
				'user_email' => $gu->getEmail(),
				'first_name' => $gu->getGivenName(),
				'last_name' => $gu->getFamilyName(),
				'role' => $this->option->get( 'default_role', 'subscriber' ),
				'locale' => str_replace( '-', '_', $gu->getLocale() )
			);
			$wp_user_id = wp_insert_user( $userdata );
			if ( !is_wp_error( $wp_user_id ) ) {
				$wp_user = get_user_by( 'id', $wp_user_id );
			}

			if ( $wp_user ) {
				add_user_meta( $wp_user->ID, $this->option->add_name( 'picture' ), $gu->getPicture(), true );
				add_user_meta( $wp_user->ID, $this->option->add_name( 'token' ), $gu->getToken() );
				if ( $gu->getRefreshToken() ) {
					add_user_meta( $wp_user->ID, $this->option->add_name( 'refresh_token' ), $gu->getRefreshToken(), true );
				}

				$expires_in = intval ( current_time( 'timestamp' ) ) + 3600 ;
				add_user_meta( $wp_user->ID, $this->option->add_name( 'expires_in' ), date( 'Y-m-d H:i:s', $expires_in ), true );
			}
		}

		return $wp_user;
	}

	/**
	 * Update a user in the database.
	 *
	 * @param SlytGoogleUser $gu
	 * @return WP_User
	 */
	public function update (SlytGoogleUser $gu = null) {
		$wp_user = null;
		if ( $gu ) {
			$wp_user = $this->get( $gu->getEmail() );
			if ( $wp_user ) {
				$userdata = array(
					'ID' => $wp_user->ID,
					'first_name' => $gu->getGivenName(),
					'last_name' => $gu->getFamilyName(),
					'display_name' => trim( $gu->getGivenName() . ' ' . $gu->getFamilyName() ),
					'role' => $this->option->get( 'default_role', 'subscriber' ),
					'locale' => str_replace( '-', '_', $gu->getLocale() )
				);
				wp_update_user( $userdata );
				update_user_meta( $wp_user->ID, $this->option->add_name( 'picture' ), $gu->getPicture() );
				update_user_meta( $wp_user->ID, $this->option->add_name( 'token' ), $gu->getToken() );
				if ( $gu->getRefreshToken() ) {
					update_user_meta( $wp_user->ID, $this->option->add_name( 'refresh_token' ), $gu->getRefreshToken() );
				}

				$expires_in = intval ( current_time( 'timestamp' ) ) + 3600 ;
				update_user_meta( $wp_user->ID, $this->option->add_name( 'expires_in' ), date( 'Y-m-d H:i:s', $expires_in ) );
			}
		}

		return $wp_user;
	}

	/**
	 * Update the user access token.
	 *
	 * @param integer $user_id
	 * @return void
	 */
	public function update_token( int $user_id = 0 ) {
        if ( $user_id ) {
			$now = current_time( 'timestamp' );
			$expires_in = get_user_meta( $user_id , $this->option->add_name( 'expires_in' ), true );
			if ( !empty( $expires_in ) && strcmp( date( 'Y-m-d H:i:s', $now ), $expires_in ) > 0 ) {
				$refresh_token = get_user_meta( $user_id , $this->option->add_name( 'refresh_token' ), true );
				$token = $this->yt->refreshUserToken( $refresh_token );
				if ( $token ) {
					update_user_meta( $user_id, $this->option->add_name( 'token' ), $token );
					$expires_in = intval ( $now ) + 3598 ;
					$expires_in = date( 'Y-m-d H:i:s', $expires_in );
					update_user_meta( $user_id, $this->option->add_name( 'expires_in' ), $expires_in );
				}
			}
        }
    }

	/**
	 * Revoke the user tokens before the user is deleted from the database. 
	 *
	 * @param integer $user_id
	 * @return void
	 */
	public function before_delete ( int $user_id ) {
		$refresh_token = get_user_meta( $user_id, $this->option->add_name( 'refresh_token' ), true );
		if ( $refresh_token ) {
			$this->yt->revokeToken( $refresh_token );
		}
	}

	/**
	 * Get the user by its email.
	 *
	 * @param string $email
	 * @return WP_User
	 */
	public function get (string $email = '') {
		$wp_user = get_user_by( 'email', $email );
		return $wp_user ? $wp_user : null;
	}

	/**
	 * Add a section in the user profile page so the user
	 * can revoke the website access to their Google account.
	 *
	 * @param WP_User $profileuser
	 * @return void
	 */
    public function edit_profile( WP_User $profileuser ) {
        $page = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $page .= $_SERVER['HTTP_HOST'];
        $page .= $_SERVER['PHP_SELF'] . '?' . $this->query->get_action() . '&user_id=' . $profileuser->ID;
        $refresh_token = get_user_meta( $profileuser->ID, $this->option->add_name( 'refresh_token' ), true );
        $html = '<h2>' . __( 'Google account', 'subscriber-login-for-youtube' ) . '</h2>' . PHP_EOL;
        $html .= '<table class="form-table" role="presentation">' . PHP_EOL;
        $html .= '<tr class="user-revoke-access">' . PHP_EOL;
        $html .= '<th>';
        $html .= '<label for="revoke-access">' . __( 'Revoke access', 'subscriber-login-for-youtube' ) . '</label>';
        $html .= '</th>' . PHP_EOL;
        $html .= '<td>';
        if ( $refresh_token ) {
            $html .= '<a id="revoke-access" href="' . $page .'">' . __( 'Revoke access', 'subscriber-login-for-youtube' ) . '</a>';
            $html .= '<p id="revoke-access-description" class="description">' . __( 'Revoke the website access to this Google account.', 'subscriber-login-for-youtube' ) . '</p>';
        }else{
            $html .= '<p id="revoke-access" class="description">' . __( 'This Google account was revoked.', 'subscriber-login-for-youtube' ) . '</p>';
        }
        $html .= '</td>' . PHP_EOL;
        $html .= '</tr>' . PHP_EOL;
        $html .= '</table>' . PHP_EOL;
        echo $html;
    }

	/**
	 * Revoke the website access to the user Google account.
	 *
	 * @param integer $user_id
	 * @return void
	 */
    public function revoke( int $user_id = 0 ) {
        $refresh_token = get_user_meta( $user_id, $this->option->add_name( 'refresh_token' ), true );
        if ( $refresh_token ) {
            $revoked = $this->yt->revokeToken( $refresh_token );
            if ( $revoked ) {
                update_user_meta( $user_id, $this->option->add_name( 'token' ), null );
                update_user_meta( $user_id, $this->option->add_name( 'refresh_token' ), null );
                update_user_meta( $user_id, $this->option->add_name( 'expires_in' ), null );
                if ( $user_id == get_current_user_id() ) {
                    wp_logout();
                }
            }
        }
    }

	/**
	 * Set the user avatar as their Google account profile picture.
	 *
	 * @param array $args
	 * @param mixed $id_or_email
	 * @return array
	 */
    public function pre_get_avatar_data ( array $args, $id_or_email ) {
        $user_id = null;
        if ( is_int( $id_or_email ) ) {
            $user_id = $id_or_email;
        } elseif( filter_var( $id_or_email, FILTER_VALIDATE_EMAIL ) !== false ) {
            $user = get_user_by( 'email', $id_or_email );
            if ( $user ) {
                $user_id = $user->ID;
            }
        }

        if ( $user_id !== null ) {
            $picture = get_user_meta( $user_id, $this->option->add_name( 'picture' ), true );
            if ( !empty( $picture ) ) {
                $args['url'] = $picture;
            }
        }

        return $args;
    }
}