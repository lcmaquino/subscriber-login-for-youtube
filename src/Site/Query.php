<?php
namespace Lcmaquino\SubscriberLoginForYouTube\Site;

class Query
{
    /**
     * The login query key.
     *
     * @var string
     */
    const LOGIN_KEY = 'login';

    /**
     * The action query key.
     *
     * @var string
     */
    const ACTION_KEY = 'action';

    /**
     * The query values.
     *
     * @var array
     */
    const QUERY = array(
        'login' => 'slyt',
        'action' => 'slyt-revoke'
    );

    /**
     * Get the login query key.
     *
     * @return string
     */
    public function getLoginKey()
    {
        return self::LOGIN_KEY;
    }

    /**
     * Get the login query.
     *
     * @return string
     */
    public function getLogin()
    {
        return self::LOGIN_KEY . '=' . self::QUERY[self::LOGIN_KEY];
    }

    /**
     * Get the action query key.
     *
     * @return string
     */
    public function getActionKey()
    {
        return self::ACTION_KEY;
    }

    /**
     * Get the action query.
     *
     * @return string
     */
    public function getAction()
    {
        return self::ACTION_KEY . '=' . self::QUERY[self::ACTION_KEY];
    }

    /**
     * Check with $value is equal to the login query value.
     *
     * @return bool
     */
    public function isValidLogin($value = '')
    {
        return self::QUERY[self::LOGIN_KEY] === $value;
    }

    /**
     * Check with $value is equal to the action query value.
     *
     * @return bool
     */
    public function isValidAction($value = '')
    {
        return self::QUERY[self::ACTION_KEY] === $value;
    }

    public function get($key, $sanitizer = 'string')
    {
        $value = isset($_GET[$key]) ? $this->sanitize($_GET[$key], $sanitizer) : null;
        return $value;
    }

    /**
     * Sanitizes a value.
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    protected function sanitize($value, $type)
    {
        switch ($type) {
            case 'int':
                $value = absint($value);
                break;
            default:
                $value = sanitize_text_field($value);
        }
        return $value;
    }
}
