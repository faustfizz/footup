<?php

namespace Footup\Utils;

/**
 * MoneyValue - Immutable money value object
 * 
 * Encapsulates an amount and currency together for type-safe operations
 * 
 * @package Footup
 */
class MoneyValue
{
    /**
     * Create a new MoneyValue
     * 
     * @param float|int $amount The amount
     * @param string $currency Currency code
     */
    public function __construct(
        private float|int $amount,
        private string $currency
    ) {
    }

    /**
     * Get the amount
     */
    public function amount(): float|int
    {
        return $this->amount;
    }

    /**
     * Get the currency
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Convert to string (for debugging)
     */
    public function __toString(): string
    {
        return Money::format($this->amount, $this->currency);
    }
}

/**
 * Money utility class for formatting and converting currencies
 * 
 * Provides methods for:
 * - Formatting money with currency symbols
 * - Currency conversion
 * - Money arithmetic (add, subtract, multiply, divide)
 * - Locale-aware formatting
 * - Multiple currency support
 * 
 * @package Footup
 */
class Money
{
    /**
     * Supported currencies with symbols and decimal places
     * 
     * @var array
     */
    private static array $currencies = [
        'USD' => ['symbol' => '$', 'decimals' => 2, 'position' => 'before'],
        'EUR' => ['symbol' => '€', 'decimals' => 2, 'position' => 'before'],
        'GBP' => ['symbol' => '£', 'decimals' => 2, 'position' => 'before'],
        'JPY' => ['symbol' => '¥', 'decimals' => 0, 'position' => 'before'],
        'CAD' => ['symbol' => 'C$', 'decimals' => 2, 'position' => 'before'],
        'AUD' => ['symbol' => 'A$', 'decimals' => 2, 'position' => 'before'],
        'CHF' => ['symbol' => 'CHF', 'decimals' => 2, 'position' => 'before'],
        'CNY' => ['symbol' => '¥', 'decimals' => 2, 'position' => 'before'],
        'INR' => ['symbol' => '₹', 'decimals' => 2, 'position' => 'before'],
        'MXN' => ['symbol' => '$', 'decimals' => 2, 'position' => 'before'],
        'BRL' => ['symbol' => 'R$', 'decimals' => 2, 'position' => 'before'],
        'ZAR' => ['symbol' => 'R', 'decimals' => 2, 'position' => 'before'],
    ];

    /**
     * Currency exchange rates (base: USD)
     * 
     * @var array
     */
    private static array $exchangeRates = [
        'USD' => 1.00,
        'EUR' => 0.92,
        'GBP' => 0.79,
        'JPY' => 149.50,
        'CAD' => 1.36,
        'AUD' => 1.53,
        'CHF' => 0.88,
        'CNY' => 7.24,
        'INR' => 83.12,
        'MXN' => 17.05,
        'BRL' => 4.97,
        'ZAR' => 18.50,
    ];

    /**
     * Create a MoneyValue object
     * 
     * MoneyValue encapsulates amount + currency together for type-safe operations
     * 
     * @param float|int $amount The amount
     * @param string $currency Currency code
     * @return MoneyValue A new MoneyValue object
     * 
     * @example
     * $m = Money::value(100, 'USD');
     * echo $m->amount();      // 100
     * echo $m->currency();    // "USD"
     * echo $m;                // "$100.00"
     */
    public static function value(float|int $amount, string $currency): MoneyValue
    {
        return new MoneyValue($amount, $currency);
    }

    /**
     * Format money with currency symbol
     * 
     * @param float|int $amount The amount
     * @param string $currency Currency code (e.g., 'USD', 'EUR')
     * @param string $separator Thousand separator
     * @param string $decimal Decimal separator
     * @return string Formatted money string
     * 
     * @example
     * Money::format(1234.56, 'USD')   // "$1,234.56"
     * Money::format(1234.56, 'EUR')   // "€1,234.56"
     * Money::format(1234567, 'JPY')   // "¥1,234,567"
     */
    public static function format(
        float|int $amount,
        string $currency = 'USD',
        string $separator = ',',
        string $decimal = '.'
    ): string {
        if (!isset(self::$currencies[$currency])) {
            return (string)$amount;
        }

        $config = self::$currencies[$currency];
        $decimals = $config['decimals'];
        $symbol = $config['symbol'];

        // Format the number
        $formatted = number_format((float)$amount, $decimals, $decimal, $separator);

        // Add symbol
        if ($config['position'] === 'after') {
            return $formatted . ' ' . $symbol;
        }

        return $symbol . $formatted;
    }

