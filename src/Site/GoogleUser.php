<?php
namespace Lcmaquino\SubscriberLoginForYouTube\Site;

class GoogleUser
{
    /**
     * The map associating the Google API Data raw keys with the GoogleUser
     * attributes.
     *
     * @var array
     */
    const MAP_KEYS = array(
        'sub' => 'sub',
        'name' => 'name',
        'given_name' => 'givenName',
        'family_name' => 'familyName',
        'locale' => 'locale',
        'email' => 'email',
        'email_verified' => 'emailVerified',
        'picture' =>  'picture'
    );

    /**
     * The unique Google identifier for the user.
     *
     * @var string
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
    protected $givenName;

    /**
     * The user's family name.
     *
     * @var string
     */
    protected $familyName;

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
    protected $emailVerified;

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
    protected $refreshToken;

    /**
     * The number of seconds the access token is valid for.
     *
     * @var int
     */
    protected $expiresIn;

    /**
     * The user's raw attributes.
     *
     * @var array
     */
    protected $rawAttributes;

    public function __construct()
    {
        $this->sub = null;
        $this->name = '';
        $this->givenName = '';
        $this->familyName = '';
        $this->locale = '';
        $this->email = null;
        $this->emailVerified = 0;
        $this->picture = null;
        $this->token = null;
        $this->refreshToken = null;
        $this->expiresIn = null;
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
        return $this->givenName;
    }

    /**
     * Get the family name of the user.
     *
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
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
        return $this->emailVerified;
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
        return $this->refreshToken;
    }

    /**
     * Get the number of seconds the access token is valid for.
     *
     * @return int
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
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
        $this->map($rawAttributes);

        return $this;
    }

    /**
    * Set the token on the user.
    *
    * @param  string  $token
    * @return $this
    */
    public function setToken(string $token)
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
    public function setRefreshToken(string $refreshToken)
    {
        $this->refreshToken = $refreshToken;

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
        $this->expiresIn = $expiresIn;

        return $this;
    }

    /**
     * Map the given array onto the user's properties.
     *
     * @param  array  $rawAttributes
     * @return $this
     */
    public function map(array $rawAttributes)
    {
        foreach ($rawAttributes as $rawKey => $value) {
            if (isset(self::MAP_KEYS[$rawKey])) {
                $key = self::MAP_KEYS[$rawKey];
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    /**
     * Determine if the given raw user attribute exists.
     *
     * @param  string $offset
     * @return bool
     */
    public function offsetExists(string $offset)
    {
        return array_key_exists($offset, $this->rawAttributes);
    }

    /**
     * Get the given key from the raw user.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet(string $offset)
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
    public function offsetSet(string $offset, $value)
    {
        $this->rawAttributes[$offset] = $value;
    }

    /**
     * Unset the given value from the raw user array.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset(string $offset)
    {
        unset($this->rawAttributes[$offset]);
    }
}
