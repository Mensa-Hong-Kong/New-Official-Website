<?php

namespace Tests\Feature\Library\Stripe;

use App\Library\Stripe\Amount;
use Tests\TestCase;

class AmountTest extends TestCase
{
    public function test_get_actual_decimal_when_currency_is_special_decimal()
    {
        foreach (Amount::$specialDecimalCurrencies as $currency => $decimal) {
            config(['stripe.currency' => $currency]);
            $this->assertEquals($decimal, Amount::getActualDecimal());
        }
    }

    public function test_get_actual_decimal_when_currency_is_normal_currency()
    {
        $normalCurrencies = array_diff([...Amount::$supportCurrencies, ...Amount::$eurSubCurrencies], array_keys(Amount::$specialDecimalCurrencies));
        foreach ($normalCurrencies as $currency) {
            config(['stripe.currency' => $currency]);
            $this->assertEquals(2, Amount::getActualDecimal());
        }
    }

    public function test_get_actual_decimal_when_currency_is_unsupported_currency()
    {
        $this->expectException(\App\Library\Stripe\Exceptions\UnsupportedCurrency::class);
        config(['stripe.currency' => 123]);
        Amount::getActualDecimal();
    }

    public function test_get_validation_decimal_when_currency_is_special_actual_decimal()
    {
        foreach (Amount::$specialActualDecimal as $currency => $decimal) {
            config(['stripe.currency' => $currency]);
            $this->assertEquals($decimal, Amount::getValidationDecimal());
        }
    }

    public function test_get_validation_decimal_when_currency_is_special_decimal_but_not_special_actual_decimal()
    {
        $currencies = array_diff(array_keys(Amount::$specialDecimalCurrencies), array_keys(Amount::$specialActualDecimal));
        foreach ($currencies as $currency) {
            config(['stripe.currency' => $currency]);
            $this->assertEquals(Amount::getActualDecimal(), Amount::getValidationDecimal());
        }
    }

    public function test_get_validation_decimal_when_currency_is_normal_currency()
    {
        $normalCurrencies = array_diff([...Amount::$supportCurrencies, ...Amount::$eurSubCurrencies], array_keys(Amount::$specialDecimalCurrencies), array_keys(Amount::$specialActualDecimal));
        foreach ($normalCurrencies as $currency) {
            config(['stripe.currency' => $currency]);
            $this->assertEquals(Amount::getActualDecimal(), Amount::getValidationDecimal());
        }
    }

    public function test_get_validation_decimal_when_currency_is_unsupported_currency()
    {
        $this->expectException(\App\Library\Stripe\Exceptions\UnsupportedCurrency::class);
        config(['stripe.currency' => 123]);
        Amount::getValidationDecimal();
    }

    public function test_get_maximum_digits_when_currency_is_special_maximum_digits()
    {
        foreach (Amount::$specialCurrenciesMaximumDigits as $currency => $digits) {
            config(['stripe.currency' => $currency]);
            $this->assertEquals($digits, Amount::getMaximumDigits());
        }
    }

    public function test_get_maximum_digits_when_currency_is_normal_currency()
    {
        $normalCurrencies = array_diff([...Amount::$supportCurrencies, ...Amount::$eurSubCurrencies], array_keys(Amount::$specialCurrenciesMaximumDigits));
        foreach ($normalCurrencies as $currency) {
            config(['stripe.currency' => $currency]);
            $this->assertEquals(8, Amount::getMaximumDigits());
        }
    }

    public function test_get_maximum_digits_when_currency_is_unsupported_currency()
    {
        $this->expectException(\App\Library\Stripe\Exceptions\UnsupportedCurrency::class);
        config(['stripe.currency' => 123]);
        Amount::getMaximumDigits();
    }

    public function test_get_maximum_validation_when_currency_is_special_maximum_digits_currency()
    {
        config(['stripe.currency' => 'idr']);
        $this->assertEquals(9999999.99, Amount::getMaximumValidation());
    }

    public function test_get_maximum_validation_when_currency_is_normal_digits_and_special_actual_decimal()
    {
        config(['stripe.currency' => 'twd']);
        $this->assertEquals(999999, Amount::getMaximumValidation());
    }

    public function test_get_maximum_validation_when_currency_is_normal_digits_and_special_decimal()
    {
        config(['stripe.currency' => 'xpf']);
        $this->assertEquals(99999999, Amount::getMaximumValidation());
    }

    public function test_get_maximum_validation_when_currency_is_normal_currency()
    {
        config(['stripe.currency' => 'hkd']);
        $this->assertEquals(999999.99, Amount::getMaximumValidation());
    }

    public function test_get_maximum_validation_when_currency_is_unsupported_currency()
    {
        $this->expectException(\App\Library\Stripe\Exceptions\UnsupportedCurrency::class);
        config(['stripe.currency' => 123]);
        Amount::getMaximumValidation();
    }
}