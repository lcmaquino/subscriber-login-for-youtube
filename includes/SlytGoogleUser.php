<?php

class SlytGoogleUser
{
    /**
     * The unique Google identifier for the user.
     *
     * @var mixed
     */
    protected $sub;

    /**
     * The user's full name.
     *
     * @var string
     */
    protected $name;

    /**
     * The user's first name.
     *
     * @var string
     */
    protected $given_name;

    /**
     * The user's family name.
     *
     * @var string
     */
    protected $family_name;

    /**
     * The user's locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * The user's e-mail address.
     *
     * @var string
     */
    protected $email;

    /**
     * The user's e-mail address is verified.
     *
     * @var boolean
     */
    protected $email_verified;

    /**
     * The user's profile picture URL.
     *
     * @var string
     */
    protected $picture;

    /**
     * The user's access token.
     *
     * @var string
     */
    protected $token;

    /**
     * The refresh token that can be exchanged for a new access token.
     *
     * @var string
     */
    protected $refresh_token;

    /**
     * The number of seconds the access token is valid for.
     *
     * @var int
     */
    protected $expires_in;

    /**
     * The user's raw attributes.
     *
     * @var array
     */
    protected $rawAttributes;

    public function __construct () {
        $this->token = null;
        $this->refresh_token = null;
        $this->rawAttributes = array();
    }

    /**
     * Get the unique Google identifier for the user.
     *
     * @return string
     */
    public function getSub()
    {
        return $this->sub;
    }

    /**
     * Get the full name of the user.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the first name of the user.
     *
     * @return string
     */
    public function getGivenName()
    {
        return $this->given_name;
    }

    /**
     * Get the family name of the user.
     *
     * @return string
     */
    public function getFamilyName()
    {
        return $this->family_name;
    }

    /**
     * Get the user's e-mail address.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Check if user's e-mail address is verified.
     *
     * @return string
     */
    public function getEmailVerified()
    {
        return $this->email_verified;
    }

    /**
     * Get the picture image URL for the user.
     *
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Get the locale of the user.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get the raw user array.
     *
     * @return array
     */
    public function getRaw()
    {
        return $this->rawAttributes;
    }

    /**
     * Get the token on the user.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get the refresh token required to obtain a new access token.
     *
     * @return $string
     */
    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    /**
     * Get the number of seconds the access token is valid for.
     *
     * @return int
     */
    public function getExpiresIn()
    {
        return $this->expires_in;
    }

    /**
     * Set the raw user array from the Google authentication.
     *
     * @param  array  $rawAttributes
     * @return $this
     */
    public function setRaw(array $rawAttributes)
    {
        $this->rawAttributes = $rawAttributes;

        return $this;
    }

     /**
     * Set the token on the user.
     *
     * @param  string  $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Set the refresh token required to obtain a new access token.
     *
     * @param  string  $refreshToken
     * @return $this
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refresh_token = $refreshToken;

        return $this;
    }

    /**
     * Set the number of seconds the access token is valid for.
     *
     * @param  int  $expiresIn
     * @return $this
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expires_in = $expiresIn;

        return $this;
    }

    /**
     * Map the given array onto the user's properties.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function map(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }

        $this->rawAttributes = array_merge( $this->rawAttributes, $attributes );

        return $this;
    }

    /**
     * Determine if the given raw user attribute exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->rawAttributes);
    }

    /**
     * Get the given key from the raw user.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->rawAttributes[$offset];
    }

    /**
     * Set the given attribute on the raw user array.
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->rawAttributes[$offset] = $value;
    }

    /**
     * Unset the given value from the raw user array.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->rawAttributes[$offset]);
    }
}