<?php

/**
 * Class definition for an object representing a configured domain
 *
 * @package  silverstripe-multi-domain
 * @author  Aaron Carlino <aaron@silverstripe.com>
 */
class MultiDomainDomain extends Object {

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
	 * Constructor. Takes a key for the domain and its array of settings from the config
	 * @param string $key 
	 * @param array $config
	 */
	public function __construct($key, $config) {
		$this->key = $key;
		$this->hostname = $config['hostname'];
		$this->url = isset($config['resolves_to']) ? $config['resolves_to'] : null;

		parent::__construct();
	}

	/**
	 * Gets the hostname for the domain
	 * @return string
	 */
	public function getHostname() {
		return $this->hostname;
	}

	/**
	 * Gets the path that the hostname resolves to
	 * @return string
	 */
	public function getURL() {
		return $this->url;
	}

	/**
	 * Returns true if the domain is currently in the HTTP_HOST
	 * @return boolean
	 */
	public function isActive() {
		$allow_subdomains = MultiDomain::config()->allow_subdomains;
		$current_host = $_SERVER['HTTP_HOST'];
		$hostname = $this->getHostname();

		return $allow_subdomains ? 
					preg_match('/(\.|^)'.$hostname.'$/', $current_host) : 
					($current_host == $hostname);
	}

	/**
	 * Returns true if this domain is the primary domain
	 * @return boolean
	 */
	public function isPrimary() {
		return $this->key === MultiDomain::KEY_PRIMARY;
	}

	/**
	 * Gets the native URL for a vanity domain, e.g. /partners/ for .com
	 * returns /company/partners when .com is mapped to /company/.
	 * 
	 * @param  string $url
	 * @return string
	 */
	public function getNativeURL($url) {
		if($this->isPrimary()) {
			throw new Exception("Cannot convert a native URL on the primary domain");		
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
	public function getVanityURL($url) {
		if($this->isPrimary()) {
			return $url;
		}		
		
		return preg_replace('/^\/?'.$this->getURL().'\//', '', $url);
	}

}