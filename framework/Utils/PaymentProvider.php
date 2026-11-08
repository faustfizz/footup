<?php

namespace Footup\Utils;

/**
 * PaymentProvider - Manage payment gateway fees
 * 
 * Encapsulates fee structure for payment providers (Paystack, Stripe, PayPal, etc.)
 * Supports both percentage and flat fees.
 * 
 * @package Footup
 */
class PaymentProvider
{
    /**
     * Provider name (e.g., 'paystack', 'stripe', 'paypal')
     */
    private string $name;

    /**
     * Percentage fee (e.g., 1.5 for 1.5%)
     */
    private float $percentFee;

    /**
     * Flat fee amount (e.g., 0.30 for $0.30)
     */
    private float $flatFee;

    /**
     * Currency for flat fee
     */
    private string $currency;

    /**
     * Create a new PaymentProvider
     * 
     * @param string $name Provider name
     * @param float $percentFee Percentage fee (1.5 = 1.5%)
     * @param float $flatFee Flat fee amount
     * @param string $currency Currency for flat fee
     */
    public function __construct(
        string $name,
        float $percentFee = 0,
        float $flatFee = 0,
        string $currency = 'USD'
    ) {
        $this->name = $name;
        $this->percentFee = max(0, $percentFee);
        $this->flatFee = max(0, $flatFee);
        $this->currency = $currency;
    }

    /**
     * Get provider name
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get percentage fee
     */
    public function percentFee(): float
    {
        return $this->percentFee;
    }

    /**
     * Get flat fee
     */
    public function flatFee(): float
    {
        return $this->flatFee;
    }

    /**
     * Get flat fee currency
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Calculate total fee for an amount
     * 
     * Fee = (amount * percentFee / 100) + flatFee
     * 
     * @param float|int $amount The transaction amount
     * @return float Total fee
     */
    public function calculateFee(float|int $amount): float
    {
        $amount = (float)$amount;
        $percentPortion = ($amount * $this->percentFee) / 100;
        return $percentPortion + $this->flatFee;
    }

    /**
     * Calculate total amount including fees
     * 
     * Total = amount + fee
     * 
     * @param float|int $amount The transaction amount
     * @return float Amount including fees
     */
    public function amountWithFee(float|int $amount): float
    {
        return (float)$amount + $this->calculateFee($amount);
    }

    /**
     * Calculate amount needed to receive a net amount (inverse calculation)
     * 
     * If you want to receive $100 net, how much should customer pay?
     * 
     * charge = net / (1 - (percentFee / 100)) + flatFee
     * 
     * @param float|int $netAmount Desired net amount after fees
     * @return float Amount to charge customer
     */
    public function amountNeeded(float|int $netAmount): float
    {
        $net = (float)$netAmount;
        
        if ($this->percentFee == 0 && $this->flatFee == 0) {
            return $net;
        }

        if ($this->percentFee == 0) {
            // Only flat fee
            return $net + $this->flatFee;
        }

        // Solve: net = charge - (charge * percentFee/100) - flatFee
        // net = charge * (1 - percentFee/100) - flatFee
        // net + flatFee = charge * (1 - percentFee/100)
        // charge = (net + flatFee) / (1 - percentFee/100)

        $divisor = 1 - ($this->percentFee / 100);
        if ($divisor <= 0) {
            throw new \InvalidArgumentException(
                "Fee percentage cannot be >= 100% (provider: {$this->name})"
            );
        }

        return ($net + $this->flatFee) / $divisor;
    }

    /**
     * Get fee breakdown for an amount
     * 
     * Returns array with:
     * - original: original amount
     * - percent_fee: percentage portion
     * - flat_fee: flat portion
     * - total_fee: total fee (percent + flat)
     * - total_with_fee: original + total_fee
     * 
     * @param float|int $amount The transaction amount
     * @return array Fee breakdown
     * 
     * @example
     * $provider->breakdown(100)
     * // [
     * //   'original' => 100,
     * //   'percent_fee' => 1.5,
     * //   'flat_fee' => 0.30,
     * //   'total_fee' => 1.8,
     * //   'total_with_fee' => 101.8
     * // ]
     */
    public function breakdown(float|int $amount): array
    {
        $amount = (float)$amount;
        $percentFee = ($amount * $this->percentFee) / 100;
        $flatFee = $this->flatFee;
        $totalFee = $percentFee + $flatFee;

        return [
            'original' => $amount,
            'percent_fee' => round($percentFee, 2),
            'flat_fee' => round($flatFee, 2),
            'total_fee' => round($totalFee, 2),
            'total_with_fee' => round($amount + $totalFee, 2),
        ];
    }

    /**
     * Get string representation
     */
    public function __toString(): string
    {
        return "{$this->name} ({$this->percentFee}% + {$this->currency}{$this->flatFee})";
    }
}
