<?php
declare(strict_types=1);

namespace PackageFactory\SymfonyExpressions\Helper;

use Neos\Flow\I18n\Formatter\DatetimeFormatter;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\Service as I18nService;

class DateHelper
{
    /**
     * @var DatetimeFormatter
     */
    protected static $datetimeFormatter;

    /**
     * @var I18nService
     */
    protected static $localizationService;

    /**
     * @return I18nService
     */
    protected static function getLocalizationService(): I18nService
    {
        if (self::$localizationService === null) {
            self::$localizationService = new I18nService();
        }
        return self::$localizationService;
    }

    /**
     * @return DatetimeFormatter
     */
    protected static function getDatetimeFormatter(): DatetimeFormatter
    {
        if (self::$datetimeFormatter === null) {
            self::$datetimeFormatter = new DatetimeFormatter();
        }
        return self::$datetimeFormatter;
    }

    /**
     * Parse a date from string with a format to a DateTime object
     *
     * @param string $string
     * @param string $format
     * @return \DateTime
     */
    public static function parse($string, $format)
    {
        return \DateTime::createFromFormat($format, $string);
    }

    /**
     * Format a date (or interval) to a string with a given format
     *
     * See formatting options as in PHP date()
     *
     * @param integer|string|\DateTime|\DateInterval $date
     * @param string $format
     * @return string
     */
    public static function format($date, $format)
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format($format);
        } elseif ($date instanceof \DateInterval) {
            return $date->format($format);
        } elseif ($date === 'now') {
            return date($format);
        } else {
            $timestamp = (integer)$date;
            return date($format, $timestamp);
        }
    }

    /**
     * Format a date to a string with a given cldr format
     *
     * @param integer|string|\DateTime $date
     * @param string $cldrFormat Format string in CLDR format (see http://cldr.unicode.org/translation/date-time)
     * @param null|string $locale String locale - example (de|en|ru_RU)
     * @return string
     */
    public static function formatCldr($date, $cldrFormat, $locale = null)
    {
        if ($date === 'now') {
            $date = new \DateTime();
        } elseif (is_int($date)) {
            $timestamp = $date;
            $date = new \DateTime();
            $date->setTimestamp($timestamp);
        } elseif (!$date instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException('The given date "' . $date . '" was neither an integer, "now" or a \DateTimeInterface instance.');
        }
        if (empty($cldrFormat)) {
            throw new \InvalidArgumentException('CLDR date formatting parameter not passed.');
        }
        if ($locale === null) {
            $useLocale = self::getLocalizationService()->getConfiguration()->getCurrentLocale();
        } else {
            $useLocale = new Locale($locale);
        }
        return self::getDatetimeFormatter()->formatDateTimeWithCustomPattern($date, $cldrFormat, $useLocale);
    }

    /**
     * Get a date object by given date or time format
     *
     * Examples::
     *
     *     Date.create('2018-12-04')
     *     Date.create('first day of next year')
     *
     * @param String $time A date/time string. For valid formats see http://php.net/manual/en/datetime.formats.php
     * @return \DateTime
     */
    public static function create(string $time): \DateTime
    {
        return new \DateTime($time);
    }

    /**
     * Get the current date and time
     *
     * Examples::
     *
     *     Date.now().timestamp
     *
     * @return \DateTime
     */
    public static function now()
    {
        return new \DateTime('now');
    }

    /**
     * Get the current date
     *
     * @return \DateTime
     */
    public static function today()
    {
        return new \DateTime('today');
    }

    /**
     * Add an interval to a date and return a new DateTime object
     *
     * @param \DateTime $date
     * @param string|\DateInterval $interval
     * @return \DateTime
     */
    public static function add($date, $interval)
    {
        if (!$interval instanceof \DateInterval) {
            $interval = new \DateInterval($interval);
        }
        $result = clone $date;
        return $result->add($interval);
    }

    /**
     * Subtract an interval from a date and return a new DateTime object
     *
     * @param \DateTime $date
     * @param string|\DateInterval $interval
     * @return \DateTime
     */
    public static function subtract($date, $interval)
    {
        if (!$interval instanceof \DateInterval) {
            $interval = new \DateInterval($interval);
        }
        $result = clone $date;
        return $result->sub($interval);
    }

    /**
     * Get the difference between two dates as a \DateInterval object
     *
     * @param \DateTime $dateA
     * @param \DateTime $dateB
     * @return \DateInterval
     */
    public static function diff($dateA, $dateB)
    {
        return $dateA->diff($dateB);
    }

    /**
     * Get the day of month of a date
     *
     * @param \DateTimeInterface $dateTime
     * @return integer The day of month of the given date
     */
    public static function dayOfMonth(\DateTimeInterface $dateTime)
    {
        return (integer)$dateTime->format('d');
    }

    /**
     * Get the month of a date
     *
     * @param \DateTimeInterface $dateTime
     * @return integer The month of the given date
     */
    public static function month(\DateTimeInterface $dateTime)
    {
        return (integer)$dateTime->format('m');
    }

    /**
     * Get the year of a date
     *
     * @param \DateTimeInterface $dateTime
     * @return integer The year of the given date
     */
    public static function year(\DateTimeInterface $dateTime)
    {
        return (integer)$dateTime->format('Y');
    }

    /**
     * Get the hour of a date (24 hour format)
     *
     * @param \DateTimeInterface $dateTime
     * @return integer The hour of the given date
     */
    public static function hour(\DateTimeInterface $dateTime)
    {
        return (integer)$dateTime->format('H');
    }

    /**
     * Get the minute of a date
     *
     * @param \DateTimeInterface $dateTime
     * @return integer The minute of the given date
     */
    public static function minute(\DateTimeInterface $dateTime)
    {
        return (integer)$dateTime->format('i');
    }

    /**
     * Get the second of a date
     *
     * @param \DateTimeInterface $dateTime
     * @return integer The second of the given date
     */
    public static function second(\DateTimeInterface $dateTime)
    {
        return (integer)$dateTime->format('s');
    }
}
