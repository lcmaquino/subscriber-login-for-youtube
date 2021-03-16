<?php

class SlytSetting
{
	/**
	 * Instance of SlytQuery.
	 *
	 * @var SlytQuery
	 */
	protected $query;

	/**
	 * Instance of SlytOption.
	 *
	 * @var SlytOption
	 */
	protected $option;

	/**
	 * Instance of SlytSetting.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->query = new SlytQuery();
		$this->option = new SlytOption();

		add_filter(
			'plugin_action_links_' . plugin_basename(SLYT_PATH_FILE),
			array(
				$this,
				'add_action_links'
			)
		);
		
        add_action(
			'admin_menu',
			array(
				$this,
				'admin_menu'
			)
		);

		add_action(
			'admin_init',
			array(
				$this,
				'register_settings'
			)
		);
	}

	/**
	 * Get the instance of SlytQuery.
	 *
	 * @var SlytQuery
	 */
	public function get_query() {
		return $this->query;
	}

	/**
	 * Get the instance of SlytOption.
	 *
	 * @var SlytOption
	 */
	public function get_option() {
		return $this->option;
	}

	/**
	 * Add an action link on WP plugins page.
	 *
	 * @param string[] $actions
	 * @return array
	 */
	public function add_action_links( $actions ) {
		$mylinks = array(
		   '<a href="' .
		     admin_url( 'options-general.php?page=' . $this->option->get_plugin_slug() ) . 
			 '">'. __( 'Settings', 'subscriber-login-for-youtube' ) .'</a>',
		);
		$actions = array_merge( $mylinks, $actions );

		return $actions;
	}

	/**
	 * Add the plugin submenu page to the Settings main menu.
	 *
	 * @return void
	 */
	public function admin_menu() {
		$menu = add_options_page(
            $this->option->get_plugin_name(),
            $this->option->get_plugin_name(),
            'manage_options',
            'subscriber-login-for-youtube',
			array(
				$this,
				'render_settings_page'
			)
		);

		add_action('admin_print_styles-' . $menu, array( $this, 'admin_css' ) );
	}

	/**
	 * Enqueue a css stylesheet for the plugin setting page.
	 *
	 * @return void
	 */
	public function admin_css() {
		$path = plugins_url( $this->option->get_plugin_slug() . '/admin/css/style_settings.css' );
		wp_enqueue_style( $this->option->get_plugin_slug() . '-admin', $path );
	}

