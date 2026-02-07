<?php

namespace OpenCompany\AstronomyBundle\Coordinates;

use OpenCompany\AstronomyBundle\Calculations\CoordinateTransformations;
use OpenCompany\AstronomyBundle\Location;

class GeocentricEclipticalRectangularCoordinates extends AbstractRectangularCoordinates
{
    public function getGeocentricEclipticalSphericalCoordinates(): GeocentricEclipticalSphericalCoordinates
    {
        $coord = CoordinateTransformations::rectangular2spherical($this->x, $this->y, $this->z);

        return new GeocentricEclipticalSphericalCoordinates($coord[0], $coord[1], $coord[2]);
    }

    public function getGeocentricEquatorialRectangularCoordinates(float $T): GeocentricEquatorialRectangularCoordinates
    {
        return $this
            ->getGeocentricEclipticalSphericalCoordinates()
            ->getGeocentricEquatorialRectangularCoordinates($T);
    }

    public function getGeocentricEquatorialSphericalCoordinates(float $T): GeocentricEquatorialSphericalCoordinates
    {
        return $this
            ->getGeocentricEclipticalSphericalCoordinates()
            ->getGeocentricEquatorialSphericalCoordinates($T);
    }

    public function getLocalHorizontalCoordinates(Location $location, float $T): LocalHorizontalCoordinates
    {
        return $this
            ->getGeocentricEclipticalSphericalCoordinates()
            ->getLocalHorizontalCoordinates($location, $T);
    }
}
