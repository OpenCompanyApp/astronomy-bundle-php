<?php

namespace OpenCompany\AstronomyBundle\AstronomicalObjects\Planets;

use OpenCompany\AstronomyBundle\Calculations\VSOP87\NeptuneRectangularVSOP87;
use OpenCompany\AstronomyBundle\Calculations\VSOP87\NeptuneSphericalVSOP87;
use OpenCompany\AstronomyBundle\TimeOfInterest;

class Neptune extends Planet
{
    protected $VSOP87_SPHERICAL = NeptuneSphericalVSOP87::class;
    protected $VSOP87_RECTANGULAR = NeptuneRectangularVSOP87::class;

    public static function create(?TimeOfInterest $toi = null): self
    {
        return new self($toi);
    }
}
