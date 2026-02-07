<?php

namespace OpenCompany\AstronomyBundle\AstronomicalObjects\Planets;

use OpenCompany\AstronomyBundle\AstronomicalObjects\AstronomicalObject;
use OpenCompany\AstronomyBundle\Calculations\TimeCalc;
use OpenCompany\AstronomyBundle\Calculations\VSOP87\EarthRectangularVSOP87;
use OpenCompany\AstronomyBundle\Calculations\VSOP87\VSOP87Interface;
use OpenCompany\AstronomyBundle\Calculations\VSOP87Calc;
use OpenCompany\AstronomyBundle\Coordinates\GeocentricEclipticalRectangularCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\GeocentricEclipticalSphericalCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\GeocentricEquatorialRectangularCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\GeocentricEquatorialSphericalCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\HeliocentricEclipticalRectangularCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\HeliocentricEclipticalSphericalCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\LocalHorizontalCoordinates;
use OpenCompany\AstronomyBundle\Corrections\GeocentricEclipticalSphericalCorrections;
use OpenCompany\AstronomyBundle\Corrections\LocalHorizontalCorrections;
use OpenCompany\AstronomyBundle\Events\RiseSetTransit\RiseSetTransit;
use OpenCompany\AstronomyBundle\Location;
use OpenCompany\AstronomyBundle\TimeOfInterest;
use OpenCompany\AstronomyBundle\Utils\AngleUtil;
use OpenCompany\AstronomyBundle\Utils\DistanceUtil;

abstract class Planet extends AstronomicalObject implements PlanetInterface
{
    /** @var VSOP87Interface */
    protected $VSOP87_SPHERICAL;

    /** @var VSOP87Interface */
    protected $VSOP87_RECTANGULAR;

    public function getHeliocentricEclipticalRectangularCoordinates(): HeliocentricEclipticalRectangularCoordinates
    {
        $t = $this->toi->getJulianMillenniaFromJ2000();
        $coefficients = VSOP87Calc::solve($this->VSOP87_RECTANGULAR, $t);

        $x = $coefficients[0];
        $y = $coefficients[1];
        $z = $coefficients[2];

        return new HeliocentricEclipticalRectangularCoordinates($x, $y, $z);
    }

    public function getHeliocentricEclipticalSphericalCoordinates(): HeliocentricEclipticalSphericalCoordinates
    {
        $t = $this->toi->getJulianMillenniaFromJ2000();
        $coefficients = VSOP87Calc::solve($this->VSOP87_SPHERICAL, $t);

        $L = $coefficients[0];
        $B = $coefficients[1];
        $R = $coefficients[2];

        $L = AngleUtil::normalizeAngle(rad2deg($L));
        $B = rad2deg($B);

        return new HeliocentricEclipticalSphericalCoordinates($L, $B, $R);
    }

    public function getGeocentricEclipticalSphericalCoordinates(): GeocentricEclipticalSphericalCoordinates
    {
        $T = $this->toi->getJulianCenturiesFromJ2000();
        $t = $this->toi->getJulianMillenniaFromJ2000();
        $JD = $this->toi->getJulianDay();

        $coefficientsEarth = VSOP87Calc::solve(EarthRectangularVSOP87::class, $t);

        // Meeus 33 - Light time corrections
        for ($i = 0; $i < 2; $i++) {
            $t = TimeCalc::julianDay2julianMillenniaJ2000($JD);

            $coefficients = VSOP87Calc::solve($this->VSOP87_RECTANGULAR, $t);

            // Get geocentric coordinates
            $X = $coefficients[0] - $coefficientsEarth[0];
            $Y = $coefficients[1] - $coefficientsEarth[1];
            $Z = $coefficients[2] - $coefficientsEarth[2];

            $d = sqrt(pow($X, 2) + pow($Y, 2) + pow($Z, 2));
            $tau = 0.0057755183 * $d;

            $JD -= $tau;
        }

        $geoEclRecCoord = new GeocentricEclipticalRectangularCoordinates($X, $Y, $Z);
        $geoEclSphCoord = $geoEclRecCoord->getGeocentricEclipticalSphericalCoordinates();

        // Meeus 33 - Aberration correction
        $geoEclSphCoord = GeocentricEclipticalSphericalCorrections::correctEffectOfAberration($geoEclSphCoord, $T);

        // Meeus 33 - Nutation correction
        $geoEclSphCoord = GeocentricEclipticalSphericalCorrections::correctEffectOfNutation($geoEclSphCoord, $T);

        return $geoEclSphCoord;
    }

    // TODO test it!
    public function getGeocentricEquatorialRectangularCoordinates(): GeocentricEquatorialRectangularCoordinates
    {
        return $this->getGeocentricEclipticalSphericalCoordinates()
            ->getGeocentricEquatorialRectangularCoordinates($this->T);
    }

    public function getGeocentricEquatorialSphericalCoordinates(): GeocentricEquatorialSphericalCoordinates
    {
        return $this
            ->getGeocentricEclipticalSphericalCoordinates()
            ->getGeocentricEquatorialSphericalCoordinates($this->T);
    }

    public function getLocalHorizontalCoordinates(Location $location, bool $refraction = true): LocalHorizontalCoordinates
    {
        $locHorCoord = $this
            ->getGeocentricEquatorialSphericalCoordinates()
            ->getLocalHorizontalCoordinates($location, $this->T);

        if ($refraction) {
            $locHorCoord = LocalHorizontalCorrections::correctAtmosphericRefraction($locHorCoord);
        }

        return $locHorCoord;
    }

    public function getRise(Location $location): TimeOfInterest
    {
        $ras = new RiseSetTransit(get_class($this), $location, $this->toi);
        return $ras->getRise();
    }

    public function getUpperCulmination(Location $location): TimeOfInterest
    {
        $ras = new RiseSetTransit(get_class($this), $location, $this->toi);
        return $ras->getTransit();
    }

    public function getSet(Location $location): TimeOfInterest
    {
        $ras = new RiseSetTransit(get_class($this), $location, $this->toi);
        return $ras->getSet();
    }

    public function getDistanceToEarthInAu(): float
    {
        $geoEclRecCoord = $this->getGeocentricEquatorialRectangularCoordinates();

        $x = $geoEclRecCoord->getX();
        $y = $geoEclRecCoord->getY();
        $z = $geoEclRecCoord->getZ();

        $d = sqrt($x * $x + $y * $y + $z * $z);

        return $d;
    }

    public function getDistanceToEarthInKm(): float
    {
        $d = $this->getDistanceToEarthInAu();

        return DistanceUtil::au2km($d);
    }
}
