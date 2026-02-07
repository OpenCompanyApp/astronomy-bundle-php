<?php

namespace OpenCompany\AstronomyBundle\Tests\Events\LunarEclipse;

use OpenCompany\AstronomyBundle\Events\LunarEclipse\LunarEclipse;
use OpenCompany\AstronomyBundle\TimeOfInterest;
use PHPUnit\Framework\TestCase;

class LunarEclipseTest extends TestCase
{
    /**
     * @test
     * 2019-01-21 Total Lunar Eclipse
     * NASA reference: magnitude 1.1953, gamma 0.3684, greatest eclipse 05:12 UT
     */
    public function totalLunarEclipse20190121Test()
    {
        $toi = TimeOfInterest::createFromString('2019-01-21 00:00:00');
        $eclipse = LunarEclipse::create($toi);

        $this->assertEquals('total', $eclipse->getEclipseType());
        $this->assertEquals(1.19, round($eclipse->getUmbralMagnitude(), 2));
        $this->assertGreaterThan(1.0, $eclipse->getUmbralMagnitude());

        // Greatest eclipse should be around 05:12 UTC
        $max = $eclipse->getGreatestEclipseTOI();
        $this->assertNotNull($max);
        $this->assertEquals(5, (int)$max->getDateTime()->format('H'));

        // Semi-durations should exist for total eclipse
        $this->assertNotNull($eclipse->getSemiDurationTotal());
        $this->assertNotNull($eclipse->getSemiDurationPartial());
        $this->assertGreaterThan(0, $eclipse->getSemiDurationPenumbral());

        // Contact times should all exist for total eclipse
        $this->assertNotNull($eclipse->getContactP1());
        $this->assertNotNull($eclipse->getContactU1());
        $this->assertNotNull($eclipse->getContactU2());
        $this->assertNotNull($eclipse->getContactU3());
        $this->assertNotNull($eclipse->getContactU4());
        $this->assertNotNull($eclipse->getContactP4());
    }

    /**
     * @test
     * 2018-07-27 Total Lunar Eclipse (longest of 21st century)
     * NASA reference: magnitude 1.6087, greatest eclipse ~20:22 UT
     */
    public function totalLunarEclipse20180727Test()
    {
        $toi = TimeOfInterest::createFromString('2018-07-27 00:00:00');
        $eclipse = LunarEclipse::create($toi);

        $this->assertEquals('total', $eclipse->getEclipseType());
        $this->assertEquals(1.61, round($eclipse->getUmbralMagnitude(), 2));

        // Total semi-duration should be substantial (longest of century, ~50+ min)
        $sdTotal = $eclipse->getSemiDurationTotal();
        $this->assertNotNull($sdTotal);
        $this->assertGreaterThan(45, $sdTotal);
    }

    /**
     * @test
     * 2017-08-07 Partial Lunar Eclipse
     * NASA reference: magnitude 0.2464
     */
    public function partialLunarEclipse20170807Test()
    {
        $toi = TimeOfInterest::createFromString('2017-08-07 00:00:00');
        $eclipse = LunarEclipse::create($toi);

        $this->assertEquals('partial', $eclipse->getEclipseType());
        $this->assertEquals(0.25, round($eclipse->getUmbralMagnitude(), 2));

        // Partial eclipse: no total semi-duration
        $this->assertNull($eclipse->getSemiDurationTotal());
        $this->assertNotNull($eclipse->getSemiDurationPartial());

        // No U2/U3 contacts for partial eclipse
        $this->assertNull($eclipse->getContactU2());
        $this->assertNull($eclipse->getContactU3());

        // But U1/U4 should exist
        $this->assertNotNull($eclipse->getContactU1());
        $this->assertNotNull($eclipse->getContactU4());
    }

    /**
     * @test
     * 2023-10-28 Partial Lunar Eclipse
     * NASA reference: magnitude 0.1222
     */
    public function partialLunarEclipse20231028Test()
    {
        $toi = TimeOfInterest::createFromString('2023-10-28 00:00:00');
        $eclipse = LunarEclipse::create($toi);

        $this->assertEquals('partial', $eclipse->getEclipseType());
        $this->assertEquals(0.12, round($eclipse->getUmbralMagnitude(), 2));
    }

    /**
     * @test
     * 2024-03-25 Penumbral Lunar Eclipse
     * NASA reference: penumbral magnitude ~0.9557
     */
    public function penumbralLunarEclipse20240325Test()
    {
        $toi = TimeOfInterest::createFromString('2024-03-25 00:00:00');
        $eclipse = LunarEclipse::create($toi);

        $this->assertEquals('penumbral', $eclipse->getEclipseType());
        $this->assertLessThanOrEqual(0, $eclipse->getUmbralMagnitude());
        $this->assertGreaterThan(0, $eclipse->getPenumbralMagnitude());

        // No umbral contacts for penumbral eclipse
        $this->assertNull($eclipse->getContactU1());
        $this->assertNull($eclipse->getContactU2());
        $this->assertNull($eclipse->getContactU3());
        $this->assertNull($eclipse->getContactU4());

        // But penumbral contacts should exist
        $this->assertNotNull($eclipse->getContactP1());
        $this->assertNotNull($eclipse->getContactP4());
    }

    /**
     * @test
     * Non-eclipse date should return type 'none'
     */
    public function noEclipseTest()
    {
        $toi = TimeOfInterest::createFromString('2024-06-15 00:00:00');
        $eclipse = LunarEclipse::create($toi);

        $this->assertEquals('none', $eclipse->getEclipseType());
        $this->assertEquals(0.0, $eclipse->getUmbralMagnitude());
        $this->assertEquals(0.0, $eclipse->getPenumbralMagnitude());
        $this->assertNull($eclipse->getGreatestEclipseTOI());
    }

    /**
     * @test
     * Contact times should be in chronological order for total eclipse
     */
    public function contactTimesOrderTest()
    {
        $toi = TimeOfInterest::createFromString('2019-01-21 00:00:00');
        $eclipse = LunarEclipse::create($toi);

        $p1 = $eclipse->getContactP1()->getJulianDay();
        $u1 = $eclipse->getContactU1()->getJulianDay();
        $u2 = $eclipse->getContactU2()->getJulianDay();
        $max = $eclipse->getGreatestEclipseTOI()->getJulianDay();
        $u3 = $eclipse->getContactU3()->getJulianDay();
        $u4 = $eclipse->getContactU4()->getJulianDay();
        $p4 = $eclipse->getContactP4()->getJulianDay();

        $this->assertLessThan($u1, $p1);
        $this->assertLessThan($u2, $u1);
        $this->assertLessThan($max, $u2);
        $this->assertLessThan($u3, $max);
        $this->assertLessThan($u4, $u3);
        $this->assertLessThan($p4, $u4);
    }

    /**
     * @test
     * Gamma should be reasonable for known eclipses
     */
    public function gammaTest()
    {
        // Total eclipse: |gamma| should be small
        $toi = TimeOfInterest::createFromString('2019-01-21 00:00:00');
        $eclipse = LunarEclipse::create($toi);
        $this->assertLessThan(1.0, abs($eclipse->getGamma()));

        // Non-eclipse: gamma should be 0 (no eclipse computed)
        $toi = TimeOfInterest::createFromString('2024-06-15 00:00:00');
        $eclipse = LunarEclipse::create($toi);
        $this->assertEquals(0.0, $eclipse->getGamma());
    }
}
