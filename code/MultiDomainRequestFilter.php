<?php

/**
 * A request filter for handling multi-domain logic
 *
 * @package  silverstripe-multi-domain
 * @author  Aaron Carlino <aaron@silverstripe.com>
 */
class MultiDomainRequestFilter implements RequestFilter {

	/**
	 * Gets the active domain, and sets its URL to the native one, with a vanity
	 * URL in the request
	 * 
	 * @param  SS_HTTPRequest $request 
	 * @param  Session        $session 
	 * @param  DataModel      $model 
	 */
	public function preRequest(SS_HTTPRequest $request, Session $session, DataModel $model) {				

		// Not the best place for validation, but _config.php is too early.
		if(!MultiDomain::get_primary_domain()) {
			throw new Exception('MultiDomain must define a "'.MultiDomain::KEY_PRIMARY.'" domain in the config, under "domains"');
		}
					
		foreach(MultiDomain::get_all_domains() as $domain) {			
			if(!$domain->isActive()) continue;
			
			$url = $this->createNativeURLForDomain($domain);
			$parts = explode('?', $url);					
			$request->setURL($parts[0]);			
		}		
	}

	/**
	 * Post request noop
	 * @param  SS_HTTPRequest  $request  
	 * @param  SS_HTTPResponse $response 
	 * @param  DataModel       $model    
	 */
	public function postRequest(SS_HTTPRequest $request, SS_HTTPResponse $response, DataModel $model) {
	}

	/**
	 * Creates a native URL for a domain. This functionality is abstracted so
	 * that other modules can overload it, e.g. translatable modules that 
	 * have their own custom URLs.
	 * 	
	 * @param  MultiDomainDomain $domain
	 * @return string
	 */
	protected function createNativeURLForDomain(MultiDomainDomain $domain) {
		return Controller::join_links(
			Director::baseURL(), 			
			$domain->getNativeURL($_SERVER['REQUEST_URI'])			
		);
	}
}
