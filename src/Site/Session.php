<?php
namespace Lcmaquino\SubscriberLoginForYouTube\Site;

class Session
{
    /**
     * Start a PHP session.
     *
     * @return void
     */
    public static function start()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Destroy a PHP session.
     *
     * @return void
     */
    public static function destroy()
    {
        session_destroy();
    }

    /**
     * Store a value in session.
     *
     * @return void
     */
    public function set($key = '', $value = null)
    {
        if (!empty($key)) {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Unset a value stored in session.
     *
     * @return void
     */
    public function unset($key = '')
    {
        if (!empty($key)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Get a value stored in session.
     *
     * @return mixed
     */
    public function get($key = '', $sanitizer = 'string')
    {
        return !empty($key) && isset($_SESSION[$key]) ?  $this->sanitize($_SESSION[$key], $sanitizer) : null;
    }

    /**
     * Get a value stored in session and then unset it.
     *
     * @return mixed
     */
    public function pop($key = '', $sanitizer = 'string')
    {
        $value = $this->get($key, $sanitizer);
        $this->unset($key);

        return $value;
    }

    /**
     * Create a random code to identify the webapp current state.
     *
     * @return string
     */
    public function state()
    {
        $code = md5(uniqid(rand(), true));
        $this->set('state', $code);

        return $code;
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
            case 'bool':
                $value = is_bool($value);
                break;
            default:
                $value = sanitize_text_field($value);
        }
        
        return $value;
    }
}
