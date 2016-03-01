<?php

/**
 * Class definition for an object representing a configured domain
 *
 * @package  silverstripe-multi-domain
 * @author  Aaron Carlino <aaron@silverstripe.com>
 */
class MultiDomainDomain extends Object
{

    /**
     * The hostname of the domain, e.g. silverstripe.org
     * @var string
     */
    protected $hostname;

    /**
     * The path that the hostname resolves to
     * @var string
     */
    protected $url;

    /**
     * The identifier of the domain, e.g. 'org','com'
     * @var string
     */
    protected $key;

    /**
     * Paths that are allowed to be accessed on the primary domain
     * @var array
     */
    protected $allowedPaths;

    /**
     * Paths that are forced from the primary domain into a vanity one,
     * outside the resolves_to path
     * 
     * @var array
     */
    protected $forcedPaths;

    /**
     * Constructor. Takes a key for the domain and its array of settings from the config
     * @param string $key 
     * @param array $config
     */
    public function __construct($key, $config)
    {
        $this->key = $key;
        $this->hostname = $config['hostname'];
        $this->url = isset($config['resolves_to']) ? $config['resolves_to'] : null;

        $globalAllowed = (array) Config::inst()->get('MultiDomain', 'allow');
        $globalForced = (array) Config::inst()->get('MultiDomain', 'force');
        $myAllowed = isset($config['allow']) ? $config['allow'] : array();
        $myForced = isset($config['force']) ? $config['force'] : array();
        $this->allowedPaths = array_merge($globalAllowed, $myAllowed);
        $this->forcedPaths = array_merge($globalForced, $myForced);

        parent::__construct();
    }

    /**
     * Gets the hostname for the domain
     * @return string
     */
    public function getHostname()
    {
        return defined($this->hostname) ? constant($this->hostname) : $this->hostname;
    }

    /**
     * Gets the path that the hostname resolves to
     * @return string
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * Returns true if the domain is currently in the HTTP_HOST
     * @return boolean
     */
    public function isActive()
    {
        if ($this->isAllowedPath($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $current_host = $_SERVER['HTTP_HOST'];
        $allow_subdomains = MultiDomain::config()->allow_subdomains;
        $hostname = $this->getHostname();

        return $allow_subdomains ?
                    preg_match('/(\.|^)'.$hostname.'$/', $current_host) :
                    ($current_host == $hostname);
    }

    /**
     * Returns true if this domain is the primary domain
     * @return boolean
     */
    public function isPrimary()
    {
        return $this->key === MultiDomain::KEY_PRIMARY;
    }

    /**
     * Gets the native URL for a vanity domain, e.g. /partners/ for .com
     * returns /company/partners when .com is mapped to /company/.
     * 
     * @param  string $url
     * @return string
     */
    public function getNativeURL($url)
    {
        if ($this->isPrimary()) {
            throw new Exception("Cannot convert a native URL on the primary domain");
        }

        if ($this->isAllowedPath($url) || $this->isForcedPath($url)) {
            return $url;
        }

        return Controller::join_links($this->getURL(), $url);
    }

    /**
     * Gets athe vanity URL given a native URL. /company/partners returns /partners/
     * when .com is mapped to /company/.
     * 
     * @param  string $url
     * @return string
     */
    public function getVanityURL($url)
    {
        if ($this->isPrimary() || $this->isAllowedPath($url)) {
            return $url;
        }
        
        return preg_replace('/^\/?'.$this->getURL().'\//', '', $url);
    }

    /**
     * Return true if this domain contains the given URL
     * @param  strin  $url 
     * @return boolean
     */
    public function hasURL($url)
    {
        if ($this->isForcedPath($url)) {
            return true;
        }
        $domainBaseURL = trim($this->getURL(), '/');
        if (preg_match('/^'.$domainBaseURL.'/', $url)) {
            return true;
        }

        return false;
    }

    /**
     * Checks a given list of wildcard patterns to see if a path is allowed
     * @param  string  $url 
     * @return boolean      
     */
    protected function isAllowedPath($url)
    {
        return self::match_url($url, $this->allowedPaths);
    }

    /**
     * Checks a given list of wildcard patterns to see if a path is allowed
     * @param  string  $url 
     * @return boolean      
     */
    protected function isForcedPath($url)
    {
        return self::match_url($url, $this->forcedPaths);
    }

    /**
     * Matches a URL against a list of wildcard patterns
     * @param  string $url      
     * @param  array $patterns 
     * @return boolean           
     */
    protected static function match_url($url, $patterns)
    {
        if (!is_array($patterns)) {
            return false;
        }

        $url = ltrim($url, '/');
        if (substr($url, -1) !== '/') {
            $url .= '/';
        }

        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $url)) {
                return true;
            }
        }

        return false;
    }
}
