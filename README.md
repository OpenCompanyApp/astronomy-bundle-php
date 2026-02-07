# Astronomy Bundle PHP

> **Fork of [andrmoel/astronomy-bundle-php](https://github.com/andrmoel/astronomy-bundle-php)** — actively maintained by [OpenCompanyApp](https://github.com/OpenCompanyApp). Used in production by [OpenCompany](https://github.com/OpenCompanyApp) for AI agent astronomical capabilities.

A PHP library for astronomical calculations. Calculate positions of the Moon, Sun, and planets, sunrise/sunset times, moon phases, solar eclipses, coordinate transformations, and more.

Based on Jean Meeus' *Astronomical Algorithms* and the VSOP87 theory.

## Changes from upstream

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
use Andrmoel\AstronomyBundle\AstronomicalObjects\Moon;
use Andrmoel\AstronomyBundle\AstronomicalObjects\Sun;
use Andrmoel\AstronomyBundle\AstronomicalObjects\Planets\Mars;
use Andrmoel\AstronomyBundle\Location;
use Andrmoel\AstronomyBundle\TimeOfInterest;

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

// Solar eclipse (Great American Eclipse, Madras OR)
use Andrmoel\AstronomyBundle\Events\SolarEclipse\SolarEclipse;

$toi = TimeOfInterest::createFromString('2017-08-21');
$madras = Location::create(44.61040, -121.23848);
$eclipse = SolarEclipse::create($toi, $madras);
echo "Type: " . $eclipse->getEclipseType();
echo "Obscuration: " . round($eclipse->getObscuration() * 100) . "%";
```

## Time of Interest

The `TimeOfInterest` (TOI) object represents the time for all calculations. It supports dates beyond PHP's DateTime range (before year 1000).

```php
// Multiple creation methods
$toi = TimeOfInterest::createFromString('2024-06-15 12:00:00');
$toi = TimeOfInterest::createFromCurrentTime();
$toi = TimeOfInterest::createFromJulianDay(2460476.0);

// Time calculations
$JD = $toi->getJulianDay();
$T = $toi->getJulianCenturiesFromJ2000();
$GMST = $toi->getGreenwichMeanSiderealTime();
$GAST = $toi->getGreenwichApparentSiderealTime();
```

## Coordinate systems

Seven coordinate systems with full transformation support:

- Geocentric Ecliptical Spherical (longitude, latitude)
- Geocentric Equatorial Spherical (right ascension, declination)
- Geocentric Equatorial Rectangular (X, Y, Z)
- Heliocentric Ecliptical Spherical (longitude, latitude)
- Heliocentric Ecliptical Rectangular (X, Y, Z)
- Heliocentric Equatorial Rectangular (x, y, z)
- Local Horizontal (azimuth, altitude) — with atmospheric refraction

## Planets

All 7 planets are supported with heliocentric and geocentric positions, rise/set/culmination, and local horizontal coordinates:

```php
$toi = TimeOfInterest::createFromString('2024-06-15 22:00:00');
$location = Location::create(52.524, 13.411);

$venus = Venus::create($toi);
$coords = $venus->getLocalHorizontalCoordinates($location);
$rise = $venus->getRise($location);
$set = $venus->getSet($location);
```

Available planets: `Mercury`, `Venus`, `Mars`, `Jupiter`, `Saturn`, `Uranus`, `Neptune`

## Solar eclipses

Calculate local circumstances for any solar eclipse:

```php
$eclipse = SolarEclipse::create($toi, $location);

$eclipse->getEclipseType();        // "total", "partial", "annular"
$eclipse->getObscuration();        // 0.0 – 1.0
$eclipse->getMagnitude();          // eclipse magnitude
$eclipse->getEclipseDuration();    // total duration in seconds
$eclipse->getEclipseUmbraDuration(); // totality duration in seconds
$eclipse->getMoonSunRatio();       // apparent size ratio

// Contact times (C1, C2, MAX, C3, C4)
$c1 = $eclipse->getCircumstancesC1();
$max = $eclipse->getCircumstancesMax();
$toiMax = $eclipse->getTimeOfInterest($max);
```

## Moonrise & moonset

```php
$toi = TimeOfInterest::createFromString('2024-06-15 00:00:00');
$location = Location::create(52.524, 13.411);

$moon = Moon::create($toi);
$moonrise = $moon->getMoonrise($location);
$moonset = $moon->getMoonset($location);
$transit = $moon->getUpperCulmination($location);

// Returns null if the moon doesn't rise/set at the given location and date
echo "Moonrise: " . $moonrise->getDateTime()->format('H:i') . " UTC";
```

## Lunar eclipses

Calculate circumstances for any lunar eclipse. Unlike solar eclipses, lunar eclipses are global events — the type, magnitude, and timing are the same for all observers.

```php
use Andrmoel\AstronomyBundle\Events\LunarEclipse\LunarEclipse;

$toi = TimeOfInterest::createFromString('2019-01-21');
$eclipse = LunarEclipse::create($toi);

$eclipse->getEclipseType();          // "total", "partial", "penumbral", "none"
$eclipse->getUmbralMagnitude();      // umbral eclipse magnitude
$eclipse->getPenumbralMagnitude();   // penumbral eclipse magnitude
$eclipse->getGamma();               // closest approach to shadow axis
$eclipse->getGreatestEclipseTOI();  // TimeOfInterest of greatest eclipse

// Semi-durations (in minutes)
$eclipse->getSemiDurationPenumbral(); // penumbral phase
$eclipse->getSemiDurationPartial();   // partial (umbral) phase, null if penumbral
$eclipse->getSemiDurationTotal();     // total phase, null if not total

// Contact times (P1, U1, U2, greatest, U3, U4, P4)
$p1 = $eclipse->getContactP1();  // penumbra first contact
$u1 = $eclipse->getContactU1();  // umbra first contact
$u2 = $eclipse->getContactU2();  // totality begins
$u3 = $eclipse->getContactU3();  // totality ends
$u4 = $eclipse->getContactU4();  // umbra last contact
$p4 = $eclipse->getContactP4();  // penumbra last contact
```

## License

MIT — see [LICENSE](LICENSE)

## Credits

Original library by [Andreas Möller](https://github.com/andrmoel). PHP 8.4+ compatibility by [OpenCompanyApp](https://github.com/OpenCompanyApp).
