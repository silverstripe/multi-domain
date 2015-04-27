<?php

class MultiDomainRequestFilter implements RequestFilter {

	public function preRequest(SS_HTTPRequest $request, Session $session, DataModel $model) {				
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

	public function postRequest(SS_HTTPRequest $request, SS_HTTPResponse $response, DataModel $model) {
	}


	protected function createNativeURLForDomain(MultiDomainDomain $domain) {
		return Controller::join_links(
			Director::baseURL(), 			
			$domain->getNativeURL($_SERVER['REQUEST_URI'])			
		);
	}
}