    /**
     * Format money without currency symbol (just number)
     * 
     * @param float|int $amount The amount
     * @param string $currency Currency code
     * @param string $separator Thousand separator
     * @param string $decimal Decimal separator
     * @return string Formatted number
     * 
     * @example
     * Money::formatNumber(1234.56, 'USD')  // "1,234.56"
     * Money::formatNumber(1234.56, 'JPY')  // "1,234"
     */
    public static function formatNumber(
        float|int $amount,
        string $currency = 'USD',
        string $separator = ',',
        string $decimal = '.'
    ): string {
        $decimals = self::$currencies[$currency]['decimals'] ?? 2;
        return number_format((float)$amount, $decimals, $decimal, $separator);
    }

    /**
     * Convert amount from one currency to another
     * 
     * @param float|int $amount Amount to convert
     * @param string $from Source currency
     * @param string $to Target currency
     * @return float Converted amount
     * 
     * @example
     * Money::convert(100, 'USD', 'EUR')  // ~92
     * Money::convert(100, 'EUR', 'USD')  // ~108.7
     */
    public static function convert(
        float|int $amount,
        string $from = 'USD',
        string $to = 'USD'
    ): float {
        if ($from === $to) {
            return (float)$amount;
        }

        if (!isset(self::$exchangeRates[$from]) || !isset(self::$exchangeRates[$to])) {
            return (float)$amount;
        }

        // Convert to USD first, then to target currency
        $usd = (float)$amount / self::$exchangeRates[$from];
        return $usd * self::$exchangeRates[$to];
    }

    /**
     * Convert and format amount in one call
     * 
     * @param float|int $amount Amount to convert
     * @param string $from Source currency
     * @param string $to Target currency
     * @param string $separator Thousand separator
     * @param string $decimal Decimal separator
     * @return string Formatted and converted money
     * 
     * @example
     * Money::convertFormat(100, 'USD', 'EUR')  // "€92.00"
     */
    public static function convertFormat(
        float|int $amount,
        string $from = 'USD',
        string $to = 'USD',
        string $separator = ',',
        string $decimal = '.'
    ): string {
        $converted = self::convert($amount, $from, $to);
        return self::format($converted, $to, $separator, $decimal);
    }

    /**
     * Add two amounts in different currencies
     * 
     * TWO clean syntaxes:
     * 1. MoneyValue + MoneyValue (type-safe)
     * 2. Same or different currencies (explicit)
     * 
     * Automatically converts the second amount to the first currency, then adds.
     * 
     * @param MoneyValue|float|int $amount1 First MoneyValue or amount
     * @param MoneyValue|string $currency1 Second MoneyValue or first currency
     * @param float|int $amount2 Second amount (if numeric form)
     * @param string $currency2 Second currency (if numeric form)
     * @return float Sum in first currency
     * 
     * @example
     * // Syntax 1: MoneyValue + MoneyValue (type-safe)
     * Money::add(Money::value(100, 'USD'), Money::value(50, 'EUR'))
     * 
     * // Syntax 2: Numeric (explicit currencies)
     * Money::add(100, 'USD', 50, 'EUR')  // Different currencies
     * Money::add(100, 'USD', 50, 'USD')  // Same currency
     */
    public static function add(
        MoneyValue|float|int $amount1,
        MoneyValue|string $currency1,
        float|int $amount2 = null,
        string $currency2 = null
    ): float {
        // Syntax 1: MoneyValue + MoneyValue
        if ($amount1 instanceof MoneyValue && $currency1 instanceof MoneyValue) {
            $amt1 = $amount1->amount();
            $cur1 = $amount1->currency();
            $amt2 = $currency1->amount();
            $cur2 = $currency1->currency();
        }
        // Syntax 2: Numeric amounts with currencies
        else {
            $amt1 = (float)$amount1;
            $cur1 = (string)$currency1;
            $amt2 = (float)$amount2;
            $cur2 = (string)$currency2;
        }

        // Convert second amount to first currency
        $converted = self::convert($amt2, $cur2, $cur1);
        return $amt1 + $converted;
    }