	/**
	 * Registers the plugin settings and its data.
	 *
	 * @return void
	 */
	public function register_settings() {
		$page = $this->option->add_name( 'settings_page' );
		$section_google_client = 'settings_google_client';
		$section_youtube = 'settings_youtube';
		$section_user_registration = 'settings_user_registration';
		$section_login_page = 'settings_login_page';

		/* Google Client settings section */
		$fields = array(
			array(
				'id' => $this->option->add_name( 'google_client_id'),
				'title' => __( 'Google Client ID', 'subscriber-login-for-youtube' )
			),
			array(
				'id' => $this->option->add_name( 'google_client_secret'),
				'title' => __( 'Google Client secret', 'subscriber-login-for-youtube' )
			),
			array(
				'id' => $this->option->add_name( 'google_client_redirect_uri'),
				'title' => __( 'Google Client redirect URI', 'subscriber-login-for-youtube' )
			),
		);

		foreach ($fields as $field) {
			add_settings_field(
				$field['id'],
				$field['title'],
				array(
					$this,
					'add_text_field'
				),
				$page,
				$section_google_client,
				array(
					'label_for' => $field['id'],
					'class' => 'settings_row'
				)
			);
		}

		register_setting(
			$this->option->add_name( 'options_google_client'),
			$this->option->get_name(),
			array(
				$this,
				'settings_sanitize'
			)
		);

		add_settings_section(
			$section_google_client,
			__( 'Google Client', 'subscriber-login-for-youtube' ),
			array(
				$this,
				$section_google_client
			),
			$page
		);

		/* YouTube settings section */
		$fields = array(
			array(
				'id' => $this->option->add_name( 'youtube_channel_id' ),
				'title' => __( 'YouTube Channel ID', 'subscriber-login-for-youtube' )
			),
			array(
				'id' => $this->option->add_name( 'youtube_channel_title' ),
				'title' => __( 'YouTube Channel Title', 'subscriber-login-for-youtube' )
			),
			array(
				'id' => $this->option->add_name( 'youtube_channel_uri' ),
				'title' => __( 'YouTube Channel URI', 'subscriber-login-for-youtube' )
			),
		);

		foreach ($fields as $field) {
			add_settings_field(
				$field['id'],
				$field['title'],
				array(
					$this,
					'add_text_field'
				),
				$page,
				$section_youtube,
				array(
					'label_for' => $field['id'],
					'class' => 'settings_row'
				)
			);
		}

		register_setting(
			$this->option->add_name( 'options_youtube' ),
			$this->option->get_name(),
			array(
				$this,
				'settings_sanitize'
			)
		);

		add_settings_section(
			$section_youtube,
			__( 'YouTube', 'subscriber-login-for-youtube' ),
			array(
				$this,
				$section_youtube
			),
			$page
		);

		/* User registration settings section */
		add_settings_field(
			$this->option->add_name( 'user_profile' ),
			__( 'User profile', 'subscriber-login-for-youtube'),
			array(
				$this,
				'add_radio_field'
			),
			$page,
			$section_user_registration,
			array(
				'label_for' => $this->option->add_name( 'user_profile' ),
				'class' => 'settings_row'
			)
		);

		add_settings_field(
			$this->option->add_name( 'default_role' ),
			__( 'Default role', 'subscriber-login-for-youtube'),
			array(
				$this,
				'add_select_field'
			),
			$page,
			$section_user_registration,
			array(
				'label_for' => $this->option->add_name( 'default_role' ),
				'class' => 'settings_row'
			)
		);

		register_setting(
			$this->option->add_name( 'options_user_registration' ),
			$this->option->get_name(),
			array(
				$this,
				'settings_sanitize'
			)
		);

		add_settings_section(
			$section_user_registration,
			__( 'User registration', 'subscriber-login-for-youtube' ),
			array(
				$this,
				$section_user_registration
			),
			$page
		);

		/* Login page settings section */
		add_settings_field(
			$this->option->add_name( 'google_client_approval_prompt' ),
			__( "Always show the Google account consent screen", 'subscriber-login-for-youtube'),
			array(
				$this,
				'add_checkbox_field'
			),
			$page,
			$section_login_page,
			array(
				'label_for' => $this->option->add_name( 'google_client_approval_prompt' ),
				'class' => 'settings_row'
			)
		);

		register_setting(
			$this->option->add_name( 'options_login_page' ),
			$this->option->get_name(),
			array(
				$this,
				'settings_sanitize'
			)
		);

		add_settings_section(
			$section_login_page,
			__( 'Login page', 'subscriber-login-for-youtube' ),
			array(
				$this,
				$section_login_page
			),
			$page
		);
	}

