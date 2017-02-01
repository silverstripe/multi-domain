<?php

namespace SilverStripe\MultiDomain\Tests;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MultiDomain\MultiDomain;
use SilverStripe\MultiDomain\MultiDomainDomain;

class MultiDomainTest extends SapphireTest
{
    /**
     * Set up some test domain configuration
     *
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        Config::nest();

        Config::inst()->remove(MultiDomain::class, 'domains');
        Config::inst()->update(MultiDomain::class, 'domains', array(
            'primary' => array(
                'hostname' => 'foo.bar',
                'resolves_to' => 'bar.baz'
            ),
            'secondary' => array(
                'hostname' => 'localhost',
                'resolves_to' => 'local.dev'
            )
        ));
    }

    /**
     * Test that a MultiDomainDomain can be returned from the configured domains
     */
    public function testGetDomain()
    {
        $this->assertNull(MultiDomain::get_domain('does-not-exist'));
        $this->assertInstanceOf(MultiDomainDomain::class, MultiDomain::get_domain('primary'));
    }

    /**
     * Test that all domains can be returned, with or without the primary domain
     *
     * @dataProvider getAllDomainsProvider
     * @param bool $withPrimary
     */
    public function testGetAllDomains($withPrimary)
    {
        $result = MultiDomain::get_all_domains($withPrimary);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);

        $expectedCount = $withPrimary ? 2 : 1;
        $this->assertCount($expectedCount, $result);
    }

    /**
     * @return array[]
     */
    public function getAllDomainsProvider()
    {
        return array(
            array(true),
            array(false)
        );
    }

    /**
     * Test that the primary domain can be returned
     */
    public function testGetPrimaryDomain()
    {
        $result = MultiDomain::get_primary_domain();
        $this->assertInstanceOf(MultiDomainDomain::class, $result);
        $this->assertTrue($result->isPrimary());
    }

    /**
     * Test that the correct domain can be returned by a provided URL
     */
    public function testDomainForUrl()
    {
        $result = MultiDomain::domain_for_url('foo.bar/my-page');
        $this->assertInstanceOf(MultiDomainDomain::class, $result);
        $this->assertSame('primary', $result->getKey());
    }

    /**
     * Test that if a URL doesn't match any domain then the primary domain is returned
     */
    public function testDomainForUrlDefaultsToPrimaryDomain()
    {
        $this->assertTrue(MultiDomain::domain_for_url('does-not-exist.com')->isPrimary());
    }

    public function tearDown()
    {
        Config::unnest();
        parent::tearDown();
    }
}
