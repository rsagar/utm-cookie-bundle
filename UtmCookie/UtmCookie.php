<?php

namespace Medelse\UtmCookieBundle\UtmCookie;

use DateTime;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use UnexpectedValueException;

class UtmCookie
{
    /**
     * Name of cookie where will be saved utm params.
     *
     * @var string
     */
    private $utmCookieName;

    /**
     * @var array
     */
    private $utmCookie = null;

    /**
     * Lifetime of utmCookie
     *
     * @var int
     */
    private $lifetime;

    /**
     * If overwrite all utm values when even one is set in get. Default true.
     *
     * @var bool
     */
    private $overwrite;

    /**
     * Path for cookie. Default "/" so not empty like in setcookie PHP function!
     *
     * @var string
     */
    private $path = '/';

    /**
     * Domain for cookie.
     *
     * @var string
     */
    private $domain = '';

    /**
     * If cookie should be secured (same as $secure parameter in setcookie PHP function).
     *
     * @var bool
     */
    private $secure = false;

    /**
     * If cookie should be http only (same as $httponly parameter in setcookie PHP function).
     *
     * @var bool
     */
    private $httpOnly = false;

    /**
     * Remove utmCookie
     */
    public function clear()
    {
        setcookie($this->utmCookieName, '', -1, $this->path, $this->domain, $this->secure, $this->httpOnly);
    }

    /**
     * Get all utm values or just value of utm with specific key.
     *
     * @param string|null $key Default null (return all values as array).
     *
     * @return string|array|null Return string value, array or null if not set.
     */
    public function get(string $key = null)
    {
        $this->init();

        if ($key === null) {
            return $this->utmCookie;
        } else {
            if (mb_strpos($key, 'utm_') !== 0) {
                $key = 'utm_' . $key;
            }
            if (false === array_key_exists($key, $this->utmCookie)) {
                throw new UnexpectedValueException(sprintf('Argument $key has unexpected value "%s". Utm value with key "%s" does not exists.', $key, $key));
            } else {
                return $this->utmCookie[$key];
            }
        }
    }

    /**
     * Initialize. Get values from _GET and _COOKIES and save to UtmCookie. Init $this->utmCookie value.
     *
     * @return void
     */
    public function init()
    {
        // if initialized, just return
        if ($this->utmCookie !== null) {
            return;
        }

        $this->initStaticValues();
        // utm from _COOKIE
        $utmCookieName = filter_input(INPUT_COOKIE, $this->utmCookieName);
        $utmCookieFilter = filter_var(
            ($utmCookieName)? json_decode($utmCookieName, true) : null,
            FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            FILTER_REQUIRE_ARRAY
        );
        if (false === is_array($utmCookieFilter)) {
            $utmCookieFilter = [];
        }
        $utmCookie = $this->removeNullValues($utmCookieFilter);
        // utm from _GET
        $utmGetFilter = filter_input_array(
            INPUT_GET,
            [
                'utm_campaign' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                'utm_medium' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                'utm_source' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                'utm_term' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                'utm_content' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
            ]
        );
        if (false === is_array($utmGetFilter)) {
            $utmGetFilter = [];
        }
        $utmGet = $this->removeNullValues($utmGetFilter);

        if (count($utmGet) !== 0 && $this->overwrite === true) {
            $utmCookieSave = array_merge($this->utmCookie, $utmGet);
        } else {
            $utmCookieSave = array_merge($this->utmCookie, $utmCookie, $utmGet);
        }
        if (count($utmGet) !== 0) {
            $this->save($utmCookieSave);
        } else {
            $this->utmCookie = $utmCookieSave;
        }
    }

    /**
     * onKernelRequest called if autoInit is true
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->init();
    }

    /**
     * Set domain for cookie.
     *
     * @param string $domain
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Set httponly for cookie.
     *
     * @param bool $httpOnly
     */
    public function setHttpOnly(bool $httpOnly)
    {
        $this->httpOnly = $httpOnly;
    }

    /**
     * Set lifetime of utm cookie in seconds
     *
     * @param int $lifetime
     */
    public function setLifetime(int $lifetime)
    {
        if ($lifetime <= 0) {
            throw new UnexpectedValueException(sprintf('Lifetime has unexpected value "%s". Value must be positive.', $lifetime));
        }
        $this->lifetime = $lifetime;
    }

    /**
     * Set name of cookie where will be saved utm params.
     *
     * @param string $utmCookieName
     */
    public function setName(string $utmCookieName)
    {
        if (trim($utmCookieName) == '') {
            throw new UnexpectedValueException(sprintf('Name has unexpected value "%s". Value can\'t be empty.', $utmCookieName));
        }

        $this->utmCookieName = $utmCookieName;
        // cancel previous init
        $this->utmCookie = null;
    }

    /**
     * Set if even one utm value in _GET will overwrite all utm values or not.
     *
     * @param bool $overwrite
     */
    public function setOverwrite(bool $overwrite)
    {
        $this->overwrite = $overwrite;
        // cancel previous init
        $this->utmCookie = null;
    }

    /**
     * Set path for cookie.
     *
     * @param string $path
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }

    /**
     * Set secure for cookie.
     *
     * @param bool $secure
     */
    public function setSecure(bool $secure)
    {
        $this->secure = $secure;
    }

    /**
     * Initialize static values to default (or empty) values.
     */
    private function initStaticValues()
    {
        $this->utmCookie = [
            'utm_campaign' => null,
            'utm_medium' => null,
            'utm_source' => null,
            'utm_term' => null,
            'utm_content' => null
        ];
    }

    /**
     * Remove elements with null values from array.
     *
     * @param array|null $array
     *
     * @return array
     */
    private static function removeNullValues(array $array = null)
    {
        // null (undefined) or false (filter failed)
        if ($array === null || $array === false) {
            return [];
        }

        return array_filter(
            $array,
            function ($value) {
                return $value !== null;
            }
        );
    }

    /**
     * Save utmCookie value into _COOKIE and set actual $this->utmCookie value (call only from init).
     *
     * @param array $utmCookieSave
     */
    private function save(array $utmCookieSave)
    {
        $expire = (new DateTime())->getTimestamp() + $this->lifetime;

        setcookie($this->utmCookieName, json_encode($utmCookieSave), $expire, $this->path, $this->domain, $this->secure, $this->httpOnly);

        $this->utmCookie = $utmCookieSave;
    }
}