	/**
	 * Sanitize the user input data on setting form and store it.
	 *
	 * @param array $input
	 * @return void
	 */
	public function settings_sanitize( $input ) {
		$errors = [];

		/* Sanitize */
		$option_keys = array_keys( $this->option->all() );
		foreach($option_keys as $key) {
			/* checkbox or radio input */
			if ( $this->option->is_int( $key ) ) {
				$input[$key] = ( isset( $input[$key] ) && ( $input[$key] === 'on' || $input[$key] === '1' ) ) ? 1 : 0;
			}

			/* text input */
			if ( $this->option->is_string( $key ) ) {
				$input[$key] = isset( $input[$key] ) ? trim( $input[$key] ) : '';
			}
		}

		/* Validate */
		if ( strlen($input['youtube_channel_id']) == 0) {
			$errors[] = __('YouTube Channel ID is required', 'subscriber-login-for-youtube');
			$input['youtube_channel_id'] = $this->option->get('youtube_channel_id');
		}

		if ( strlen($input['youtube_channel_title']) == 0) {
			$errors[] = __('YouTube Channel Title is required', 'subscriber-login-for-youtube');
			$input['youtube_channel_title'] = $this->option->get('youtube_channel_title');
		}

		if ( filter_var($input['youtube_channel_uri'], FILTER_VALIDATE_URL) === false ||
			strpos( $input['youtube_channel_uri'], 'youtube.com/' ) === false) {
			$errors[] = sprintf(
				/* translators: %s it's a YouTube Channel URI. */
				__('YouTube Channel URI should be like %s', 'subscriber-login-for-youtube'),
				'https://www.youtube.com/MyAwsomeChannel'
			);
			$input['youtube_channel_uri'] = $this->option->get('youtube_channel_uri');
		}

		if ( strlen($input['google_client_id']) == 0) {
			$errors[] = __('Google Client ID is required', 'subscriber-login-for-youtube');
			$input['google_client_id'] = $this->option->get('google_client_id');
		}

		if ( strlen($input['google_client_secret']) == 0) {
			$errors[] = __('Google Client secret is required', 'subscriber-login-for-youtube');
			$input['google_client_secret'] = $this->option->get('google_client_secret');
		}

		$query_str = '?' . $this->query->get_login();
		$site_url = site_url( 'wp-login.php' );
		$site_url = str_replace('https:', '', $site_url);
		$site_url = str_replace('http:', '', $site_url);
		if ( filter_var($input['google_client_redirect_uri'], FILTER_VALIDATE_URL) === false ||
			strpos( $input['google_client_redirect_uri'], $site_url ) === false ||
			strpos( $input['google_client_redirect_uri'], $query_str ) === false) {
			/* translators: %s it's the plugin slug. */
			$errors[] = sprintf( __('Google Client redirect URI should be something like <code>%s</code>.', 'subscriber-login-for-youtube'), 'https:' . $site_url . $query_str );
			$input['google_client_redirect_uri'] = $this->option->get('google_client_redirect_uri');
		}

		if(!empty($errors)) {
			$errors_html = '<ul>';
			foreach ($errors as $error) {
				$errors_html .= '<li>' . $error . '</li>';
			}
			
			$errors_html .= '</ul>';
			add_settings_error(
				$this->option->add_name( 'setting_form' ),
				'plugin-form',
				$errors_html,
				'error'
			);
		}

		return $input;
	}

	/**
	 * Echo the html code for the text field.
	 *
	 * @param array $args
	 * @return void
	 */
	public function add_text_field( $args ) {
		$input_id = $args['label_for'];
		$option_key = $this->option->remove_name( $input_id );
		$option_name = $this->option->get_name();
		$value = $this->option->get( $option_key );
		$value = ( strpos($option_key, 'uri') === false ) ? esc_attr($value) : esc_url($value);

		echo "<input id='{$input_id}' name='{$option_name}[{$option_key}]' type='text' value='{$value}'>";
	}

	/**
	 * Echo the html code for the checkbox field.
	 *
	 * @param array $args
	 * @return void
	 */
	public function add_checkbox_field( $args ) {
		$input_id = $args['label_for'];
		$option_key = $this->option->remove_name( $input_id );
		$option_name = $this->option->get_name();
		$value = $this->option->get( $option_key );
		$checked = checked(1, $value, false );
		echo "<input id='{$input_id}' name='{$option_name}[{$option_key}]' type='checkbox'{$checked}>";
	}

