<?php

namespace Andrmoel\AstronomyBundle\Events\LunarEclipse;

use Andrmoel\AstronomyBundle\Calculations\TimeCalc;
use Andrmoel\AstronomyBundle\TimeOfInterest;

/**
 * Lunar eclipse calculations based on Meeus "Astronomical Algorithms" Chapter 54.
 *
 * Lunar eclipses are global events - the type, magnitude, and timing are the same
 * for all observers (unlike solar eclipses). Location only affects whether the moon
 * is above the horizon during the eclipse.
 */
class LunarEclipse
{
    const TYPE_NONE = 'none';
    const TYPE_PENUMBRAL = 'penumbral';
    const TYPE_PARTIAL = 'partial';
    const TYPE_TOTAL = 'total';

    /** @var LunarEclipseCircumstances|null */
    private $circumstances;

    /** @var TimeOfInterest */
    private $toi;

    private function __construct(TimeOfInterest $toi)
    {
        $this->toi = $toi;
        $this->circumstances = $this->compute();
    }

    public static function create(TimeOfInterest $toi): self
    {
        return new self($toi);
    }

    public function getEclipseType(): string
    {
        return $this->circumstances ? $this->circumstances->getType() : self::TYPE_NONE;
    }

    public function getUmbralMagnitude(): float
    {
        return $this->circumstances ? $this->circumstances->getUmbralMagnitude() : 0.0;
    }

    public function getPenumbralMagnitude(): float
    {
        return $this->circumstances ? $this->circumstances->getPenumbralMagnitude() : 0.0;
    }

    public function getGamma(): float
    {
        return $this->circumstances ? $this->circumstances->getGamma() : 0.0;
    }

    public function getGreatestEclipseJDE(): float
    {
        return $this->circumstances ? $this->circumstances->getGreatestEclipseJDE() : 0.0;
    }

    /**
     * Get TimeOfInterest for greatest eclipse (converted from TDT to UT)
     */
    public function getGreatestEclipseTOI(): ?TimeOfInterest
    {
        if (!$this->circumstances) {
            return null;
        }

        $jde = $this->circumstances->getGreatestEclipseJDE();

        return $this->jde2toi($jde);
    }

    /**
     * Get semi-duration of penumbral phase in minutes
     */
    public function getSemiDurationPenumbral(): float
    {
        return $this->circumstances ? $this->circumstances->getSdPenumbralMinutes() : 0.0;
    }

    /**
     * Get semi-duration of partial (umbral) phase in minutes, null if not partial/total
     */
    public function getSemiDurationPartial(): ?float
    {
        return $this->circumstances ? $this->circumstances->getSdPartialMinutes() : null;
    }

    /**
     * Get semi-duration of total phase in minutes, null if not total
     */
    public function getSemiDurationTotal(): ?float
    {
        return $this->circumstances ? $this->circumstances->getSdTotalMinutes() : null;
    }

    /**
     * P1 - Penumbra first contact
     */
    public function getContactP1(): ?TimeOfInterest
    {
        if (!$this->circumstances) {
            return null;
        }

        $jde = $this->circumstances->getGreatestEclipseJDE();
        $sd = $this->circumstances->getSdPenumbralMinutes();
        $jdeP1 = $jde - ($sd / 1440.0); // minutes to days

        return $this->jde2toi($jdeP1);
    }

    /**
     * U1 - Umbra first contact (partial/total only)
     */
    public function getContactU1(): ?TimeOfInterest
    {
        if (!$this->circumstances) {
            return null;
        }

        $sd = $this->circumstances->getSdPartialMinutes();
        if ($sd === null) {
            return null;
        }

        $jde = $this->circumstances->getGreatestEclipseJDE();
        $jdeU1 = $jde - ($sd / 1440.0);

        return $this->jde2toi($jdeU1);
    }

    /**
     * U2 - Total eclipse begins (total only)
     */
    public function getContactU2(): ?TimeOfInterest
    {
        if (!$this->circumstances) {
            return null;
        }

        $sd = $this->circumstances->getSdTotalMinutes();
        if ($sd === null) {
            return null;
        }

        $jde = $this->circumstances->getGreatestEclipseJDE();
        $jdeU2 = $jde - ($sd / 1440.0);

        return $this->jde2toi($jdeU2);
    }

    /**
     * U3 - Total eclipse ends (total only)
     */
    public function getContactU3(): ?TimeOfInterest
    {
        if (!$this->circumstances) {
            return null;
        }

        $sd = $this->circumstances->getSdTotalMinutes();
        if ($sd === null) {
            return null;
        }

        $jde = $this->circumstances->getGreatestEclipseJDE();
        $jdeU3 = $jde + ($sd / 1440.0);

        return $this->jde2toi($jdeU3);
    }

