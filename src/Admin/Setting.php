<?php
namespace Lcmaquino\SubscriberLoginForYouTube\Admin;

use Lcmaquino\SubscriberLoginForYouTube\Site\Query;
use Lcmaquino\SubscriberLoginForYouTube\Site\Option;

class Setting
{
    /**
     * Instance of Query.
     *
     * @var Query
     */
    protected $query;

    /**
     * Instance of Option.
     *
     * @var Option
     */
    protected $option;

    /**
     * Instance of Setting.
     *
     * @return void
     */
    public function __construct()
    {
        $this->query = new Query();
        $this->option = new Option();

        add_filter(
            'plugin_action_links_' . plugin_basename(SLYT_MAIN_FILE),
            array(
                $this,
                'addActionLinks'
            )
        );
        
        add_action(
            'admin_menu',
            array(
                $this,
                'adminMenu'
            )
        );

        add_action(
            'admin_init',
            array(
                $this,
                'registerSettings'
            )
        );
    }

    /**
     * Get the instance of Query.
     *
     * @var Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the instance of Option.
     *
     * @var Option
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Add an action link on WP plugins page.
     *
     * @param string[] $actions
     * @return array
     */
    public function addActionLinks($actions)
    {
        $mylinks = array(
           '<a href="' .
             admin_url('options-general.php?page=' . $this->option->getPluginSlug()) .
             '">'. __('Settings', 'subscriber-login-for-youtube') .'</a>',
        );
        $actions = array_merge($mylinks, $actions);

        return $actions;
    }

    /**
     * Add the plugin submenu page to the Settings main menu.
     *
     * @return void
     */
    public function adminMenu()
    {
        $menu = add_options_page(
            $this->option->getPluginName(),
            $this->option->getPluginName(),
            'manage_options',
            'subscriber-login-for-youtube',
            array(
                $this,
                'renderSettingsPage'
            )
        );

        add_action('admin_print_styles-' . $menu, array( $this, 'admin_css' ));
    }

    /**
     * Enqueue a css stylesheet for the plugin setting page.
     *
     * @return void
     */
    public function admin_css()
    {
        $path = '/css/style_admin.css';
        $src = plugins_url($this->option->getPluginSlug() . '/resources' .  $path);
        $handle = $this->option->getPluginSlug() . '-admin';
        wp_enqueue_style($handle, $src, array(), filemtime(SLYT_RESOURCES . $path));
    }

