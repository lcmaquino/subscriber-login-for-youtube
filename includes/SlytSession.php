<?php 

class SlytSession
{
    /**
     * Start a PHP session.
     *
     * @return void
     */
    public static function start() {
        if( session_status() !== PHP_SESSION_ACTIVE ){
            session_start();
        }
    }

    /**
     * Destroy a PHP session.
     *
     * @return void
     */
    public static function destroy() {
        session_destroy();
    }

    /**
     * Store a value in session.
     *
     * @return void
     */
    public function set( $key = '', $value = null ) {
        if (!empty($key)) {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Unset a value stored in session.
     *
     * @return void
     */
    public function unset( $key = '' ) {
        if ( !empty($key) ) {
            unset( $_SESSION[$key] );
        }
    }

    /**
     * Get a value stored in session.
     *
     * @return mixed
     */
    public function get( $key = '', $return = null ) {
        return !empty($key) && isset($_SESSION[$key]) ?  $_SESSION[$key] : $return;
    }

    /**
     * Get a value stored in session and then unset it.
     *
     * @return mixed
     */
    public function pop( $key = '', $default = null ) {
        //Be carefull:
        //It'll return $default if $_SESSION[$key] is equal
        //to $default or it isn't set.
        $value = $this->get( $key );
        if ( $value ) {
            $this->unset( $key );
        }else{
            $value = $default;
        }

        return $value;
    }

    /**
     * Create a random code to identify the webapp current state.
     *
     * @return string
     */
    public function state() {
        $code = md5(uniqid(rand(), true));
        $this->set('state', $code);
        return $code;
    }
}