    /**
     * U4 - Umbra last contact (partial/total only)
     */
    public function getContactU4(): ?TimeOfInterest
    {
        if (!$this->circumstances) {
            return null;
        }

        $sd = $this->circumstances->getSdPartialMinutes();
        if ($sd === null) {
            return null;
        }

        $jde = $this->circumstances->getGreatestEclipseJDE();
        $jdeU4 = $jde + ($sd / 1440.0);

        return $this->jde2toi($jdeU4);
    }

    /**
     * P4 - Penumbra last contact
     */
    public function getContactP4(): ?TimeOfInterest
    {
        if (!$this->circumstances) {
            return null;
        }

        $jde = $this->circumstances->getGreatestEclipseJDE();
        $sd = $this->circumstances->getSdPenumbralMinutes();
        $jdeP4 = $jde + ($sd / 1440.0);

        return $this->jde2toi($jdeP4);
    }

    // ---- Private computation methods ----

    /**
     * Compute lunar eclipse circumstances for the full moon nearest to the given TOI.
     * Based on Meeus "Astronomical Algorithms" Chapter 54.
     */
    private function compute(): ?LunarEclipseCircumstances
    {
        $JD = $this->toi->getJulianDay();

        // Convert JD to approximate decimal year
        $year = 2000.0 + ($JD - 2451545.0) / 365.25;

        // Find lunation number k for nearest full moon (k = integer + 0.5)
        $kRaw = ($year - 2000) * 12.3685;
        $k = floor($kRaw) + 0.5;

        // Also check previous full moon and pick whichever is closest to target
        $jde1 = $this->getMeanFullMoonJDE($k);
        $jde2 = $this->getMeanFullMoonJDE($k - 1.0);
        if (abs($jde2 - $JD) < abs($jde1 - $JD)) {
            $k = $k - 1.0;
        }

        return $this->computeEclipseForK($k);
    }

    /**
     * Compute the mean JDE of the full moon for the given lunation number k.
     * Meeus equation 49.1
     */
    private function getMeanFullMoonJDE(float $k): float
    {
        $T = $k / 1236.85;

        return 2451550.09766
            + 29.530588861 * $k
            + 0.00015437 * pow($T, 2)
            - 0.00000015 * pow($T, 3)
            + 0.00000000073 * pow($T, 4);
    }

