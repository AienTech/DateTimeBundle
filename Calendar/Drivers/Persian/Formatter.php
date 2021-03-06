<?php

/*
 * Copyright (C) 2014 Mohamad Mohebifar <mohebifar.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Mohebifar\DateTimeBundle\Calendar\Drivers\Persian;

use Mohebifar\DateTimeBundle\Calendar\TimeRepresentation;

/**
 * Description of Persian
 *
 * @author Mohamad Mohebifar <mohebifar.ir>
 */
class Formatter
{
    /**
     * Length of a year
     * Calculated by Khayam is 365.2422 days (approx.);
     * but as the years are getting shorter the new value
     * (valid from year 1380 Per./2000 A.D.) is used instead.
     *
     * @access protected
     * @var double
     */
    protected static $khayamYear = 365.24218956;

    /**
     * Count of days at the end of each Persian month
     *
     * @access protected
     * @var int
     */
    protected static $mountCounter = array(
        0, 31, 62, 93, 124, 155,
        186, 216, 246, 276, 306, 336
    );

    /**
     * Reference table made by Khayam for leap years
     *
     * @access protected
     * @var int
     */
    protected static $khayamLeapYears = array(
        0 => 5, 9, 13, 17, 21, 25, 29,
        34, 38, 42, 46, 50, 54, 58, 62,
        67, 71, 75, 79, 83, 87, 91, 95,
        100, 104, 108, 112, 116, 120, 124, 0
    );

    /**
     * Recent calculations has introduced a correcting factor,
     * which Khayam could not reach.
     * This is used to better adjust length of each year in seconds.
     *
     * @access protected
     * @var double
     */
    protected static $khayamYearCorrection = 0.00000006152;

    private static $words = array(
        "month" => array(
            "long" => array("فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"),
            "short" => array("فرو", "ارد", "خرد", "تیر", "مر", "شهر", "مهر", "آبا", "آذر", "دی", "بهم", "اسفـ")
        ),
        "week" => array(
            "long" => array("شنبه", "یکشنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنج‌شنبه", "جمعه"),
            "short" => array("ش", "ی", "د", "س", "چ", "پ", "ج")
        ),
        "number" => array(
            "صفرم", "یکم", "دوم", "سوم", "چهارم", "پنجم", "ششم", "هفتم", "هشتم", "نهم", "دهم",
            "یازدهم", "دوازدهم", "سیزدهم", "چهاردهم", "پانزدهم", "شانزدهم", "هفدهم", "هجدهم", "نوزدهم", "بیستم",
            "بیست و یکم", "بیست و دوم", "بیست و سوم", "بیست و چهارم", "بیست و پنجم", "بیست و ششم", "بیست و هفتم", "بیست و هشتم", "بیست و نهم",
            "سی‌ام", "سی و یکم"
        ),
        "AM" => "ق.ظ",
        "PM" => "ب.ظ"
    );

    public function __construct()
    {
        $this->representation = new TimeRepresentation();
    }

    public function setTimestamp($timestamp)
    {
        if($timestamp instanceof \DateTime) {
            $timestamp = $timestamp->getTimestamp();
        }
        $seconds = floor($timestamp % 60);
        $minutes = floor(( $timestamp % 3600 ) / 60);
        $hours = floor(( $timestamp % 86400 ) / 3600);
        $days = floor($timestamp / 86400) + 288;

        $years = floor(($days / self::$khayamYear) - ($days * self::$khayamYearCorrection));
        $dayOfYear = $days - round($years * self::$khayamYear, 0);

        if ($dayOfYear == 0) {
            $dayOfYear = 366;
        }

        $years += 1348;
        $months = 0;

        while ($months < 12 && $dayOfYear > self::$mountCounter[$months]) {
            $months++;
        }

        $days = $dayOfYear - self::$mountCounter[$months - 1];

        $this->representation->setSecond($seconds);
        $this->representation->setMinute($minutes);
        $this->representation->setHour($hours);
        $this->representation->setDay($days);
        $this->representation->setYear($years);
        $this->representation->setMonth($months);
        $this->representation->setDayOfYear($dayOfYear);

        return $this;
    }

