<?php

namespace OpenCompany\AstronomyBundle\AstronomicalObjects\Planets;

use OpenCompany\AstronomyBundle\Calculations\VSOP87\JupiterRectangularVSOP87;
use OpenCompany\AstronomyBundle\Calculations\VSOP87\JupiterSphericalVSOP87;
use OpenCompany\AstronomyBundle\TimeOfInterest;

class Jupiter extends Planet
{
    protected $VSOP87_SPHERICAL = JupiterSphericalVSOP87::class;
    protected $VSOP87_RECTANGULAR = JupiterRectangularVSOP87::class;

    public static function create(?TimeOfInterest $toi = null): self
    {
        return new self($toi);
    }
}