	/**
	 * Echo the html code for the radio button field.
	 *
	 * @param array $args
	 * @return void
	 */
	public function add_radio_field( $args ) {
		$input_id = $args['label_for'];
		$option_key = $this->option->remove_name( $input_id );
		$option_name = $this->option->get_name();
		$value = $this->option->get( $option_key );
		$checked_basic = checked(0, $value, false );
		$checked_full = checked(1, $value, false );
		echo "<div id='{$input_id}'>";
		echo "<input id='{$input_id}_basic' name='{$option_name}[{$option_key}]' type='radio' value='0'{$checked_basic}>";
		echo "<label for='{$input_id}_basic'>" . __( 'Basic', 'subscriber-login-for-youtube' ) . '</label>';
		echo "<input id='{$input_id}_full' name='{$option_name}[{$option_key}]' type='radio' value='1'{$checked_full}>";
		echo "<label for='{$input_id}_full'>" . __( 'Full', 'subscriber-login-for-youtube' ) . '</label>';
		echo '</div>';
	}

	/**
	 * Echo the html code for the select field.
	 *
	 * @param array $args
	 * @return void
	 */
	public function add_select_field( $args ) {
		$input_id = $args['label_for'];
		$option_key = $this->option->remove_name( $input_id );
		$option_name = $this->option->get_name();
		$value = $this->option->get( $option_key );
		echo "<select id='{$input_id}' name='{$option_name}[{$option_key}]'>";
		wp_dropdown_roles( $value );
		echo '</select>';
	}

	/**
	 * Echo the html code for the section description.
	 *
	 * @return void
	 */
	public function settings_youtube() {
		$msg = '<div class="settings_section_text">';
		$msg .= '<ul>';

		$msg .= sprintf( '<li>' .
			/* translators: %s: it's an URI address. */
			__("You can see your YouTube Channel ID in your <a href=\"%s\">advanced account settings</a> on YouTube.", 'subscriber-login-for-youtube') .
			'</li>',
			'http://www.youtube.com/account_advanced'
		);

		$msg .= '</ul></div>';

		echo $msg;
	}

	/**
	 * Echo the html code for the section description.
	 *
	 * @return void
	 */
	public function settings_google_client() {
		$msg = '<div class="settings_section_text">';
		$msg .= '<ul>';

		$msg .= sprintf( '<li>' .
			/* translators: %1$s: it's an URI address. %2$s, %3$s, and %4$s: those are Google Client OAuth 2.0 parameters. */
			__("Before using this plugin, you need to set up a Google project for web application in <a href=\"%1\$s\">Google Console</a>. It's a Google requirement for users to login! The web application project will provide: \"%2\$s\", \"%3\$s\", and \"%4\$s\".", 'subscriber-login-for-youtube') .
			'</li>',
			'https://console.developers.google.com/',
			__( 'Google Client ID', 'subscriber-login-for-youtube' ),
			__( 'Google Client secret', 'subscriber-login-for-youtube' ),
			__( 'Google Client redirect URI', 'subscriber-login-for-youtube' )
		);

		$msg .= sprintf( '<li>' .
			/* translators: %1$s, %2$s, and %3$s: those are Google Client OAuth 2.0 scopes. %4$s: it's an URI address. %5$s: it's an Google support page for setting up OAuth 2.0. */
			__('Activate the "YouTube Data API v3" in your project and add the scopes <code>%1$s</code>, <code>%2$s</code>, and <code>%3$s</code> for its consent screen. See more details in <a href="%4$s">%5$s</a>.', 'subscriber-login-for-youtube') .
			'</li>',
			'userinfo.email',
			'openid',
			'youtube.readonly',
			'https://support.google.com/cloud/answer/6158849?hl=en',
			'Setting up OAuth 2.0'
		);

		$msg .= sprintf( '<li>' .
			/* translators: %1$s: it's an URI address. %2$s: it's the redirect URI for a Google Client OAuth 2.0. */
			__('Set your project "%1$s" to be something like <code>%2$s</code>.', 'subscriber-login-for-youtube') .
			'</li>',
			__( 'Google Client redirect URI', 'subscriber-login-for-youtube' ),
			site_url( 'wp-login.php?' . $this->query->get_login() )
		);

		$msg .= '</ul></div>';

		echo $msg;
	}

