<?php

namespace OpenCompany\AstronomyBundle\AstronomicalObjects\Planets;

use OpenCompany\AstronomyBundle\Coordinates\GeocentricEclipticalSphericalCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\GeocentricEquatorialRectangularCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\GeocentricEquatorialSphericalCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\HeliocentricEclipticalRectangularCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\HeliocentricEclipticalSphericalCoordinates;
use OpenCompany\AstronomyBundle\Coordinates\LocalHorizontalCoordinates;
use OpenCompany\AstronomyBundle\Location;
use OpenCompany\AstronomyBundle\TimeOfInterest;

interface PlanetInterface
{
    public function setTimeOfInterest(TimeOfInterest $toi): void;

    public function getTimeOfInterest(): TimeOfInterest;

    public function getHeliocentricEclipticalRectangularCoordinates(): HeliocentricEclipticalRectangularCoordinates;

    public function getHeliocentricEclipticalSphericalCoordinates(): HeliocentricEclipticalSphericalCoordinates;

    public function getGeocentricEclipticalSphericalCoordinates(): GeocentricEclipticalSphericalCoordinates;

    public function getGeocentricEquatorialRectangularCoordinates(): GeocentricEquatorialRectangularCoordinates;

    public function getGeocentricEquatorialSphericalCoordinates(): GeocentricEquatorialSphericalCoordinates;

    public function getLocalHorizontalCoordinates(Location $location, bool $refraction = true): LocalHorizontalCoordinates;

    public function getRise(Location $location): TimeOfInterest;

    public function getUpperCulmination(Location $location): TimeOfInterest;

    public function getSet(Location $location): TimeOfInterest;
}