    /**
     * Subtract two amounts in different currencies
     * 
     * TWO clean syntaxes:
     * 1. MoneyValue - MoneyValue (type-safe)
     * 2. Same or different currencies (explicit)
     * 
     * Automatically converts the second amount to the first currency, then subtracts.
     * 
     * @param MoneyValue|float|int $amount1 First MoneyValue or amount
     * @param MoneyValue|string $currency1 Second MoneyValue or first currency
     * @param float|int $amount2 Second amount (if numeric form)
     * @param string $currency2 Second currency (if numeric form)
     * @return float Difference in first currency
     * 
     * @example
     * // Syntax 1: MoneyValue - MoneyValue (type-safe)
     * Money::subtract(Money::value(100, 'USD'), Money::value(50, 'EUR'))
     * 
     * // Syntax 2: Numeric (explicit currencies)
     * Money::subtract(100, 'USD', 50, 'EUR')  // Different currencies
     * Money::subtract(100, 'USD', 50, 'USD')  // Same currency
     */
    public static function subtract(
        MoneyValue|float|int $amount1,
        MoneyValue|string $currency1,
        float|int $amount2 = null,
        string $currency2 = null
    ): float {
        // Syntax 1: MoneyValue - MoneyValue
        if ($amount1 instanceof MoneyValue && $currency1 instanceof MoneyValue) {
            $amt1 = $amount1->amount();
            $cur1 = $amount1->currency();
            $amt2 = $currency1->amount();
            $cur2 = $currency1->currency();
        }
        // Syntax 2: Numeric amounts with currencies
        else {
            $amt1 = (float)$amount1;
            $cur1 = (string)$currency1;
            $amt2 = (float)$amount2;
            $cur2 = (string)$currency2;
        }

        // Convert second amount to first currency
        $converted = self::convert($amt2, $cur2, $cur1);
        return $amt1 - $converted;
    }

    /**
     * Multiply amount (stays in same currency)
     * 
     * @param float|int $amount The amount
     * @param string $currency Currency code
     * @param float|int $multiplier Multiplier value
     * @return float Result in same currency
     * 
     * @example
     * Money::multiply(100, 'USD', 1.5)  // 150 USD
     */
    public static function multiply(
        float|int $amount,
        string $currency,
        float|int $multiplier
    ): float {
        return (float)$amount * (float)$multiplier;
    }

    /**
     * Divide amount (stays in same currency)
     * 
     * @param float|int $amount The amount
     * @param string $currency Currency code
     * @param float|int $divisor Divisor
     * @return float Result in same currency
     * 
     * @example
     * Money::divide(100, 'USD', 2)  // 50 USD
     */
    public static function divide(
        float|int $amount,
        string $currency,
        float|int $divisor
    ): float {
        if ((float)$divisor === 0.0) {
            return 0;
        }

        return (float)$amount / (float)$divisor;
    }

    /**
     * Calculate percentage of amount (stays in same currency)
     * 
     * @param float|int $amount The amount
     * @param string $currency Currency code
     * @param float|int $percent The percentage
     * @return float Percentage amount in same currency
     * 
     * @example
     * Money::percent(200, 'USD', 15)  // 30 USD (15% of 200 USD)
     */
    public static function percent(
        float|int $amount,
        string $currency,
        float|int $percent
    ): float {
        return ((float)$amount / 100) * (float)$percent;
    }

    /**
     * Add percentage increase to amount (stays in same currency)
     * 
     * @param float|int $amount The amount
     * @param string $currency Currency code
     * @param float|int $percent Percentage to increase
     * @return float Amount after increase in same currency
     * 
     * @example
     * Money::addPercent(100, 'USD', 10)  // 110 USD
     */
    public static function addPercent(
        float|int $amount,
        string $currency,
        float|int $percent
    ): float {
        return (float)$amount * (1 + (float)$percent / 100);
    }

