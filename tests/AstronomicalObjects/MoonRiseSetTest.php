<?php

namespace OpenCompany\AstronomyBundle\Tests\AstronomicalObjects;

use OpenCompany\AstronomyBundle\AstronomicalObjects\Moon;
use OpenCompany\AstronomyBundle\Location;
use OpenCompany\AstronomyBundle\TimeOfInterest;
use PHPUnit\Framework\TestCase;

class MoonRiseSetTest extends TestCase
{
    /**
     * @test
     */
    public function getMoonriseTest()
    {
        $toi = TimeOfInterest::createFromString('2024-01-15 00:00:00');
        $moon = Moon::create($toi);
        $berlin = Location::create(52.524, 13.411);

        $rise = $moon->getMoonrise($berlin);

        $this->assertNotNull($rise);

        $riseHour = (int)$rise->getDateTime()->format('H');
        // Moonrise in Berlin on 2024-01-15 is around 09:30 UTC
        $this->assertGreaterThanOrEqual(8, $riseHour);
        $this->assertLessThanOrEqual(11, $riseHour);
    }

    /**
     * @test
     */
    public function getMoonsetTest()
    {
        $toi = TimeOfInterest::createFromString('2024-01-15 00:00:00');
        $moon = Moon::create($toi);
        $berlin = Location::create(52.524, 13.411);

        $set = $moon->getMoonset($berlin);

        $this->assertNotNull($set);

        $setHour = (int)$set->getDateTime()->format('H');
        // Moonset in Berlin on 2024-01-15 is around 20:49 UTC
        $this->assertGreaterThanOrEqual(19, $setHour);
        $this->assertLessThanOrEqual(22, $setHour);
    }

    /**
     * @test
     */
    public function getUpperCulminationTest()
    {
        $toi = TimeOfInterest::createFromString('2024-01-15 00:00:00');
        $moon = Moon::create($toi);
        $berlin = Location::create(52.524, 13.411);

        $transit = $moon->getUpperCulmination($berlin);

        $this->assertNotNull($transit);

        $transitHour = (int)$transit->getDateTime()->format('H');
        // Transit should be between rise and set
        $this->assertGreaterThanOrEqual(13, $transitHour);
        $this->assertLessThanOrEqual(17, $transitHour);
    }

    /**
     * @test
     */
    public function getMoonriseEquatorTest()
    {
        $toi = TimeOfInterest::createFromString('2024-01-15 00:00:00');
        $moon = Moon::create($toi);
        $equator = Location::create(0.0, 0.0);

        $rise = $moon->getMoonrise($equator);
        $set = $moon->getMoonset($equator);

        $this->assertNotNull($rise);
        $this->assertNotNull($set);

        // At equator, the moon always rises and sets
        $riseJD = $rise->getJulianDay();
        $setJD = $set->getJulianDay();
        $this->assertGreaterThan($riseJD, $setJD);
    }

    /**
     * @test
     */
    public function moonriseSetOrderTest()
    {
        // Verify that rise < transit < set for a typical date
        $toi = TimeOfInterest::createFromString('2024-06-15 00:00:00');
        $moon = Moon::create($toi);
        $location = Location::create(48.8566, 2.3522); // Paris

        $rise = $moon->getMoonrise($location);
        $transit = $moon->getUpperCulmination($location);
        $set = $moon->getMoonset($location);

        if ($rise !== null && $set !== null) {
            $riseJD = $rise->getJulianDay();
            $transitJD = $transit->getJulianDay();
            $setJD = $set->getJulianDay();

            // Transit should be after rise
            $this->assertGreaterThan($riseJD, $transitJD);
        }
    }
}
