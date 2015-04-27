<?php

class MultiDomainDomain extends Object {

	protected $hostname;


	protected $url;


	protected $key;

	public function __construct($key, $config) {
		$this->key = $key;
		$this->hostname = $config['hostname'];
		$this->url = isset($config['resolves_to']) ? $config['resolves_to'] : null;

		parent::__construct();
	}


	public function getHostname() {
		return $this->hostname;
	}


	public function getURL() {
		return $this->url;
	}


	public function isActive() {
		$allow_subdomains = MultiDomain::config()->allow_subdomains;
		$current_host = $_SERVER['HTTP_HOST'];
		$hostname = $this->getHostname();

		return $allow_subdomains ? 
					preg_match('/(\.|^)'.$hostname.'$/', $current_host) : 
					($current_host == $hostname);
	}


	public function isPrimary() {
		return $this->key === MultiDomain::KEY_PRIMARY;
	}


	public function getNativeURL($url) {
		if($this->isPrimary()) {
			throw new Exception("Cannot convert a native URL on the primary domain");		
		}

		return Controller::join_links($this->getURL(), $url);
	}


	public function getVanityURL($url) {
		if($this->isPrimary()) {
			return $url;
		}		
		
		return preg_replace('/^\/?'.$this->getURL().'\//', '', $url);
	}


}