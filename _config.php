<?php

if(!MultiDomain::get_primary_domain()) {
	throw new Exception('MultiDomain must define a "'.MultiDomain::KEY_PRIMARY.'" domain in the config, under "domains"');
}