    /**
     * Registers the plugin settings and its data.
     *
     * @return void
     */
    public function registerSettings()
    {
        $page = $this->option->addName('settings_page');
        $sectionGoogleClient = 'settingsGoogleClient';
        $sectionYouTube = 'settingsYouTube';
        $sectionUserRegistration = 'settingsUserRegistration';
        $sectionLoginPage = 'settingsLoginPage';

        /* Google Client settings section */
        $fields = array(
            array(
                'id' => $this->option->addName('google_client_id'),
                'title' => __('Google Client ID', 'subscriber-login-for-youtube')
            ),
            array(
                'id' => $this->option->addName('google_client_secret'),
                'title' => __('Google Client secret', 'subscriber-login-for-youtube')
            ),
            array(
                'id' => $this->option->addName('google_client_redirect_uri'),
                'title' => __('Google Client redirect URI', 'subscriber-login-for-youtube')
            ),
        );

        foreach ($fields as $field) {
            add_settings_field(
                $field['id'],
                $field['title'],
                array(
                    $this,
                    'addTextField'
                ),
                $page,
                $sectionGoogleClient,
                array(
                    'label_for' => $field['id'],
                    'class' => 'settings_row'
                )
            );
        }

        register_setting(
            $this->option->addName('options_google_client'),
            $this->option->getName(),
            array(
                $this,
                'settingsSanitize'
            )
        );

        add_settings_section(
            $sectionGoogleClient,
            __('Google Client', 'subscriber-login-for-youtube'),
            array(
                $this,
                $sectionGoogleClient
            ),
            $page
        );

        /* YouTube settings section */
        $fields = array(
            array(
                'id' => $this->option->addName('youtube_channel_id'),
                'title' => __('YouTube Channel ID', 'subscriber-login-for-youtube')
            ),
            array(
                'id' => $this->option->addName('youtube_channel_title'),
                'title' => __('YouTube Channel Title', 'subscriber-login-for-youtube')
            ),
            array(
                'id' => $this->option->addName('youtube_channel_uri'),
                'title' => __('YouTube Channel URI', 'subscriber-login-for-youtube')
            ),
        );

        foreach ($fields as $field) {
            add_settings_field(
                $field['id'],
                $field['title'],
                array(
                    $this,
                    'addTextField'
                ),
                $page,
                $sectionYouTube,
                array(
                    'label_for' => $field['id'],
                    'class' => 'settings_row'
                )
            );
        }

        register_setting(
            $this->option->addName('options_youtube'),
            $this->option->getName(),
            array(
                $this,
                'settingsSanitize'
            )
        );

        add_settings_section(
            $sectionYouTube,
            __('YouTube', 'subscriber-login-for-youtube'),
            array(
                $this,
                $sectionYouTube
            ),
            $page
        );

        /* User registration settings section */
        add_settings_field(
            $this->option->addName('user_profile'),
            __('User profile', 'subscriber-login-for-youtube'),
            array(
                $this,
                'addRadioField'
            ),
            $page,
            $sectionUserRegistration,
            array(
                'label_for' => $this->option->addName('user_profile'),
                'class' => 'settings_row'
            )
        );

        add_settings_field(
            $this->option->addName('default_role'),
            __('Default role', 'subscriber-login-for-youtube'),
            array(
                $this,
                'addSelectField'
            ),
            $page,
            $sectionUserRegistration,
            array(
                'label_for' => $this->option->addName('default_role'),
                'class' => 'settings_row'
            )
        );

        register_setting(
            $this->option->addName('options_user_registration'),
            $this->option->getName(),
            array(
                $this,
                'settingsSanitize'
            )
        );

        add_settings_section(
            $sectionUserRegistration,
            __('User registration', 'subscriber-login-for-youtube'),
            array(
                $this,
                $sectionUserRegistration
            ),
            $page
        );

        /* Login page settings section */
        add_settings_field(
            $this->option->addName('google_client_approval_prompt'),
            __("Always show the Google account consent screen", 'subscriber-login-for-youtube'),
            array(
                $this,
                'addCheckboxField'
            ),
            $page,
            $sectionLoginPage,
            array(
                'label_for' => $this->option->addName('google_client_approval_prompt'),
                'class' => 'settings_row'
            )
        );

        register_setting(
            $this->option->addName('options_login_page'),
            $this->option->getName(),
            array(
                $this,
                'settingsSanitize'
            )
        );

        add_settings_section(
            $sectionLoginPage,
            __('Login page', 'subscriber-login-for-youtube'),
            array(
                $this,
                $sectionLoginPage
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
    public function settingsSanitize($input)
    {
        $errors = [];
        $option = $this->option->all();
        /* Sanitize */
        $optionKeys = array_keys($option);
        foreach ($optionKeys as $key) {
            /* checkbox or radio input */
            if ($this->option->isInt($key)) {
                $input[$key] = (isset($input[$key]) && ($input[$key] === 'on' || $input[$key] === '1')) ? 1 : 0;
            }

            /* text input */
            if ($this->option->isString($key)) {
                $input[$key] = isset($input[$key]) ? sanitize_text_field($input[$key]) : '';
            }
        }

        /* Validate */
        if (strlen($input['youtube_channel_id']) == 0) {
            $errors[] = __('YouTube Channel ID is required', 'subscriber-login-for-youtube');
            $input['youtube_channel_id'] = $option['youtube_channel_id'];
        }

        if (strlen($input['youtube_channel_title']) == 0) {
            $errors[] = __('YouTube Channel Title is required', 'subscriber-login-for-youtube');
            $input['youtube_channel_title'] = $option['youtube_channel_title'];
        }

        if (filter_var($input['youtube_channel_uri'], FILTER_VALIDATE_URL) === false ||
            strpos($input['youtube_channel_uri'], 'youtube.com/') === false) {
            $errors[] = sprintf(
                /* translators: %s it's a YouTube Channel URI. */
                __('YouTube Channel URI should be like %s', 'subscriber-login-for-youtube'),
                'https://www.youtube.com/MyAwsomeChannel'
            );
            $input['youtube_channel_uri'] = $option['youtube_channel_uri'];
        }

        if (strlen($input['google_client_id']) == 0) {
            $errors[] = __('Google Client ID is required', 'subscriber-login-for-youtube');
            $input['google_client_id'] = $option['google_client_id'];
        }

        if (strlen($input['google_client_secret']) == 0) {
            $errors[] = __('Google Client secret is required', 'subscriber-login-for-youtube');
            $input['google_client_secret'] = $option['google_client_secret'];
        }

        $queryStr = '?' . $this->query->getLogin();
        $siteUrl = site_url('wp-login.php');
        $siteUrl = str_replace('https:', '', $siteUrl);
        $siteUrl = str_replace('http:', '', $siteUrl);
        if (filter_var($input['google_client_redirect_uri'], FILTER_VALIDATE_URL) === false ||
            strpos($input['google_client_redirect_uri'], $siteUrl) === false ||
            strpos($input['google_client_redirect_uri'], $queryStr) === false) {
            /* translators: %s it's the plugin slug. */
            $errors[] = sprintf(__('Google Client redirect URI should be something like <code>%s</code>.', 'subscriber-login-for-youtube'), 'https:' . $siteUrl . $queryStr);
            $input['google_client_redirect_uri'] = $option['google_client_redirect_uri'];
        }

        if (!empty($errors)) {
            $errorsHtml = '<ul>';
            foreach ($errors as $error) {
                $errorsHtml .= '<li>' . $error . '</li>';
            }
            
            $errorsHtml .= '</ul>';
            add_settings_error(
                $this->option->addName('setting_form'),
                'plugin-form',
                $errorsHtml,
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
    public function addTextField($args)
    {
        $inputId = $args['label_for'];
        $optionKey = $this->option->removeName($inputId);
        $optionName = $this->option->getName();
        $value = $this->option->get($optionKey);
        $value = (strpos($optionKey, 'uri') === false) ? esc_attr($value) : esc_url($value);

        echo "<input id='{$inputId}' name='{$optionName}[{$optionKey}]' type='text' value='{$value}'>";
    }

    /**
     * Echo the html code for the checkbox field.
     *
     * @param array $args
     * @return void
     */
    public function addCheckboxField($args)
    {
        $inputId = $args['label_for'];
        $optionKey = $this->option->removeName($inputId);
        $optionName = $this->option->getName();
        $value = $this->option->get($optionKey);
        $checked = checked(1, $value, false);
        echo "<input id='{$inputId}' name='{$optionName}[{$optionKey}]' type='checkbox'{$checked}>";
    }

    /**
     * Echo the html code for the radio button field.
     *
     * @param array $args
     * @return void
     */
    public function addRadioField($args)
    {
        $inputId = $args['label_for'];
        $optionKey = $this->option->removeName($inputId);
        $optionName = $this->option->getName();
        $value = $this->option->get($optionKey);
        $checkedBasic = checked(0, $value, false);
        $checkedFull = checked(1, $value, false);
        echo "<div id='{$inputId}'>";
        echo "<input id='{$inputId}_basic' name='{$optionName}[{$optionKey}]' type='radio' value='0'{$checkedBasic}>";
        echo "<label for='{$inputId}_basic'>" . __('Basic', 'subscriber-login-for-youtube') . '</label>';
        echo "<input id='{$inputId}_full' name='{$optionName}[{$optionKey}]' type='radio' value='1'{$checkedFull}>";
        echo "<label for='{$inputId}_full'>" . __('Full', 'subscriber-login-for-youtube') . '</label>';
        echo '</div>';
    }

    /**
     * Echo the html code for the select field.
     *
     * @param array $args
     * @return void
     */
    public function addSelectField($args)
    {
        $inputId = $args['label_for'];
        $optionKey = $this->option->removeName($inputId);
        $optionName = $this->option->getName();
        $value = $this->option->get($optionKey);
        echo "<select id='{$inputId}' name='{$optionName}[{$optionKey}]'>";
        wp_dropdown_roles($value);
        echo '</select>';
    }

    /**
     * Echo the html code for the section description.
     *
     * @return void
     */
    public function settingsYouTube()
    {
        $message = '<div class="settings_section_text">';
        $message .= '<ul>';

        $message .= sprintf(
            '<li>' .
            /* translators: %s: it's an URI address. */
            __("You can see your YouTube Channel ID in your <a href=\"%s\">advanced account settings</a> on YouTube.", 'subscriber-login-for-youtube') .
            '</li>',
            'http://www.youtube.com/account_advanced'
        );

        $message .= '</ul></div>';

        echo $message;
    }

    /**
     * Echo the html code for the section description.
     *
     * @return void
     */
    public function settingsGoogleClient()
    {
        $message = '<div class="settings_section_text">';
        $message .= '<ul>';

        $message .= sprintf(
            '<li>' .
            /* translators: %1$s: it's an URI address. %2$s, %3$s, and %4$s: those are Google Client OAuth 2.0 parameters. */
            __("Before using this plugin, you need to set up a Google project for web application in <a href=\"%1\$s\">Google Console</a>. It's a Google requirement for users to login! The web application project will provide: \"%2\$s\", \"%3\$s\", and \"%4\$s\".", 'subscriber-login-for-youtube') .
            '</li>',
            'https://console.developers.google.com/',
            __('Google Client ID', 'subscriber-login-for-youtube'),
            __('Google Client secret', 'subscriber-login-for-youtube'),
            __('Google Client redirect URI', 'subscriber-login-for-youtube')
        );

        $message .= sprintf(
            '<li>' .
            /* translators: %1$s, %2$s, and %3$s: those are Google Client OAuth 2.0 scopes. %4$s: it's an URI address. %5$s: it's an Google support page for setting up OAuth 2.0. */
            __('Activate the "YouTube Data API v3" in your project and add the scopes <code>%1$s</code>, <code>%2$s</code>, and <code>%3$s</code> for its consent screen. See more details in <a href="%4$s">%5$s</a>.', 'subscriber-login-for-youtube') .
            '</li>',
            'userinfo.email',
            'openid',
            'youtube.readonly',
            'https://support.google.com/cloud/answer/6158849?hl=en',
            'Setting up OAuth 2.0'
        );

        $message .= sprintf(
            '<li>' .
            /* translators: %1$s: it's an URI address. %2$s: it's the redirect URI for a Google Client OAuth 2.0. */
            __('Set your project "%1$s" to be something like <code>%2$s</code>.', 'subscriber-login-for-youtube') .
            '</li>',
            __('Google Client redirect URI', 'subscriber-login-for-youtube'),
            site_url('wp-login.php?' . $this->query->getLogin())
        );

        $message .= '</ul></div>';

        echo $message;
    }

    /**
     * Echo the html code for the section description.
     *
     * @return void
     */
    public function settingsUserRegistration()
    {
        $message = '<div class="settings_section_text">';
        $message .= '<ul>';

        $message .= sprintf(
            '<li>' .
            /* translators: %1s: the "Basic" option for "User profile". */
            __('The "%s" user profile stores: email and picture.', 'subscriber-login-for-youtube') .
            '</li>',
            __('Basic', 'subscriber-login-for-youtube')
        );

        $message .= sprintf(
            '<li>' .
            /* translators: %1$s: the "Full" option for "User profile". %2$s: it's a scope for a Google Client OAuth 2.0. */
            __('The "%s" user profile stores: email, picture, full name, given name, family name, and locale. This option only works if the scope <code>%2$s</code> is added to the Google web application project.', 'subscriber-login-for-youtube') .
            '</li>',
            __('Full', 'subscriber-login-for-youtube'),
            'userinfo.profile'
        );

        $message .= sprintf(
            '<li>' .
            /* translators: %s: the "Default role" option. */
            __('When users login for the first time they will be registrated as the "%s".', 'subscriber-login-for-youtube') .
            '</li>',
            __('Default role', 'subscriber-login-for-youtube')
        );

        $message .= '</ul></div>';

        echo $message;
    }

    /**
     * Echo the html code for the section description.
     *
     * @return void
     */
    public function settingsLoginPage()
    {
        $message = '<div class="settings_section_text">';
        $message .= '<ul>';

        $message .= sprintf(
            '<li>' .
            /* translators: %s it's the filter slug. */
            __("Add the filter <code>%s</code> to custom your sign in button.", 'subscriber-login-for-youtube') .
            '</li>',
            'youtube_sign_in_button'
        );

        $message .= '</ul>';

        $message .= '<p><strong>' . __('Example', 'subscriber-login-for-youtube') . '</strong></p>';
        $message .= sprintf(
            '<p>' .
            /* translators: %s: the function.php template file. */
            __('Add to <code>%s</code> template file the following code.', 'subscriber-login-for-youtube') .
            '</p>',
            'function.php'
        );
        $message .= "<p><pre class='php-code'>" . PHP_EOL;
        $message .= "function my_custom_sign_in( \$html, \$icon, \$auth_uri, \$link_text ) {" . PHP_EOL;
        $message .= esc_html("    \$html = \"<div><p><img src='{\$icon}'></p><p><a href='{\$auth_uri}'>{\$link_text}</a></p></div>\";" . PHP_EOL);
        $message .= "    return \$html;" . PHP_EOL;
        $message .= "}" . PHP_EOL;
        $message .= "add_filter( 'youtube_sign_in_button', 'my_custom_sign_in', 10, 4);" . PHP_EOL;
        $message .= '</pre></p>';

        $message .= '</div>';

        echo $message;
    }

    /**
     * Echo the html code for the setting form.
     *
     * @return void
     */
    public function renderSettingsPage()
    {
        $html = '<div class="wrap">';
        /* translators: %s it's the plugin name. */
        $html .= '<h2>' . sprintf(__('%s &bullet; Settings', 'subscriber-login-for-youtube'), $this->option->getPluginName()) . '</h2>';
        $html .= '<form id="' . $this->option->addName('setting_form') . '" action="options.php" method="post">';
        $html .= '<div class="settings_form_fields">';
        echo $html;
        settings_fields($this->option->addName('options_google_client'));
        settings_fields($this->option->addName('options_youtube'));
        settings_fields($this->option->addName('options_login_page'));
        do_settings_sections($this->option->addName('settings_page'));
        submit_button(__('Save', 'subscriber-login-for-youtube'));
        $html = '</div></form></div>';
        echo $html;
    }
}
