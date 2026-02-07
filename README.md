# Astronomy Bundle PHP

> **Fork of [andrmoel/astronomy-bundle-php](https://github.com/andrmoel/astronomy-bundle-php)** — actively maintained by [OpenCompanyApp](https://github.com/OpenCompanyApp). Used in production by [OpenCompany](https://github.com/OpenCompanyApp) for AI agent astronomical capabilities.

A PHP library for astronomical calculations. Calculate positions of the Moon, Sun, and planets, sunrise/sunset times, moon phases, solar eclipses, coordinate transformations, and more.

Based on Jean Meeus' *Astronomical Algorithms* and the VSOP87 theory.

**[Full Documentation](docs.md)**

## Changes from upstream

- **OpenCompany namespace**: Rebranded to `OpenCompany\AstronomyBundle` namespace
- **Actively maintained** — this fork is used in production and will receive ongoing updates
- **PHP 8.4/8.5 compatibility**: Fixed all implicit nullable parameter deprecations (18 fixes across 15 files)
- **Moonrise & moonset**: Implemented using the existing RiseSetTransit framework with lunar parallax correction
- **Lunar eclipses**: Full algorithmic implementation based on Meeus Chapter 54 — type detection (total/partial/penumbral), magnitude, gamma, contact times (P1–P4, U1–U4), and semi-durations
- Requires `php ^7.2 || ^8.0` (works on PHP 8.0–8.5+)

## Installation

```console
composer require opencompanyapp/astronomy-bundle
```

## Features

| Feature | Status |
|---------|--------|
| Sun position (ecliptical, equatorial, horizontal) | Done |
| Sunrise, sunset & solar culmination | Done |
| Sun distance to Earth | Done |
| Moon position (ecliptical, equatorial, horizontal) | Done |
| Moon illumination, phase & bright limb angle | Done |
| Moon distance to Earth | Done |
| Moonrise & moonset | Done |
| All 7 planets (Mercury–Neptune) | Done |
| Planet rise, set & culmination | Done |
| Heliocentric & geocentric positions (VSOP87) | Done |
| Solar eclipses (type, contacts, obscuration, magnitude) | Done |
| Lunar eclipses (type, magnitude, contacts, semi-durations) | Done |
| Coordinate transformations (7 systems) | Done |
| Julian Day, sidereal time, equation of time | Done |
| Atmospheric refraction correction | Done |
| Distance between locations | Done |
| Earth nutation | Done |

## Quick start

```php
use OpenCompany\AstronomyBundle\AstronomicalObjects\Moon;
use OpenCompany\AstronomyBundle\AstronomicalObjects\Sun;
use OpenCompany\AstronomyBundle\AstronomicalObjects\Planets\Mars;
use OpenCompany\AstronomyBundle\Location;
use OpenCompany\AstronomyBundle\TimeOfInterest;

// Current moon phase
$moon = Moon::create();
$illumination = round($moon->getIlluminatedFraction() * 100, 1);
$waxing = $moon->isWaxingMoon() ? 'Waxing' : 'Waning';
echo "{$waxing}, {$illumination}% illuminated";

// Sunrise in Berlin
$location = Location::create(52.524, 13.411);
$sun = Sun::create();
$sunrise = $sun->getSunrise($location);
echo "Sunrise: " . $sunrise->getDateTime()->format('H:i') . " UTC";

// Mars position from Berlin
$mars = Mars::create();
$coords = $mars->getLocalHorizontalCoordinates($location);
echo "Mars: alt {$coords->getAltitude()}°, az {$coords->getAzimuth()}°";
```

### Moonrise & moonset

```php
$moon = Moon::create($toi);
$moonrise = $moon->getMoonrise($location);  // ?TimeOfInterest (null if none)
$moonset = $moon->getMoonset($location);
$transit = $moon->getUpperCulmination($location);
```

### Solar eclipses

```php
use OpenCompany\AstronomyBundle\Events\SolarEclipse\SolarEclipse;

$eclipse = SolarEclipse::create($toi, $location);
$eclipse->getEclipseType();        // "total", "partial", "annular"
$eclipse->getObscuration();        // 0.0 – 1.0
$eclipse->getMagnitude();          // eclipse magnitude
```

### Lunar eclipses

```php
use OpenCompany\AstronomyBundle\Events\LunarEclipse\LunarEclipse;

$eclipse = LunarEclipse::create($toi);
$eclipse->getEclipseType();          // "total", "partial", "penumbral", "none"
$eclipse->getUmbralMagnitude();      // umbral eclipse magnitude
$eclipse->getGreatestEclipseTOI();   // TimeOfInterest of greatest eclipse
```

See the **[full documentation](docs.md)** for complete API reference, coordinate systems, utilities, and more.

## License

MIT — see [LICENSE](LICENSE)

## Credits

Original library by [Andreas Möller](https://github.com/andrmoel). OpenCompany namespace, PHP 8.4+ compatibility, moonrise/moonset, and lunar eclipses by [OpenCompanyApp](https://github.com/OpenCompanyApp).
