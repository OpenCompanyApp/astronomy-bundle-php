<?php

namespace OpenCompany\AstronomyBundle\AstronomicalObjects;

use OpenCompany\AstronomyBundle\Coordinates\GeocentricEclipticalSphericalCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\GeocentricEquatorialRectangularCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\GeocentricEquatorialSphericalCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\LocalHorizontalCoordinates;
use OpenCompany\AstronomyBundle\Location;
use OpenCompany\AstronomyBundle\TimeOfInterest;

interface AstronomicalObjectInterface
{
    public static function create(?TimeOfInterest $toi = null);

    public function setTimeOfInterest(TimeOfInterest $toi): void;

    public function getTimeOfInterest(): TimeOfInterest;

    public function getGeocentricEclipticalSphericalCoordinates(): GeocentricEclipticalSphericalCoordinates;

    public function getGeocentricEquatorialRectangularCoordinates(): GeocentricEquatorialRectangularCoordinates;

    public function getGeocentricEquatorialSphericalCoordinates(): GeocentricEquatorialSphericalCoordinates;

    public function getLocalHorizontalCoordinates(Location $location, bool $refraction = true): LocalHorizontalCoordinates;

    public function getDistanceToEarth(): float;
}