    /**
     * Subtract percentage from amount (stays in same currency)
     * 
     * @param float|int $amount The amount
     * @param string $currency Currency code
     * @param float|int $percent Percentage to decrease
     * @return float Amount after decrease in same currency
     * 
     * @example
     * Money::subtractPercent(100, 'USD', 10)  // 90 USD
     */
    public static function subtractPercent(
        float|int $amount,
        string $currency,
        float|int $percent
    ): float {
        return (float)$amount * (1 - (float)$percent / 100);
    }

    /**
     * Get currency symbol
     * 
     * @param string $currency Currency code
     * @return string Currency symbol
     * 
     * @example
     * Money::symbol('USD')  // "$"
     * Money::symbol('EUR')  // "€"
     */
    public static function symbol(string $currency): string
    {
        return self::$currencies[$currency]['symbol'] ?? $currency;
    }

    /**
     * Get decimal places for currency
     * 
     * @param string $currency Currency code
     * @return int Number of decimal places
     * 
     * @example
     * Money::decimals('USD')  // 2
     * Money::decimals('JPY')  // 0
     */
    public static function decimals(string $currency): int
    {
        return self::$currencies[$currency]['decimals'] ?? 2;
    }

    /**
     * Get exchange rate for currency
     * 
     * @param string $currency Currency code
     * @return float Exchange rate (base: USD = 1.0)
     * 
     * @example
     * Money::rate('USD')  // 1.0
     * Money::rate('EUR')  // 0.92
     */
    public static function rate(string $currency): float
    {
        return self::$exchangeRates[$currency] ?? 1.0;
    }

    /**
     * Set exchange rate for currency
     * 
     * @param string $currency Currency code
     * @param float $rate Exchange rate (base: USD)
     * @return void
     * 
     * @example
     * Money::setRate('EUR', 0.95);
     */
    public static function setRate(string $currency, float $rate): void
    {
        self::$exchangeRates[$currency] = $rate;
    }

    /**
     * Add custom currency
     * 
     * @param string $code Currency code
     * @param string $symbol Currency symbol
     * @param int $decimals Decimal places
     * @param string $position Symbol position ('before' or 'after')
     * @param float $rate Exchange rate (base: USD)
     * @return void
     * 
     * @example
     * Money::addCurrency('BTC', '₿', 8, 'before', 42500);
     */
    public static function addCurrency(
        string $code,
        string $symbol,
        int $decimals = 2,
        string $position = 'before',
        float $rate = 1.0
    ): void {
        self::$currencies[$code] = [
            'symbol' => $symbol,
            'decimals' => $decimals,
            'position' => $position,
        ];
        self::$exchangeRates[$code] = $rate;
    }

    /**
     * List all supported currencies
     * 
     * @return array Array of currency codes
     * 
     * @example
     * Money::currencies()  // ['USD', 'EUR', 'GBP', ...]
     */
    public static function currencies(): array
    {
        return array_keys(self::$currencies);
    }

    /**
     * Check if currency is supported
     * 
     * @param string $currency Currency code
     * @return bool True if supported
     * 
     * @example
     * Money::hasCurrency('USD')  // true
     * Money::hasCurrency('BTC')  // false (unless added)
     */
    public static function hasCurrency(string $currency): bool
    {
        return isset(self::$currencies[$currency]);
    }

    /**
     * Parse formatted money string to amount
     * 
     * @param string $money Formatted money string
     * @param string $currency Currency code (to detect decimals)
     * @param string $separator Thousand separator
     * @param string $decimal Decimal separator
     * @return float Parsed amount
     * 
     * @example
     * Money::parse("$1,234.56", "USD")  // 1234.56
     * Money::parse("€1.234,56", "EUR", ",", ".")  // 1234.56
     */
    public static function parse(
        string $money,
        string $currency = 'USD',
        string $separator = ',',
        string $decimal = '.'
    ): float {
        // Remove currency symbol
        $symbol = self::symbol($currency);
        $money = str_replace($symbol, '', $money);
        $money = trim($money);

        // Remove thousands separator
        $money = str_replace($separator, '', $money);

        // Replace decimal separator with dot for PHP parsing
        $money = str_replace($decimal, '.', $money);

        return (float)$money;
    }

