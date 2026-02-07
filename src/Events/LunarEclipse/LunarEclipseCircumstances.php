<?php

namespace Andrmoel\AstronomyBundle\Events\LunarEclipse;

class LunarEclipseCircumstances
{
    private $type;
    private $gamma;
    private $umbralMagnitude;
    private $penumbralMagnitude;
    private $greatestEclipseJDE;
    private $penumbralRadius;
    private $umbralRadius;
    private $sdPenumbralMinutes;
    private $sdPartialMinutes;
    private $sdTotalMinutes;

    public function __construct(
        string $type,
        float $gamma,
        float $umbralMagnitude,
        float $penumbralMagnitude,
        float $greatestEclipseJDE,
        float $penumbralRadius,
        float $umbralRadius,
        float $sdPenumbralMinutes,
        ?float $sdPartialMinutes,
        ?float $sdTotalMinutes
    ) {
        $this->type = $type;
        $this->gamma = $gamma;
        $this->umbralMagnitude = $umbralMagnitude;
        $this->penumbralMagnitude = $penumbralMagnitude;
        $this->greatestEclipseJDE = $greatestEclipseJDE;
        $this->penumbralRadius = $penumbralRadius;
        $this->umbralRadius = $umbralRadius;
        $this->sdPenumbralMinutes = $sdPenumbralMinutes;
        $this->sdPartialMinutes = $sdPartialMinutes;
        $this->sdTotalMinutes = $sdTotalMinutes;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getGamma(): float
    {
        return $this->gamma;
    }

    public function getUmbralMagnitude(): float
    {
        return $this->umbralMagnitude;
    }

    public function getPenumbralMagnitude(): float
    {
        return $this->penumbralMagnitude;
    }

    public function getGreatestEclipseJDE(): float
    {
        return $this->greatestEclipseJDE;
    }

    public function getPenumbralRadius(): float
    {
        return $this->penumbralRadius;
    }

    public function getUmbralRadius(): float
    {
        return $this->umbralRadius;
    }

    public function getSdPenumbralMinutes(): float
    {
        return $this->sdPenumbralMinutes;
    }

    public function getSdPartialMinutes(): ?float
    {
        return $this->sdPartialMinutes;
    }

    public function getSdTotalMinutes(): ?float
    {
        return $this->sdTotalMinutes;
    }
}