    /**
     * <pre>
     * <b> Day </b>
     * d: Day of month 01 to 31
     * D: A textual representation of a day ش to ج
     * j: Day of the month without leading zeros 1 to 31
     * l: (lowercase 'L') A full textual representation of the day of the week
     * N: numeric representation of the day of the week 1 to 7
     * S: Persian Presentation for the day of the month : یکم to سی و یکم
     * w: Numeric representation of the day of the week 0 to 6
     * z: The day of the year (starting from 0)
     * <b> Week </b>
     * W: week number of year
     * <b> Month </b>
     * F: A full textual representation of a month فروردین to اسفند
     * m: Numeric representation of a month, with leading zeros 01 to 12
     * M: A short textual representation of a month, فرو to اسف
     * n: Numeric representation of a month, without leading zeros 1 to 12
     * <b> Year </b>
     * L: Whether it's a leap year, 1 if it is a leap year, 0 otherwise.
     * o: Year number. This has the same value as Y
     * Y: A full numeric representation of a year, 4 digits
     * y: A two digit representation of a year
     * <b> Time </b>
     * a | A: Ante meridiem and Post meridiem
     * g: 12-hour format of an hour without leading zeros 1 to 12
     * G: 24-hour format of an hour without leading zeros 1 to 23
     * h: 12-hour format of an hour with leading zeros 01 to 12
     * H: 24-hour format of an hour with leading zeros 01 to 23
     * i: Minutes with leading zeros
     * s: Seconds with leading zeros
     * </pre>
     * @param string $format
     * @return string
     */
    public function format($format)
    {
        // Day
        $format = str_replace("d", str_pad($this->representation->getDay(), 2, '0', STR_PAD_LEFT), $format);
        $format = str_replace("D", $this->getDayInWeekName("short"), $format);
        $format = str_replace("jS", $this->getDayNumberWord(), $format); // Temporary top of j
        $format = str_replace("j", $this->representation->getDay(), $format);
        $format = str_replace("S", 'ام', $format);
        $format = str_replace("l", $this->getDayInWeekName("long"), $format);
        $format = str_replace("N", $this->dayOfWeek() + 1, $format);
        $format = str_replace("w", $this->dayOfWeek(), $format);
        $format = str_replace("z", $this->representation->getDayOfYear() - 1, $format);

        // Week
        $format = str_replace("W", "nth week of year", $format);

        // Month
        $format = str_replace("F", $this->getMonthName("long"), $format);
        $format = str_replace("m", str_pad($this->representation->getMonth(), 2, '0', STR_PAD_LEFT), $format);
        $format = str_replace("M", $this->getMonthName("short"), $format);
        $format = str_replace("n", $this->representation->getMonth(), $format);
        $format = str_replace("t", "number of days in given month", $format);  //TODO: fix it

        // Year
        $format = str_replace("L", (int) $this->isLeap(), $format);
        $format = str_replace("o", $this->representation->getYear(), $format);
        $format = str_replace("Y", $this->representation->getYear(), $format);
        $format = str_replace("y", $this->representation->getYear() % 100, $format);

        // Time
        $format = str_replace(array('a', 'A'), $this->representation->getHour() < 12 ? self::$words['AM'] : self::$words['PM'], $format);
        $format = str_replace("B", 000, $format);
        $format = str_replace("g", $this->representation->getHour() % 12, $format);
        $format = str_replace("G", $this->representation->getHour(), $format);
        $format = str_replace("h", str_pad($this->representation->getHour() % 12, 2, '0', STR_PAD_LEFT), $format);
        $format = str_replace("H", str_pad($this->representation->getHour(), 2, '0', STR_PAD_LEFT), $format);
        $format = str_replace("i", str_pad($this->representation->getMinute(), 2, '0', STR_PAD_LEFT), $format);
        $format = str_replace("s", str_pad($this->representation->getSecond(), 2, '0', STR_PAD_LEFT), $format);
        $format = str_replace("u", 0, $format);

        return $format;
    }

    private function getWeekOfYear()
    {
        $x = (7 - $this->dayOfWeek($this->representation->getYear(), 1) ) % 7;
        $z = $this->representation->getDayOfYear() - $x;
        return abs(ceil($z / 7));
    }

    private function dayOfWeek()
    {
        $dayOfYear = $this->representation->getDayOfYear();
        $yearValue = $this->representation->getYear();

        $rasad = $yearValue + 2346;

        $count2820 = floor($rasad / 2820);
        $mod2820 = $rasad % 2820;
        $count128 = floor($mod2820 / 128);
        $mod128 = $mod2820 % 128;

        $leapCount = 0;
        while (isset(self::$khayamLeapYears[$leapCount]) && $mod128 > self::$khayamLeapYears[$leapCount]) {
            $leapCount++;
        }

        $yearStartDay = ( $count2820 + 1 ) * 3 + $count128 * 5 + $mod128 + $leapCount;
        if ($dayOfYear > 0) {
            $dayOfYear--;
        }

        return ($yearStartDay + $dayOfYear) % 7;
    }

    private function getDayInWeekName($type = "long") {
        $day = $this->dayOfWeek();
        return self::$words['week'][$type][$day];
    }

    private function getMonthName($type = "long") {
        $month = $this->representation->getMonth();
        return self::$words['month'][$type][$month -1];
    }

    private function getDayNumberWord() {
        $day = $this->representation->getDay();
        return self::$words['number'][$day];
    }

    private function isLeap() {
        $rasad = $this->representation->getYear() + 2346;
        $yrNam = ($rasad % 2820) % 128;
        return (boolean) array_search($yrNam, self::$khayamLeapYears);
    }
}