    /**
     * Compute eclipse data for a specific full moon k.
     * Returns LunarEclipseCircumstances or null if no eclipse.
     * Based on Meeus Chapter 54.
     */
    private function computeEclipseForK(float $k): ?LunarEclipseCircumstances
    {
        $T = $k / 1236.85;

        // F - Moon's argument of latitude (Meeus Ch. 49)
        // Linear term uses k, higher-order terms use T
        $F = 160.7108
            + 390.67050284 * $k
            - 0.0016118 * pow($T, 2)
            - 0.00000227 * pow($T, 3)
            + 0.000000011 * pow($T, 4);
        $F = $this->normalizeAngle($F);

        // Eclipse check: if |sin(F)| > 0.36, no eclipse possible
        $FRad = deg2rad($F);
        if (abs(sin($FRad)) > 0.36) {
            return null;
        }

        // E - eccentricity correction
        $E = 1 - 0.002516 * $T - 0.0000074 * pow($T, 2);

        // M - Sun's mean anomaly
        $M = 2.5534
            + 29.1053567 * $k
            - 0.0000014 * pow($T, 2)
            - 0.00000011 * pow($T, 3);
        $M = $this->normalizeAngle($M);
        $MRad = deg2rad($M);

        // M' - Moon's mean anomaly
        $Mp = 201.5643
            + 385.81693528 * $k
            + 0.0107582 * pow($T, 2)
            + 0.00001238 * pow($T, 3)
            - 0.000000058 * pow($T, 4);
        $Mp = $this->normalizeAngle($Mp);
        $MpRad = deg2rad($Mp);

        // Omega - longitude of ascending node
        $Omega = 124.7746
            - 1.56375588 * $k
            + 0.0020672 * pow($T, 2)
            + 0.00000215 * pow($T, 3);
        $Omega = $this->normalizeAngle($Omega);
        $OmegaRad = deg2rad($Omega);

        // F1 - corrected argument of latitude
        $F1 = $F - 0.02665 * sin($OmegaRad);
        $F1Rad = deg2rad($F1);

        // A1 - planetary correction
        $A1 = 299.77 + 0.107408 * $k - 0.009173 * pow($T, 2);
        $A1Rad = deg2rad($A1);

        // Mean JDE of full moon
        $jm = $this->getMeanFullMoonJDE($k);

        // JDE of greatest eclipse (equation 54.1)
        // Lunar eclipse coefficients: c1 = -0.4065, c2 = 0.1727
        $jmax = $jm
            - 0.4065 * sin($MpRad)
            + 0.1727 * $E * sin($MRad)
            + 0.0161 * sin(2 * $MpRad)
            - 0.0097 * sin(2 * $F1Rad)
            + 0.0073 * $E * sin($MpRad - $MRad)
            - 0.0050 * $E * sin($MpRad + $MRad)
            - 0.0023 * sin($MpRad - 2 * $F1Rad)
            + 0.0021 * $E * sin(2 * $MRad)
            + 0.0012 * sin($MpRad + 2 * $F1Rad)
            + 0.0006 * $E * sin(2 * $MpRad + $MRad)
            - 0.0004 * sin(3 * $MpRad)
            - 0.0003 * $E * sin($MRad + 2 * $F1Rad)
            + 0.0003 * sin($A1Rad)
            - 0.0002 * $E * sin($MRad - 2 * $F1Rad)
            - 0.0002 * $E * sin(2 * $MpRad - $MRad)
            - 0.0002 * sin($OmegaRad);

        // P - equation 54
        $P = 0.207 * $E * sin($MRad)
            + 0.0024 * $E * sin(2 * $MRad)
            - 0.0392 * sin($MpRad)
            + 0.0116 * sin(2 * $MpRad)
            - 0.0073 * $E * sin($MpRad + $MRad)
            + 0.0067 * $E * sin($MpRad - $MRad)
            + 0.0118 * sin(2 * $F1Rad);

        // Q - equation 54
        $Q = 5.2207
            - 0.0048 * $E * cos($MRad)
            + 0.0020 * $E * cos(2 * $MRad)
            - 0.3299 * cos($MpRad)
            - 0.0060 * $E * cos($MpRad + $MRad)
            + 0.0041 * $E * cos($MpRad - $MRad);

        // W
        $W = abs(cos($F1Rad));

        // gamma - closest approach of Moon center to shadow axis
        $gamma = ($P * cos($F1Rad) + $Q * sin($F1Rad)) * (1 - 0.0048 * $W);

        // u - radius parameter for Earth's shadow cones
        $u = 0.0059
            + 0.0046 * $E * cos($MRad)
            - 0.0182 * cos($MpRad)
            + 0.0004 * cos(2 * $MpRad)
            - 0.0005 * cos($MRad + $MpRad);

        $absGamma = abs($gamma);

        // Umbral magnitude (equation 54.3)
        $umbralMagnitude = (1.0128 - $u - $absGamma) / 0.545;

        // Penumbral magnitude (equation 54.4)
        $penumbralMagnitude = (1.5573 + $u - $absGamma) / 0.545;

        // No eclipse if penumbral magnitude <= 0
        if ($penumbralMagnitude <= 0) {
            return null;
        }

        // Determine type
        if ($umbralMagnitude > 1.0) {
            $type = self::TYPE_TOTAL;
        } elseif ($umbralMagnitude > 0.0) {
            $type = self::TYPE_PARTIAL;
        } else {
            $type = self::TYPE_PENUMBRAL;
        }

        // Shadow cone radii (in Earth radii)
        $rho = 1.2848 + $u; // penumbral
        $sigma = 0.7403 - $u; // umbral

        // n - rate of Moon's motion through shadow
        $n = 0.5458 + 0.04 * cos($MpRad);

        $gamma2 = $gamma * $gamma;

        // Semi-duration of penumbral phase (hours)
        $h = 1.5573 + $u;
        $hh = $h * $h - $gamma2;
        $sdPenumbralHours = $hh > 0 ? sqrt($hh) / $n : 0;

        // Semi-duration of partial (umbral) phase (hours)
        $sdPartialHours = null;
        if ($umbralMagnitude > 0) {
            $p = 1.0128 - $u;
            $pp = $p * $p - $gamma2;
            $sdPartialHours = $pp > 0 ? sqrt($pp) / $n : 0;
        }

        // Semi-duration of total phase (hours)
        $sdTotalHours = null;
        if ($umbralMagnitude > 1.0) {
            $t = 0.4678 - $u;
            $tt = $t * $t - $gamma2;
            $sdTotalHours = $tt > 0 ? sqrt($tt) / $n : 0;
        }

        return new LunarEclipseCircumstances(
            $type,
            $gamma,
            $umbralMagnitude,
            $penumbralMagnitude,
            $jmax,
            $rho,
            $sigma,
            $sdPenumbralHours * 60, // convert to minutes
            $sdPartialHours !== null ? $sdPartialHours * 60 : null,
            $sdTotalHours !== null ? $sdTotalHours * 60 : null
        );
    }

    /**
     * Convert JDE (TDT) to TimeOfInterest (UT) by applying Delta T correction
     */
    private function jde2toi(float $jde): TimeOfInterest
    {
        // Get approximate year/month for Delta T lookup
        $time = TimeCalc::julianDay2time($jde);
        $deltaT = TimeCalc::getDeltaT($time->year, $time->month);

        // JD_UT = JDE - deltaT/86400
        $jdUT = $jde - $deltaT / 86400.0;

        return TimeOfInterest::createFromJulianDay($jdUT);
    }

    /**
     * Normalize angle to 0-360 range
     */
    private function normalizeAngle(float $angle): float
    {
        $angle = fmod($angle, 360.0);
        if ($angle < 0) {
            $angle += 360.0;
        }

        return $angle;
    }
}
