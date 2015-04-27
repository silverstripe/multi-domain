<?php

class MultiDomain extends Object {

	const KEY_PRIMARY = 'primary';


	public static function domain_for_url($url) {
		$url = trim($url, '/');
		
		foreach(self::get_all_domains() as $domain) {			
			$domainBaseURL = trim($domain->getURL(),'/');
			if(preg_match('/^'.$domainBaseURL.'/', $url)) {
				return $domain;
			}
		}

		return self::get_primary_domain();
	}


	public static function get_all_domains($includePrimary = false) {
		$domains = array ();
		
		foreach(self::config()->domains as $key => $config) {
			if(!$includePrimary && $key === self::KEY_PRIMARY) continue;
			$domains[] = MultiDomainDomain::create($key, $config);
		}

		return $domains;
	}
	

	public static function get_primary_domain() {
		return self::get_domain(self::KEY_PRIMARY);
	}


	public static function get_domain($domain) {
		if(isset(self::config()->domains[$domain])) {
			return MultiDomainDomain::create(
				$domain,
				self::config()->domains[$domain]
			);				
		}
	}
}