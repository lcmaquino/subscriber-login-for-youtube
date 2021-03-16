<?php

class SlytQuery
{
    /**
     * The login query key.
     * 
     * @var string
     */
    protected const LOGIN_KEY = 'login';

    /**
     * The action query key.
     * 
     * @var string
     */
    protected const ACTION_KEY = 'action';

    /**
     * The query values.
     * 
     * @var array
     */
    protected const QUERY = array(
        'login' => 'slyt',
        'action' => 'slyt-revoke'
    );

    /**
     * Get the login query key.
     *
     * @return string
     */
    public function get_login_key() {
        return self::LOGIN_KEY;
    }

    /**
     * Get the login query.
     *
     * @return string
     */
    public function get_login() {
        return self::LOGIN_KEY . '=' . self::QUERY[self::LOGIN_KEY];
    }

    /**
     * Get the action query key.
     *
     * @return string
     */
    public function get_action_key() {
        return self::ACTION_KEY;
    }

    /**
     * Get the action query.
     *
     * @return string
     */
    public function get_action() {
        return self::ACTION_KEY . '=' . self::QUERY[self::ACTION_KEY];
    }

    /**
     * Check with $value is equal to the login query value.
     *
     * @return bool
     */
    public function is_valid_login( $value = '' ) {
        return self::QUERY[self::LOGIN_KEY] === $value;
    }

    /**
     * Check with $value is equal to the action query value.
     *
     * @return bool
     */
    public function is_valid_action( $value = '' ) {
        return self::QUERY[self::ACTION_KEY] === $value;
    }
}