    /**
     * Format money with custom pattern
     * 
     * Patterns:
     * {amount} - The formatted amount
     * {symbol} - Currency symbol
     * {code} - Currency code
     * 
     * @param float|int $amount The amount
     * @param string $currency Currency code
     * @param string $pattern Format pattern
     * @return string Formatted string
     * 
     * @example
     * Money::formatPattern(100, 'USD', '{amount} {code}')  // "100.00 USD"
     * Money::formatPattern(100, 'USD', '{symbol}{amount}')  // "$100.00"
     */
    public static function formatPattern(
        float|int $amount,
        string $currency,
        string $pattern
    ): string {
        $formatted = self::formatNumber($amount, $currency);
        $symbol = self::symbol($currency);

        return strtr($pattern, [
            '{amount}' => $formatted,
            '{symbol}' => $symbol,
            '{code}' => $currency,
        ]);
    }

    // ========================================================================
    // PAYMENT PROVIDER FEES
    // ========================================================================

    /**
     * Registered payment providers (static storage)
     */
    private static array $providers = [];

    /**
     * Register a payment provider with its fees
     * 
     * @param string $name Provider name (e.g., 'paystack', 'stripe', 'paypal')
     * @param float $percentFee Percentage fee (1.5 = 1.5%)
     * @param float $flatFee Flat fee amount
     * @param string $currency Currency for flat fee
     * @return void
     * 
     * @example
     * Money::registerProvider('paystack', 1.5, 0.30)
     * Money::registerProvider('stripe', 2.9, 0.30)
     * Money::registerProvider('paypal', 2.9, 0.30)
     */
    public static function registerProvider(
        string $name,
        float $percentFee = 0,
        float $flatFee = 0,
        string $currency = 'USD'
    ): void {
        self::$providers[strtolower($name)] = new PaymentProvider(
            $name,
            $percentFee,
            $flatFee,
            $currency
        );
    }

    /**
     * Get a registered provider
     * 
     * @param string $name Provider name
     * @return PaymentProvider|null
     */
    public static function getProvider(string $name): ?PaymentProvider
    {
        return self::$providers[strtolower($name)] ?? null;
    }

    /**
     * Check if provider is registered
     * 
     * @param string $name Provider name
     * @return bool
     */
    public static function hasProvider(string $name): bool
    {
        return isset(self::$providers[strtolower($name)]);
    }

    /**
     * List all registered providers
     * 
     * @return array
     */
    public static function listProviders(): array
    {
        return array_keys(self::$providers);
    }

    /**
     * Calculate payment gateway fee with optional platform fee
     * 
     * Fee = (amount * providerPercentFee / 100) + providerFlatFee
     *      + (amount * platformPercentFee / 100) + platformFlatFee
     * 
     * @param float|int $amount Transaction amount
     * @param string $provider Provider name (must be registered)
     * @param float $platformPercentFee Optional platform percentage fee
     * @param float $platformFlatFee Optional platform flat fee
     * @param string $currency Currency (for validation)
     * @return array Complete fee breakdown
     * 
     * @throws \InvalidArgumentException if provider not found
     * 
     * @example
     * // Paystack only
     * Money::calculateFee(100, 'paystack')
     * // [
     * //   'amount' => 100,
     * //   'provider' => 'paystack',
     * //   'provider_percent_fee' => 1.5,
     * //   'provider_flat_fee' => 0.30,
     * //   'provider_total_fee' => 1.8,
     * //   'platform_percent_fee' => 0,
     * //   'platform_flat_fee' => 0,
     * //   'platform_total_fee' => 0,
     * //   'total_fee' => 1.8,
     * //   'total_with_fee' => 101.8,
     * //   'currency' => 'USD'
     * // ]
     * 
     * // Paystack + 2.5% platform fee
     * Money::calculateFee(100, 'paystack', 2.5)
     * 
     * // Paystack + 2.5% + $5 flat platform fee
     * Money::calculateFee(100, 'paystack', 2.5, 5)
     */
    public static function calculateFee(
        float|int $amount,
        string $provider,
        float $platformPercentFee = 0,
        float $platformFlatFee = 0,
        string $currency = 'USD'
    ): array {
        $providerObj = self::getProvider($provider);
        if (!$providerObj) {
            throw new \InvalidArgumentException(
                "Payment provider '{$provider}' not registered. "
                . "Use Money::registerProvider() first."
            );
        }

        $amount = (float)$amount;
        $platformPercentFee = max(0, $platformPercentFee);
        $platformFlatFee = max(0, $platformFlatFee);

        // Provider fees
        $providerPercentFee = ($amount * $providerObj->percentFee()) / 100;
        $providerFlatFee = $providerObj->flatFee();
        $providerTotalFee = $providerPercentFee + $providerFlatFee;

        // Platform fees
        $platformPercentFeeAmount = ($amount * $platformPercentFee) / 100;
        $platformTotalFee = $platformPercentFeeAmount + $platformFlatFee;

        // Total
        $totalFee = $providerTotalFee + $platformTotalFee;

        return [
            'amount' => $amount,
            'provider' => $provider,
            'provider_percent_fee' => round($providerPercentFee, 2),
            'provider_flat_fee' => round($providerFlatFee, 2),
            'provider_total_fee' => round($providerTotalFee, 2),
            'platform_percent_fee' => round($platformPercentFeeAmount, 2),
            'platform_flat_fee' => round($platformFlatFee, 2),
            'platform_total_fee' => round($platformTotalFee, 2),
            'total_fee' => round($totalFee, 2),
            'total_with_fee' => round($amount + $totalFee, 2),
            'currency' => $currency,
        ];
    }

