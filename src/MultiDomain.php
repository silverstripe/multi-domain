<?php

namespace SilverStripe\MultiDomain;

use SilverStripe\Core\Object;
use SilverStripe\MultiDomain\MultiDomainDomain;

/**
 * A utility class that provides several static methods for parsing URLs
 * and resolving hostnames.
 *
 * @package  silverstripe-multi-domain
 * @author  Aaron Carlino <aaron@silverstripe.com>
 */
class MultiDomain extends Object
{
    /**
     * The key for the "primary" domain
     *
     * @var string
     */
    const KEY_PRIMARY = 'primary';

    /**
     * Given a url, get the domain that maps to it, e.g.
     *
     * /company/ -> silverstripe.com
     * /company/partners -> silverstripe.com
     * /community/forum -> silverstripe.org
     *
     * @param  string $url
     * @return MultiDomainDomain
     */
    public static function domain_for_url($url)
    {
        $url = trim($url, '/');

        foreach (self::get_all_domains() as $domain) {
            if ($domain->hasURL($url)) {
                return $domain;
            }
        }

        return self::get_primary_domain();
    }

    /**
     * Gets all the domains that have been configured
     *
     * @param  boolean $includePrimary If true, include the primary domain
     * @return array
     */
    public static function get_all_domains($includePrimary = false)
    {
        $domains = array ();

        foreach (self::config()->domains as $key => $config) {
            if (!$includePrimary && $key === self::KEY_PRIMARY) {
                continue;
            }
            $domains[] = MultiDomainDomain::create($key, $config);
        }

        return $domains;
    }

    /**
     * Gets the domain marked as "primary"
     * @return MultiDomainDomain
     */
    public static function get_primary_domain()
    {
        return self::get_domain(self::KEY_PRIMARY);
    }

    /**
     * Gets a domain by its key, e.g. 'org','com'
     * @param  string $domain
     * @return MultiDomainDomain
     */
    public static function get_domain($domain)
    {
        if (isset(self::config()->domains[$domain])) {
            return MultiDomainDomain::create(
                $domain,
                self::config()->domains[$domain]
            );
        }
    }
}
