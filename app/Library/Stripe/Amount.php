<?php

namespace App\Library\Stripe;

use App\Library\Stripe\Exceptions\UnsupportedCurrency;

class Amount
{
    public static $supportCurrencies = [
        'USD', 'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD',
        'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BIF', 'BMD', 'BND',
        'BOB', 'BRL', 'BSD', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHF',
        'CLP', 'CNY', 'COP', 'CRC', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP',
        'DZD', 'EGP', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GIP',
        'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HTG', 'HUF', 'IDR',
        'ILS', 'INR', 'ISK', 'JMD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF',
        'KRW', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'MAD',
        'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MUR', 'MVR', 'MWK',
        'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD',
        'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON',
        'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SEK', 'SGD', 'SHP',
        'SLE', 'SOS', 'SRD', 'STD', 'SZL', 'THB', 'TJS', 'TOP', 'TRY',
        'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'UYU', 'UZS', 'VND', 'VUV',
        'WST', 'XAF', 'XCD', 'XCG', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW',
    ];

    public static $eurSubCurrencies = [
        'AD', 'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FO', 'FI',
        'FR', 'DE', 'GI', 'GR', 'GL', 'GG', 'VA', 'HU', 'IS', 'IE', 'IM',
        'IT', 'JE', 'LV', 'LI', 'LT', 'LU', 'MT', 'MC', 'NL', 'NO', 'PL',
        'PT', 'RO', 'PM', 'SM', 'SK', 'SI', 'ES', 'SE', 'TR', 'GB',
    ];

    public static $specialDecimalCurrencies = [
        'BIF' => 0,
        'CLP' => 0,
        'DJF' => 0,
        'GNF' => 0,
        'JPY' => 0,
        'KMF' => 0,
        'KRW' => 0,
        'MGA' => 0,
        'PYG' => 0,
        'RWF' => 0,
        'UGX' => 0,
        'VND' => 0,
        'VUV' => 0,
        'XAF' => 0,
        'XOF' => 0,
        'XPF' => 0,
    ];

    public static $specialActualDecimal = [
        'ISK' => 0,
        'HUF' => 0,
        'TWD' => 0,
        'UGX' => 0,
    ];

    // if add more special currencies when it has special actual decimal or special decimal, please also add more test cases
    public static $specialCurrenciesMaximumDigits = [
        'IDR' => 9,
        'INR' => 9,
    ];

    private static function getCurrency()
    {
        $currency = strtoupper(config('stripe.currency', 'hkd'));
        if (in_array($currency, self::$eurSubCurrencies)) {
            $currency = 'EUR';
        }

        return $currency;
    }

    public static function getActualDecimal(): int
    {
        $currency = self::getCurrency();
        if (array_key_exists($currency, self::$specialDecimalCurrencies)) {
            return self::$specialDecimalCurrencies[$currency];
        }
        if (in_array($currency, self::$supportCurrencies)) {
            return 2;
        }

        throw new UnsupportedCurrency($currency);
    }

    public static function getValidationDecimal(): int
    {
        $currency = self::getCurrency();
        if (array_key_exists($currency, self::$specialActualDecimal)) {
            return self::$specialActualDecimal[$currency];
        }

        return self::getActualDecimal();
    }

    public static function getMaximumDigits(): int
    {
        $currency = self::getCurrency();
        if (array_key_exists($currency, self::$specialCurrenciesMaximumDigits)) {
            return self::$specialCurrenciesMaximumDigits[$currency];
        }
        if (in_array($currency, self::$supportCurrencies)) {
            return 8;
        }

        throw new UnsupportedCurrency($currency);
    }

    public static function getMaximumValidation(): int|float
    {
        $digits = self::getMaximumDigits();
        $digits -= self::getActualDecimal();
        $decimal = self::getValidationDecimal();
        if ($decimal > 0) {
            return (float) (str_repeat('9', $digits).'.'.str_repeat('9', $decimal));
        } else {
            return (int) str_repeat('9', $digits);
        }
    }
}