	/**
	 * Echo the html code for the section description.
	 *
	 * @return void
	 */
	public function settings_user_registration() {
		$msg = '<div class="settings_section_text">';
		$msg .= '<ul>';

		$msg .= sprintf( '<li>' .
			/* translators: %1s: the "Basic" option for "User profile". */
			__('The "%s" user profile stores: email and picture.', 'subscriber-login-for-youtube') .
			'</li>',
			__( 'Basic', 'subscriber-login-for-youtube' )
		);

		$msg .= sprintf( '<li>' .
			/* translators: %1$s: the "Full" option for "User profile". %2$s: it's a scope for a Google Client OAuth 2.0. */
			__('The "%s" user profile stores: email, picture, full name, given name, family name, and locale. This option only works if the scope <code>%2$s</code> is added to the Google web application project.', 'subscriber-login-for-youtube') .
			'</li>',
			__( 'Full', 'subscriber-login-for-youtube' ),
			'userinfo.profile'
		);

		$msg .= sprintf('<li>' .
			/* translators: %s: the "Default role" option. */
			__('When users login for the first time they will be registrated as the "%s".', 'subscriber-login-for-youtube') .
			'</li>',
			__( 'Default role', 'subscriber-login-for-youtube')			
		);

		$msg .= '</ul></div>';

		echo $msg;
	}

	/**
	 * Echo the html code for the section description.
	 *
	 * @return void
	 */
	public function settings_login_page() {
		$msg = '<div class="settings_section_text">';
		$msg .= '<ul>';

		$msg .= sprintf( '<li>' .
			/* translators: %s it's the filter slug. */
			__("Add the filter <code>%s</code> to custom your sign in button.", 'subscriber-login-for-youtube') .
			'</li>',
			'youtube_sign_in_button'
		);

		$msg .= '</ul>';

		$msg .= '<p><strong>' . __( 'Example', 'subscriber-login-for-youtube' ) . '</strong></p>';
		$msg .= sprintf( '<p>' .
			/* translators: %s: the function.php template file. */
			__( 'Add to <code>%s</code> template file the following code.', 'subscriber-login-for-youtube' ) .
			'</p>',
			'function.php'
		);
		$msg .= "<p><pre class='php-code'>" . PHP_EOL;
		$msg .= "function my_custom_sign_in( \$html, \$icon, \$auth_uri, \$link_text ) {" . PHP_EOL;
		$msg .= esc_html( "    \$html = \"<div><p><img src='{\$icon}'></p><p><a href='{\$auth_uri}'>{\$link_text}</a></p></div>\";" . PHP_EOL );
		$msg .= "    return \$html;" . PHP_EOL;
		$msg .= "}" . PHP_EOL;
		$msg .= "add_filter( 'youtube_sign_in_button', 'my_custom_sign_in', 10, 4);" . PHP_EOL;
		$msg .= '</pre></p>';

		$msg .= '</div>';

		echo $msg;
	}

	/**
	 * Echo the html code for the setting form.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		$html = '<div class="wrap">';
		/* translators: %s it's the plugin name. */
		$html .= '<h2>' . sprintf( __( '%s &bullet; Settings', 'subscriber-login-for-youtube' ), $this->option->get_plugin_name() ) . '</h2>';
		$html .= '<form id="' . $this->option->add_name('setting_form') . '" action="options.php" method="post">';
		$html .= '<div class="settings_form_fields">';
		echo $html;
		settings_fields( $this->option->add_name( 'options_google_client' ) );
		settings_fields( $this->option->add_name( 'options_youtube' ) );
		settings_fields( $this->option->add_name( 'options_login_page' ) );
		do_settings_sections( $this->option->add_name( 'settings_page' ) );
		submit_button( __( 'Save', 'subscriber-login-for-youtube' ) );
		$html = '</div></form></div>';
		echo $html;
	}
}