<?php

namespace OpenCompany\AstronomyBundle\AstronomicalObjects\Planets;

use OpenCompany\AstronomyBundle\Calculations\VSOP87\VenusRectangularVSOP87;
use OpenCompany\AstronomyBundle\Calculations\VSOP87\VenusSphericalVSOP87;
use OpenCompany\AstronomyBundle\TimeOfInterest;

class Venus extends Planet
{
    protected $VSOP87_SPHERICAL = VenusSphericalVSOP87::class;
    protected $VSOP87_RECTANGULAR = VenusRectangularVSOP87::class;

    public static function create(?TimeOfInterest $toi = null): self
    {
        return new self($toi);
    }
}
