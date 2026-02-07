# Astronomy Bundle PHP — Documentation

Comprehensive documentation for `opencompanyapp/astronomy-bundle`, a PHP library for astronomical calculations based on Jean Meeus' *Astronomical Algorithms* and the VSOP87 theory.

## Table of Contents

- [Installation](#installation)
- [Core Concepts](#core-concepts)
  - [TimeOfInterest](#timeofinterest)
  - [Location](#location)
- [Sun](#sun)
- [Moon](#moon)
- [Planets](#planets)
- [Solar Eclipses](#solar-eclipses)
- [Lunar Eclipses](#lunar-eclipses)
- [Coordinate Systems](#coordinate-systems)
- [Utilities](#utilities)
  - [AngleUtil](#angleutil)
  - [TimeCalc](#timecalc)
- [API Reference](#api-reference)

---

## Installation

```console
composer require opencompanyapp/astronomy-bundle
```

Requires PHP `^7.2 || ^8.0` (tested through PHP 8.5).

## Core Concepts

### TimeOfInterest

The `TimeOfInterest` (TOI) object represents the point in time for all calculations. It supports dates beyond PHP's native DateTime range (including dates before year 1000).

```php
use OpenCompany\AstronomyBundle\TimeOfInterest;

// From a date string
$toi = TimeOfInterest::createFromString('2024-06-15 12:00:00');

// Current time
$toi = TimeOfInterest::createFromCurrentTime();

// From Julian Day
$toi = TimeOfInterest::createFromJulianDay(2460476.0);

// From a day-of-year
$toi = TimeOfInterest::createFromDayOfYear(2024, 167); // June 15
```

**Time properties:**

```php
$JD   = $toi->getJulianDay();                        // Julian Day number
$T    = $toi->getJulianCenturiesFromJ2000();          // Julian centuries since J2000.0
$t    = $toi->getJulianMillenniaFromJ2000();           // Julian millennia since J2000.0
$GMST = $toi->getGreenwichMeanSiderealTime();          // Greenwich Mean Sidereal Time (degrees)
$GAST = $toi->getGreenwichApparentSiderealTime();      // Greenwich Apparent Sidereal Time (degrees)
$E    = $toi->getEquationOfTime();                     // Equation of Time (degrees)
$dt   = $toi->getDateTime();                           // PHP DateTime object
```

**String output:**

```php
echo $toi; // "2024-06-15 12:00:00"
```

### Location

Represents a geographic position on Earth for observer-dependent calculations (rise/set times, horizontal coordinates, solar eclipses).

```php
use OpenCompany\AstronomyBundle\Location;

// Create with latitude, longitude (degrees), optional elevation (meters)
$berlin = Location::create(52.524, 13.411);
$denver = Location::create(39.739, -104.990, 1609); // with elevation

$berlin->getLatitude();   // 52.524
$berlin->getLongitude();  // 13.411
$berlin->getElevation();  // 0.0
```

---

## Sun

Calculate the Sun's position, rise/set times, and distance from Earth.

```php
use OpenCompany\AstronomyBundle\AstronomicalObjects\Sun;
use OpenCompany\AstronomyBundle\Location;
use OpenCompany\AstronomyBundle\TimeOfInterest;

$toi = TimeOfInterest::createFromString('2024-06-15 12:00:00');
$location = Location::create(52.524, 13.411); // Berlin

$sun = Sun::create($toi);
```

### Position

```php
// Geocentric ecliptical coordinates (apparent)
$eclCoords = $sun->getGeocentricEclipticalSphericalCoordinates();
$eclCoords->getLongitude();    // ecliptical longitude (degrees)
$eclCoords->getLatitude();     // ecliptical latitude (degrees)
$eclCoords->getRadiusVector(); // distance (AU)

// Geocentric equatorial coordinates
$eqCoords = $sun->getGeocentricEquatorialSphericalCoordinates();
$eqCoords->getRightAscension(); // right ascension (degrees)
$eqCoords->getDeclination();    // declination (degrees)

// Local horizontal coordinates (from observer's position)
$horCoords = $sun->getLocalHorizontalCoordinates($location);
$horCoords->getAzimuth();   // azimuth (degrees, with refraction)
$horCoords->getAltitude();  // altitude (degrees, with refraction)
```

### Rise, Set & Culmination

```php
$sunrise     = $sun->getSunrise($location);          // TimeOfInterest
$sunset      = $sun->getSunset($location);           // TimeOfInterest
$culmination = $sun->getUpperCulmination($location);  // TimeOfInterest

echo $sunrise->getDateTime()->format('H:i') . " UTC";
```

### Distance

```php
$distance = $sun->getDistanceToEarth(); // distance in km
```

### Twilight

Determine the current twilight phase at the observer's location:

```php
$twilight = $sun->getTwilight($location);

// Constants:
// Sun::TWILIGHT_DAY          — sun above horizon
// Sun::TWILIGHT_CIVIL        — sun 0° to -6°
// Sun::TWILIGHT_NAUTICAL     — sun -6° to -12°
// Sun::TWILIGHT_ASTRONOMICAL — sun -12° to -18°
// Sun::TWILIGHT_NIGHT        — sun below -18°
```

---

## Moon

Calculate the Moon's position, phase, illumination, rise/set times, and distance from Earth.

```php
use OpenCompany\AstronomyBundle\AstronomicalObjects\Moon;
use OpenCompany\AstronomyBundle\Location;

$location = Location::create(52.524, 13.411);
$moon = Moon::create(); // current time
```

### Position

```php
// Same coordinate methods as Sun
$eclCoords = $moon->getGeocentricEclipticalSphericalCoordinates();
$eqCoords  = $moon->getGeocentricEquatorialSphericalCoordinates();
$horCoords = $moon->getLocalHorizontalCoordinates($location);
```

### Phase & Illumination

```php
$moon->isWaxingMoon();                 // bool — true if waxing
$moon->getIlluminatedFraction();       // float 0.0–1.0
$moon->getPositionAngleOfMoonsBrightLimb(); // degrees
```

### Moonrise, Moonset & Transit

Returns `null` if the Moon doesn't rise/set at the given location and date (e.g., polar regions).

```php
$moonrise = $moon->getMoonrise($location);         // ?TimeOfInterest
$moonset  = $moon->getMoonset($location);          // ?TimeOfInterest
$transit  = $moon->getUpperCulmination($location);  // TimeOfInterest

if ($moonrise) {
    echo "Moonrise: " . $moonrise->getDateTime()->format('H:i') . " UTC";
}
```

### Distance

```php
$distance = $moon->getDistanceToEarth(); // distance in km
```

---

## Planets

All 7 planets (Mercury through Neptune) are supported with the same API as the Sun, plus heliocentric coordinates.

```php
use OpenCompany\AstronomyBundle\AstronomicalObjects\Planets\Mercury;
use OpenCompany\AstronomyBundle\AstronomicalObjects\Planets\Venus;
use OpenCompany\AstronomyBundle\AstronomicalObjects\Planets\Mars;
use OpenCompany\AstronomyBundle\AstronomicalObjects\Planets\Jupiter;
use OpenCompany\AstronomyBundle\AstronomicalObjects\Planets\Saturn;
use OpenCompany\AstronomyBundle\AstronomicalObjects\Planets\Uranus;
use OpenCompany\AstronomyBundle\AstronomicalObjects\Planets\Neptune;
use OpenCompany\AstronomyBundle\Location;
use OpenCompany\AstronomyBundle\TimeOfInterest;

$toi = TimeOfInterest::createFromString('2024-06-15 22:00:00');
$location = Location::create(52.524, 13.411);

$mars = Mars::create($toi);
```

### Position

```php
// Heliocentric ecliptical coordinates (planet's position relative to Sun)
$helCoords = $mars->getHeliocentricEclipticalSphericalCoordinates();
$helCoords->getLongitude();
$helCoords->getLatitude();

// Heliocentric rectangular coordinates
$helRect = $mars->getHeliocentricEclipticalRectangularCoordinates();
$helRect->getX();
$helRect->getY();
$helRect->getZ();

// Geocentric coordinates (planet's position as seen from Earth)
$geoEcl = $mars->getGeocentricEclipticalSphericalCoordinates();
$geoEq  = $mars->getGeocentricEquatorialSphericalCoordinates();

// Local horizontal coordinates
$horCoords = $mars->getLocalHorizontalCoordinates($location);
$horCoords->getAzimuth();
$horCoords->getAltitude();
```

### Rise, Set & Culmination

```php
$rise        = $mars->getRise($location);             // TimeOfInterest
$set         = $mars->getSet($location);              // TimeOfInterest
$culmination = $mars->getUpperCulmination($location);  // TimeOfInterest

echo "Mars rises: " . $rise->getDateTime()->format('H:i') . " UTC";
```

---

## Solar Eclipses

Calculate local circumstances of solar eclipses for a specific date and observer location. Solar eclipses are location-dependent — the type, obscuration, and contact times vary by observer position.

```php
use OpenCompany\AstronomyBundle\Events\SolarEclipse\SolarEclipse;
use OpenCompany\AstronomyBundle\Location;
use OpenCompany\AstronomyBundle\TimeOfInterest;

// Great American Eclipse, Madras, Oregon
$toi = TimeOfInterest::createFromString('2017-08-21');
$location = Location::create(44.61040, -121.23848);

$eclipse = SolarEclipse::create($toi, $location);
```

### Eclipse Properties

```php
$eclipse->getEclipseType();          // "total", "partial", "annular", or "none"
$eclipse->getObscuration();          // 0.0–1.0 (fraction of sun's area obscured)
$eclipse->getMagnitude();            // eclipse magnitude
$eclipse->getEclipseDuration();      // total eclipse duration in seconds
$eclipse->getEclipseUmbraDuration(); // totality/annularity duration in seconds
$eclipse->getMoonSunRatio();         // apparent size ratio of moon to sun
```

### Contact Times

Solar eclipse contact times represent key moments:
- **C1**: First contact (partial eclipse begins)
- **C2**: Second contact (totality/annularity begins)
- **MAX**: Maximum eclipse
- **C3**: Third contact (totality/annularity ends)
- **C4**: Fourth contact (partial eclipse ends)

```php
$c1  = $eclipse->getCircumstancesC1();
$c2  = $eclipse->getCircumstancesC2();
$max = $eclipse->getCircumstancesMax();
$c3  = $eclipse->getCircumstancesC3();
$c4  = $eclipse->getCircumstancesC4();

// Convert circumstances to TimeOfInterest
$toiMax = $eclipse->getTimeOfInterest($max);
echo "Maximum eclipse: " . $toiMax->getDateTime()->format('H:i:s') . " UTC";
```

### Greatest Eclipse Location

```php
$loc = $eclipse->getLocationOfGreatestEclipse();
echo "Greatest eclipse at: " . $loc->getLatitude() . "°, " . $loc->getLongitude() . "°";
```

### Data Coverage

Solar eclipse calculations use pre-computed Besselian element data files. The library includes data for eclipses from approximately 1500 to 2500 CE.

---

## Lunar Eclipses

Calculate circumstances of lunar eclipses. Unlike solar eclipses, lunar eclipses are **global events** — the type, magnitude, and timing are the same for all observers worldwide. No location parameter is needed.

The implementation uses the algorithmic approach from Meeus Chapter 54, computing eclipse circumstances directly from fundamental astronomical arguments.

```php
use OpenCompany\AstronomyBundle\Events\LunarEclipse\LunarEclipse;
use OpenCompany\AstronomyBundle\TimeOfInterest;

$toi = TimeOfInterest::createFromString('2019-01-21');
$eclipse = LunarEclipse::create($toi);
```

### Eclipse Type

```php
$eclipse->getEclipseType();
// Returns: "total", "partial", "penumbral", or "none"
```

- **Total**: Moon passes entirely through Earth's umbral shadow
- **Partial**: Moon partially enters the umbral shadow
- **Penumbral**: Moon passes through the penumbral shadow only
- **None**: No eclipse near the given date

### Magnitude & Gamma

```php
$eclipse->getUmbralMagnitude();     // umbral eclipse magnitude (> 1.0 for total)
$eclipse->getPenumbralMagnitude();  // penumbral eclipse magnitude
$eclipse->getGamma();              // closest approach of Moon to shadow axis
```

### Greatest Eclipse

```php
$toi = $eclipse->getGreatestEclipseTOI(); // ?TimeOfInterest (null if no eclipse)
echo "Greatest eclipse: " . $toi->getDateTime()->format('Y-m-d H:i') . " UTC";
```

### Semi-Durations

Semi-durations represent the time (in minutes) from greatest eclipse to each phase boundary:

```php
$eclipse->getSemiDurationPenumbral(); // float — penumbral phase semi-duration
$eclipse->getSemiDurationPartial();   // ?float — null for penumbral eclipses
$eclipse->getSemiDurationTotal();     // ?float — null for partial/penumbral eclipses
```

### Contact Times

Contact times mark key moments in the eclipse progression:

| Contact | Description | Availability |
|---------|-------------|-------------|
| **P1** | Penumbra first contact | All eclipses |
| **U1** | Umbra first contact | Partial & total |
| **U2** | Totality begins | Total only |
| **U3** | Totality ends | Total only |
| **U4** | Umbra last contact | Partial & total |
| **P4** | Penumbra last contact | All eclipses |

```php
$p1 = $eclipse->getContactP1();  // ?TimeOfInterest
$u1 = $eclipse->getContactU1();  // ?TimeOfInterest (null for penumbral)
$u2 = $eclipse->getContactU2();  // ?TimeOfInterest (null for partial/penumbral)
$u3 = $eclipse->getContactU3();  // ?TimeOfInterest (null for partial/penumbral)
$u4 = $eclipse->getContactU4();  // ?TimeOfInterest (null for penumbral)
$p4 = $eclipse->getContactP4();  // ?TimeOfInterest

// Contact times are always in chronological order: P1 < U1 < U2 < MAX < U3 < U4 < P4
```

### Example: Full Eclipse Report

```php
$toi = TimeOfInterest::createFromString('2019-01-21');
$eclipse = LunarEclipse::create($toi);

echo "Type: " . $eclipse->getEclipseType() . "\n";
echo "Magnitude: " . round($eclipse->getUmbralMagnitude(), 3) . "\n";
echo "Gamma: " . round($eclipse->getGamma(), 4) . "\n";
echo "Greatest eclipse: " . $eclipse->getGreatestEclipseTOI()->getDateTime()->format('H:i') . " UTC\n";
echo "Penumbral semi-duration: " . round($eclipse->getSemiDurationPenumbral()) . " min\n";
echo "Partial semi-duration: " . round($eclipse->getSemiDurationPartial()) . " min\n";
echo "Total semi-duration: " . round($eclipse->getSemiDurationTotal()) . " min\n";

// Output:
// Type: total
// Magnitude: 1.193
// Gamma: 0.3684
// Greatest eclipse: 05:12 UTC
// Penumbral semi-duration: 186 min
// Partial semi-duration: 111 min
// Total semi-duration: 31 min
```

---

## Coordinate Systems

The library supports 7 coordinate systems with full transformation between them. Each coordinate class can convert to any other coordinate system.

### Coordinate Types

| System | Class | Getters |
|--------|-------|---------|
| Geocentric Ecliptical Spherical | `GeocentricEclipticalSphericalCoordinates` | `getLongitude()`, `getLatitude()`, `getRadiusVector()` |
| Geocentric Equatorial Spherical | `GeocentricEquatorialSphericalCoordinates` | `getRightAscension()`, `getDeclination()`, `getRadiusVector()` |
| Geocentric Ecliptical Rectangular | `GeocentricEclipticalRectangularCoordinates` | `getX()`, `getY()`, `getZ()` |
| Geocentric Equatorial Rectangular | `GeocentricEquatorialRectangularCoordinates` | `getX()`, `getY()`, `getZ()` |
| Heliocentric Ecliptical Spherical | `HeliocentricEclipticalSphericalCoordinates` | `getLongitude()`, `getLatitude()`, `getRadiusVector()` |
| Heliocentric Ecliptical Rectangular | `HeliocentricEclipticalRectangularCoordinates` | `getX()`, `getY()`, `getZ()` |
| Heliocentric Equatorial Rectangular | `HeliocentricEquatorialRectangularCoordinates` | `getX()`, `getY()`, `getZ()` |
| Local Horizontal | `LocalHorizontalCoordinates` | `getAzimuth()`, `getAltitude()` |

All coordinate classes are in the `OpenCompany\AstronomyBundle\Coordinates` namespace.

### Transformations

Every coordinate object provides transformation methods to all other systems:

```php
$eclSph = $mars->getGeocentricEclipticalSphericalCoordinates();

// Transform to other systems (requires Julian centuries T for epoch-dependent transforms)
$T = $toi->getJulianCenturiesFromJ2000();

$eqSph  = $eclSph->getGeocentricEquatorialSphericalCoordinates($T);
$eqRect = $eclSph->getGeocentricEquatorialRectangularCoordinates($T);
$locHor = $eclSph->getLocalHorizontalCoordinates($location, $T);
```

### Atmospheric Refraction

Local horizontal coordinates include atmospheric refraction correction automatically. The apparent altitude is always slightly higher than the geometric altitude due to atmospheric bending of light.

---

## Utilities

### AngleUtil

Convert between decimal degrees, DMS notation, and time notation.

```php
use OpenCompany\AstronomyBundle\Utils\AngleUtil;

// Decimal degrees ↔ DMS string
AngleUtil::dec2angle(52.524);         // "52°31'26.4\""
AngleUtil::angle2dec("52°31'26.4\""); // 52.524

// Decimal degrees ↔ Time notation (for right ascension)
AngleUtil::dec2time(152.5);            // "10h10m0s"
AngleUtil::time2dec("10h10m0s");       // 152.5

// Normalize angle to 0–360 range
AngleUtil::normalizeAngle(400.0);     // 40.0
AngleUtil::normalizeAngle(-30.0);     // 330.0
```

### TimeCalc

Low-level time calculation utilities.

```php
use OpenCompany\AstronomyBundle\Calculations\TimeCalc;

// Delta T (TDT - UT) in seconds
$deltaT = TimeCalc::getDeltaT(2024, 6);

// Greenwich Apparent Sidereal Time
$GAST = TimeCalc::getGreenwichApparentSiderealTime($T);

// Greenwich Mean Sidereal Time
$GMST = TimeCalc::getGreenwichMeanSiderealTime($T);
```

---

## API Reference

### `TimeOfInterest`

| Method | Return | Description |
|--------|--------|-------------|
| `createFromString(string $dateString)` | `self` | Create from date string |
| `createFromCurrentTime()` | `self` | Create for current time |
| `createFromJulianDay(float $JD)` | `self` | Create from Julian Day |
| `createFromJulianCenturiesJ2000(float $T)` | `self` | Create from Julian centuries |
| `createFromDateTime(DateTime $dt)` | `self` | Create from PHP DateTime |
| `createFromDayOfYear(int $year, float $doy)` | `self` | Create from day of year |
| `getJulianDay()` | `float` | Julian Day number |
| `getJulianCenturiesFromJ2000()` | `float` | Julian centuries since J2000.0 |
| `getJulianMillenniaFromJ2000()` | `float` | Julian millennia since J2000.0 |
| `getGreenwichMeanSiderealTime()` | `float` | GMST in degrees |
| `getGreenwichApparentSiderealTime()` | `float` | GAST in degrees |
| `getEquationOfTime()` | `float` | Equation of time in degrees |
| `getDateTime()` | `DateTime` | PHP DateTime object |

### `Sun`

| Method | Return | Description |
|--------|--------|-------------|
| `create(?TimeOfInterest $toi = null)` | `self` | Static factory (defaults to now) |
| `getGeocentricEclipticalSphericalCoordinates()` | `GeocentricEclipticalSphericalCoordinates` | Apparent ecliptical position |
| `getGeocentricEquatorialSphericalCoordinates()` | `GeocentricEquatorialSphericalCoordinates` | Apparent equatorial position |
| `getLocalHorizontalCoordinates(Location $loc)` | `LocalHorizontalCoordinates` | Position from observer |
| `getSunrise(Location $loc)` | `TimeOfInterest` | Sunrise time |
| `getSunset(Location $loc)` | `TimeOfInterest` | Sunset time |
| `getUpperCulmination(Location $loc)` | `TimeOfInterest` | Solar noon |
| `getDistanceToEarth()` | `float` | Distance in km |
| `getTwilight(Location $loc)` | `int` | Twilight phase constant |

### `Moon`

| Method | Return | Description |
|--------|--------|-------------|
| `create(?TimeOfInterest $toi = null)` | `self` | Static factory (defaults to now) |
| `getGeocentricEclipticalSphericalCoordinates()` | `GeocentricEclipticalSphericalCoordinates` | Apparent ecliptical position |
| `getGeocentricEquatorialSphericalCoordinates()` | `GeocentricEquatorialSphericalCoordinates` | Apparent equatorial position |
| `getLocalHorizontalCoordinates(Location $loc)` | `LocalHorizontalCoordinates` | Position from observer |
| `getMoonrise(Location $loc)` | `?TimeOfInterest` | Moonrise time (null if none) |
| `getMoonset(Location $loc)` | `?TimeOfInterest` | Moonset time (null if none) |
| `getUpperCulmination(Location $loc)` | `TimeOfInterest` | Moon transit |
| `getDistanceToEarth()` | `float` | Distance in km |
| `isWaxingMoon()` | `bool` | Whether moon is waxing |
| `getIlluminatedFraction()` | `float` | 0.0–1.0 illumination |
| `getPositionAngleOfMoonsBrightLimb()` | `float` | Bright limb angle (degrees) |

### `Planet` (Mercury, Venus, Mars, Jupiter, Saturn, Uranus, Neptune)

| Method | Return | Description |
|--------|--------|-------------|
| `create(?TimeOfInterest $toi = null)` | `self` | Static factory (defaults to now) |
| `getHeliocentricEclipticalSphericalCoordinates()` | `HeliocentricEclipticalSphericalCoordinates` | Heliocentric position |
| `getHeliocentricEclipticalRectangularCoordinates()` | `HeliocentricEclipticalRectangularCoordinates` | Heliocentric rectangular |
| `getGeocentricEclipticalSphericalCoordinates()` | `GeocentricEclipticalSphericalCoordinates` | Geocentric ecliptical |
| `getGeocentricEquatorialSphericalCoordinates()` | `GeocentricEquatorialSphericalCoordinates` | Geocentric equatorial |
| `getLocalHorizontalCoordinates(Location $loc)` | `LocalHorizontalCoordinates` | Position from observer |
| `getRise(Location $loc)` | `TimeOfInterest` | Rise time |
| `getSet(Location $loc)` | `TimeOfInterest` | Set time |
| `getUpperCulmination(Location $loc)` | `TimeOfInterest` | Transit time |
| `getDistanceToEarthInAu()` | `float` | Distance in AU |
| `getDistanceToEarthInKm()` | `float` | Distance in km |

### `SolarEclipse`

| Method | Return | Description |
|--------|--------|-------------|
| `create(TimeOfInterest $toi, Location $loc)` | `self` | Static factory |
| `getEclipseType()` | `string` | "total", "partial", "annular", "none" |
| `getObscuration()` | `float` | 0.0–1.0 sun area obscured |
| `getMagnitude()` | `float` | Eclipse magnitude |
| `getEclipseDuration()` | `float` | Total duration (seconds) |
| `getEclipseUmbraDuration()` | `float` | Totality duration (seconds) |
| `getMoonSunRatio()` | `float` | Apparent size ratio |
| `getCircumstancesC1()` | `array` | First contact |
| `getCircumstancesC2()` | `array` | Second contact |
| `getCircumstancesMax()` | `array` | Maximum eclipse |
| `getCircumstancesC3()` | `array` | Third contact |
| `getCircumstancesC4()` | `array` | Fourth contact |
| `getTimeOfInterest(array $circ)` | `TimeOfInterest` | Convert circumstances to TOI |
| `getLocationOfGreatestEclipse()` | `Location` | Location of greatest eclipse |

### `LunarEclipse`

| Method | Return | Description |
|--------|--------|-------------|
| `create(TimeOfInterest $toi)` | `self` | Static factory |
| `getEclipseType()` | `string` | "total", "partial", "penumbral", "none" |
| `getUmbralMagnitude()` | `float` | Umbral magnitude |
| `getPenumbralMagnitude()` | `float` | Penumbral magnitude |
| `getGamma()` | `float` | Closest approach to shadow axis |
| `getGreatestEclipseTOI()` | `?TimeOfInterest` | Time of greatest eclipse |
| `getSemiDurationPenumbral()` | `float` | Penumbral semi-duration (min) |
| `getSemiDurationPartial()` | `?float` | Partial semi-duration (min) |
| `getSemiDurationTotal()` | `?float` | Total semi-duration (min) |
| `getContactP1()` | `?TimeOfInterest` | Penumbra first contact |
| `getContactU1()` | `?TimeOfInterest` | Umbra first contact |
| `getContactU2()` | `?TimeOfInterest` | Totality begins |
| `getContactU3()` | `?TimeOfInterest` | Totality ends |
| `getContactU4()` | `?TimeOfInterest` | Umbra last contact |
| `getContactP4()` | `?TimeOfInterest` | Penumbra last contact |

### `AngleUtil`

| Method | Return | Description |
|--------|--------|-------------|
| `angle2dec(string $angle)` | `float` | DMS string to decimal degrees |
| `dec2angle(float $dec)` | `string` | Decimal degrees to DMS string |
| `time2dec(string $time)` | `float` | Time notation to decimal degrees |
| `dec2time(float $angle)` | `string` | Decimal degrees to time notation |
| `normalizeAngle(float $angle, float $base = 360)` | `float` | Normalize to 0–base range |

---

## Accuracy

Calculations are based on established astronomical algorithms:

- **Sun/Moon/Planet positions**: VSOP87 theory and Meeus corrections, accurate to arcsecond precision for modern dates
- **Rise/set times**: Accurate to approximately 1–2 minutes, with atmospheric refraction correction
- **Solar eclipses**: Besselian elements method, accurate to seconds for contact times
- **Lunar eclipses**: Meeus Chapter 54 algorithm, magnitude accuracy within 0.003 of NASA reference values

## References

- Jean Meeus, *Astronomical Algorithms* (2nd Edition, 1998)
- VSOP87 — Bretagnon & Francou (1988)
- NASA Eclipse Catalog — eclipse.gsfc.nasa.gov

## License

MIT — see [LICENSE](LICENSE)
