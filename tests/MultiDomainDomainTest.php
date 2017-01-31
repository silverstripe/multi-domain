<?php

class MultiDomainDomainTest extends SapphireTest
{
    /**
     * Set up some test domain data for testing
     *
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        Config::nest();

        Config::inst()->remove('MultiDomain', 'domains');
        Config::inst()->update('MultiDomain', 'domains', array(
            'primary' => array(
                'hostname' => 'example.com'
            ),
            'store' => array(
                'hostname' => 'example-store.com',
                'resolves_to' => 'shop/store',
                'allow' => array(
                    'admin/*',
                    'Security/*',
                    'my-custom-webhook/'
                )
            ),
            'configurable' => array(
                'hostname' => 'MY_CONSTANT_HOSTNAME'
            ),
            'forceful' => array(
                'hostname' => 'forced.com',
                'force' => array(
                    'buy-now/*'
                )
            )
        ));
    }

    /**
     * Test that a hostname defined in a constant will override the default configuration, otherwise the default
     * configuration for the domain is returned
     */
    public function testGetHostname()
    {
        $configurableDomain = MultiDomain::get_domain('configurable');
        define('MY_CONSTANT_HOSTNAME', 'I am a constant');
        $this->assertSame('I am a constant', $configurableDomain->getHostname());

        $storeDomain = MultiDomain::get_domain('store');
        $this->assertSame('example-store.com', $storeDomain->getHostname());
    }

    /**
     * Test that the domain's "resolves to" property is returned for the URL if it is defined, otherwise null
     */
    public function testGetUrl()
    {
        $primaryDomain = MultiDomain::get_domain('primary');
        $this->assertNull($primaryDomain->getURL());

        $storeDomain = MultiDomain::get_domain('store');
        $this->assertSame('shop/store', $storeDomain->getURL());
    }

    /**
     * Test that a domain can be identified as the primary domain or otherwise
     */
    public function testIsPrimary()
    {
        $this->assertTrue(MultiDomain::get_primary_domain()->isPrimary());
        $this->assertFalse(MultiDomain::get_domain('store')->isPrimary());
    }

    /**
     * When the request URI matches one of the allowed rules for a domain, the isActive method should return false
     */
    public function testIsActiveReturnsFalseWhenRequestUriIsAllowedPath()
    {
        $domain = MultiDomain::get_domain('store');
        $domain->setRequestUri('/Security/login');
        $this->assertFalse($domain->isActive());
    }

    /**
     * When a subdomain is "allowed" and is requested, subdomains should be allowed through "isActive" as well
     * as the primary domain
     */
    public function testSubdomainsAllowedInIsActiveWhenConfigured()
    {
        Config::inst()->update('MultiDomain', 'allow_subdomains', true);

        $domain = MultiDomain::get_domain('store')
            ->setRequestUri('/some/page')
            ->setHttpHost('api.example-store.com');

        $this->assertTrue($domain->isActive());
    }

    /**
     * The default behaviour would be that if the current host from the request matchese that of the domain model
     * then isActive should be true
     */
    public function testReturnActiveIfCurrentHostMatchesDomainsHostname()
    {
        $domain = MultiDomain::get_domain('primary')
            ->setRequestUri('/another/page')
            ->setHttpHost('example.com');

        $this->assertTrue($domain->isActive());
    }

    /**
     * getNativeUrl should not be used on the primary domain
     *
     * @expectedException Exception
     * @expectedExceptionMessage Cannot convert a native URL on the primary domain
     */
    public function testGetNativeUrlThrowsExceptionOnPrimaryDomain()
    {
        MultiDomain::get_primary_domain()->getNativeUrl('foo');
    }

    /**
     * Test that a URL segment can be added to the domain's URL and returned as a "native URL"
     */
    public function testGetNativeUrl()
    {
        $domain = MultiDomain::get_domain('store');
        $this->assertSame('shop/store/foo/bar', $domain->getNativeUrl('foo/bar'));
    }

    /**
     * "Allowed" and "forced" URLs should just be returned from getNativeUrl as is
     */
    public function testGetNativeUrlReturnsInputWhenUrlIsAllowedOrForced()
    {
        $domain = MultiDomain::get_domain('store');
        $this->assertSame('my-custom-webhook/', $domain->getNativeUrl('my-custom-webhook/'));

        $domain = MultiDomain::get_domain('forceful');
        $this->assertSame('buy-now/whatever', $domain->getNativeUrl('buy-now/whatever'));
    }

    /**
     * The primary domain and "allowed" route matches should be returned as it
     */
    public function testGetVanityUrlReturnsInputWhenUrlIsAllowedOrIsPrimaryDomain()
    {
        $this->assertSame('/pages/info', MultiDomain::get_primary_domain()->getVanityUrl('/pages/info'));
        $this->assertSame('/Security/login', MultiDomain::get_domain('store')->getVanityUrl('/Security/login'));
    }

    /**
     * Non-primary domains and un-allowed route matches should be returned without their URL for vanity
     */
    public function testGetVanityUrl()
    {
        $this->assertSame('partners/', MultiDomain::get_domain('store')->getVanityUrl('shop/store/partners/'));
        $this->assertSame('foo/bar', MultiDomain::get_domain('store')->getVanityUrl('shop/store/foo/bar'));
    }

    public function tearDown()
    {
        Config::unnest();
        parent::tearDown();
    }
}