    /**
     * Calculate amount needed to charge customer to receive net amount (inverse calculation)
     * 
     * Useful for: "I want to receive $100 net, how much should customer pay?"
     * 
     * Formula (simplified):
     * charge = (net + platformFlatFee + providerFlatFee) / 
     *          (1 - (providerPercent + platformPercent) / 100)
     * 
     * @param float|int $netAmount Desired net amount to receive
     * @param string $provider Provider name (must be registered)
     * @param float $platformPercentFee Optional platform percentage fee
     * @param float $platformFlatFee Optional platform flat fee
     * @param string $currency Currency (for validation)
     * @return array Charge breakdown
     * 
     * @throws \InvalidArgumentException if provider not found or fees >= 100%
     * 
     * @example
     * // To receive $100 with Stripe (2.9% + $0.30)
     * Money::amountNeeded(100, 'stripe')
     * // [
     * //   'net_amount' => 100,
     * //   'charge_amount' => 103.09,
     * //   'fee_amount' => 3.09,
     * //   'provider' => 'stripe',
     * //   'currency' => 'USD'
     * // ]
     * 
     * // To receive $100 with Stripe + 5% platform fee
     * Money::amountNeeded(100, 'stripe', 5)
     * // [
     * //   'net_amount' => 100,
     * //   'charge_amount' => 110.14,
     * //   'fee_amount' => 10.14,
     * //   'provider' => 'stripe',
     * //   'currency' => 'USD'
     * // ]
     */
    public static function amountNeeded(
        float|int $netAmount,
        string $provider,
        float $platformPercentFee = 0,
        float $platformFlatFee = 0,
        string $currency = 'USD'
    ): array {
        $providerObj = self::getProvider($provider);
        if (!$providerObj) {
            throw new \InvalidArgumentException(
                "Payment provider '{$provider}' not registered. "
                . "Use Money::registerProvider() first."
            );
        }

        $net = (float)$netAmount;
        $platformPercentFee = max(0, $platformPercentFee);
        $platformFlatFee = max(0, $platformFlatFee);

        // Combined percentages
        $totalPercentFee = $providerObj->percentFee() + $platformPercentFee;
        $totalFlatFee = $providerObj->flatFee() + $platformFlatFee;

        if ($totalPercentFee >= 100) {
            throw new \InvalidArgumentException(
                "Combined fees cannot be >= 100% (provider: {$provider}, "
                . "total: {$totalPercentFee}%)"
            );
        }

        // charge = (net + flatFee) / (1 - percent/100)
        $divisor = 1 - ($totalPercentFee / 100);
        $charge = ($net + $totalFlatFee) / $divisor;
        $feeAmount = $charge - $net;

        return [
            'net_amount' => $net,
            'charge_amount' => round($charge, 2),
            'fee_amount' => round($feeAmount, 2),
            'provider' => $provider,
            'platform_percent_fee' => $platformPercentFee,
            'platform_flat_fee' => $platformFlatFee,
            'currency' => $currency,
        ];
    }
}
