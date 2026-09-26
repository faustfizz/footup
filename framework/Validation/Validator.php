<?php

declare(strict_types=1);

namespace Footup\Validation;

/*
|--------------------------------------------------------------------------
| Validator — Data validation with common rules
|
| Usage:
|   $v = new Validator($data);
|   if ($v->validate(['email' => 'required|email', 'age' => 'required|integer|min:18'])) {
|       // Valid
|   }
|   $errors = $v->errors();
|--------------------------------------------------------------------------
*/

class Validator
{
    private array $data;
    private array $rules = [];
    private array $errors = [];
    private array $messages = [];

    private array $defaultMessages = [
        'required' => 'The {field} is required',
        'email' => 'The {field} must be a valid email',
        'url' => 'The {field} must be a valid URL',
        'ip' => 'The {field} must be a valid IP address',
        'ipv4' => 'The {field} must be a valid IPv4 address',
        'ipv6' => 'The {field} must be a valid IPv6 address',
        'date' => 'The {field} must be a valid date',
        'json' => 'The {field} must be valid JSON',
        'uuid' => 'The {field} must be a valid UUID',
        
        'string' => 'The {field} must be a string',
        'integer' => 'The {field} must be an integer',
        'numeric' => 'The {field} must be numeric',
        'float' => 'The {field} must be a float',
        'boolean' => 'The {field} must be true or false',
        'array' => 'The {field} must be an array',
        
        'min' => 'The {field} must be at least {param} characters',
        'max' => 'The {field} can not exceed {param} characters',
        'between' => 'The {field} must be between {param1} and {param2} characters',
        'length' => 'The {field} must be exactly {param} characters',
        
        'regex' => 'The {field} format is invalid',
        'alpha' => 'The {field} must contain only letters',
        'alphanumeric' => 'The {field} must contain only letters and numbers',
        'alpha_dash' => 'The {field} must contain only letters, numbers, dashes and underscores',
        'slug' => 'The {field} must be a valid slug',
        'username' => 'The {field} must be a valid username',
        'phone' => 'The {field} must be a valid phone number',
        'base64' => 'The {field} must be valid Base64',
        'credit_card' => 'The {field} must be a valid credit card number',
        
        'lowercase' => 'The {field} must be lowercase',
        'uppercase' => 'The {field} must be uppercase',
        
        'in' => 'The {field} must be one of: {param}',
        'not_in' => 'The {field} must not be one of: {param}',
        'equals' => 'The {field} must equal {param}',
        'not_equals' => 'The {field} must not equal {param}',
        'greater_than' => 'The {field} must be greater than {param}',
        'less_than' => 'The {field} must be less than {param}',
        'greater_than_or_equal' => 'The {field} must be greater than or equal to {param}',
        'less_than_or_equal' => 'The {field} must be less than or equal to {param}',
        
        'confirmed' => 'The {field} confirmation does not match',
        'equals_field' => 'The {field} must equal {param}',
        'different_field' => 'The {field} must be different from {param}',
        
        'starts_with' => 'The {field} must start with {param}',
        'ends_with' => 'The {field} must end with {param}',
        'contains' => 'The {field} must contain {param}',
        'not_contains' => 'The {field} must not contain {param}',
        
        'before' => 'The {field} must be before {param}',
        'after' => 'The {field} must be after {param}',
        'before_or_equal' => 'The {field} must be before or equal to {param}',
        'after_or_equal' => 'The {field} must be after or equal to {param}',
        
        'unique' => 'The {field} is already taken',
        'distinct' => 'The {field} must be different from {param}',
        
        'accepted' => 'The {field} must be accepted',
        'declined' => 'The {field} must be declined',
        'present' => 'The {field} must be present',
        
        'mac_address' => 'The {field} must be a valid MAC address',
        'iban' => 'The {field} must be a valid IBAN',
        'ip_range' => 'The {field} must be in range {param}',
        'hostname' => 'The {field} must be a valid hostname',
        'domain' => 'The {field} must be a valid domain',
        'no_whitespace' => 'The {field} must not contain whitespace',
        'no_spaces' => 'The {field} must not contain spaces',
        'whitespace_only' => 'The {field} must contain only whitespace',
        
        'credit_card_expired' => 'The {field} credit card is expired',
        'credit_card_cvc' => 'The {field} must be a valid CVC',
        'bank_account_number' => 'The {field} must be a valid bank account number',
        'routing_number' => 'The {field} must be a valid routing number',
        
        'mime_type' => 'The {field} must be one of: {param}',
        'file_extension' => 'The {field} must have one of these extensions: {param}',
        'file_size' => 'The {field} must not exceed {param} bytes',
        'file_size_min' => 'The {field} must be at least {param} bytes',
        'file_size_between' => 'The {field} must be between {param1} and {param2} bytes',
        
        'postal_code' => 'The {field} must be a valid postal code for {param}',
        'country' => 'The {field} must be a valid country code',
        'coordinates' => 'The {field} must be valid coordinates (latitude,longitude)',
        'latitude' => 'The {field} must be a valid latitude',
        'longitude' => 'The {field} must be a valid longitude',
        
        'ssn' => 'The {field} must be a valid SSN',
        'passport' => 'The {field} must be a valid passport number',
        'driving_license' => 'The {field} must be a valid driving license',
        
        'hexadecimal' => 'The {field} must be hexadecimal',
        'octal' => 'The {field} must be octal',
        'binary' => 'The {field} must be binary',
        'url64' => 'The {field} must be valid Base64 URL safe',
        
        'unique_fields' => 'The {field} must be different from {param}',
        'unique_together' => 'The combination of {param} must be unique',
    ];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Set custom messages
     */
    public function setMessages(array $messages): self
    {
        $this->messages = array_merge($this->messages, $messages);
        return $this;
    }

    /**
     * Validate data against rules
     */
    public function validate(array $rules): bool
    {
        $this->rules = $rules;
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $ruleList = explode('|', $fieldRules);
            
            foreach ($ruleList as $rule) {
                $this->validateRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Validate single field rule
     */
    private function validateRule(string $field, string $rule): void
    {
        [$ruleName, $params] = $this->parseRule($rule);
        $value = $this->data[$field] ?? null;

        // Construct validator method name: "required" → "validateRequired"
        $methodName = 'validate' . str_replace(' ', '', ucwords(str_replace('_', ' ', $ruleName)));

        // Check if validator method exists
        if (!method_exists($this, $methodName)) {
            throw new \BadMethodCallException("Validation rule [{$ruleName}] does not exist");
        }

        // Call the validator method
        $this->$methodName($field, $value, $params);
    }

    // ============================================================================
    // BASIC VALIDATORS
    // ============================================================================

    private function validateRequired(string $field, $value, array $params): void
    {
        if ($this->isEmpty($value)) {
            $this->addError($field, 'required');
        }
    }

    private function validateEmail(string $field, $value, array $params): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email');
        }
    }

    private function validateUrl(string $field, $value, array $params): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'url');
        }
    }

    private function validateIp(string $field, $value, array $params): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_IP)) {
            $this->addError($field, 'ip');
        }
    }

    private function validateIpv4(string $field, $value, array $params): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->addError($field, 'ipv4');
        }
    }

    private function validateIpv6(string $field, $value, array $params): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->addError($field, 'ipv6');
        }
    }

    private function validateDate(string $field, $value, array $params): void
    {
        if ($value && !strtotime($value)) {
            $this->addError($field, 'date');
        }
    }

    private function validateJson(string $field, $value, array $params): void
    {
        if ($value) {
            json_decode($value);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError($field, 'json');
            }
        }
    }

    private function validateUuid(string $field, $value, array $params): void
    {
        $uuidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        if ($value && !preg_match($uuidPattern, $value)) {
            $this->addError($field, 'uuid');
        }
    }

    // ============================================================================
    // TYPE VALIDATORS
    // ============================================================================

    private function validateString(string $field, $value, array $params): void
    {
        if ($value && !is_string($value)) {
            $this->addError($field, 'string');
        }
    }

    private function validateInteger(string $field, $value, array $params): void
    {
        if ($value && !is_int((int)$value) && (string)(int)$value !== (string)$value) {
            $this->addError($field, 'integer');
        }
    }

    private function validateNumeric(string $field, $value, array $params): void
    {
        if ($value && !is_numeric($value)) {
            $this->addError($field, 'numeric');
        }
    }

    private function validateFloat(string $field, $value, array $params): void
    {
        if ($value && !is_float((float)$value) && !is_numeric($value)) {
            $this->addError($field, 'float');
        }
    }

    private function validateBoolean(string $field, $value, array $params): void
    {
        if ($value && !in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'])) {
            $this->addError($field, 'boolean');
        }
    }

    private function validateArray(string $field, $value, array $params): void
    {
        if ($value && !is_array($value)) {
            $this->addError($field, 'array');
        }
    }

    // ============================================================================
    // LENGTH/SIZE VALIDATORS
    // ============================================================================

    private function validateMin(string $field, $value, array $params): void
    {
        if ($value && strlen((string)$value) < (int)($params[0] ?? 0)) {
            $this->addError($field, 'min', $params[0] ?? 0);
        }
    }

    private function validateMax(string $field, $value, array $params): void
    {
        if ($value && strlen((string)$value) > (int)($params[0] ?? 0)) {
            $this->addError($field, 'max', $params[0] ?? 0);
        }
    }

    private function validateBetween(string $field, $value, array $params): void
    {
        if ($value) {
            $min = (int)($params[0] ?? 0);
            $max = (int)($params[1] ?? 999999);
            $len = strlen((string)$value);
            if ($len < $min || $len > $max) {
                $this->addError($field, 'between', $min, $max);
            }
        }
    }

    private function validateLength(string $field, $value, array $params): void
    {
        if ($value && strlen((string)$value) != (int)($params[0] ?? 0)) {
            $this->addError($field, 'length', $params[0] ?? 0);
        }
    }

    // ============================================================================
    // PATTERN VALIDATORS
    // ============================================================================

    private function validateRegex(string $field, $value, array $params): void
    {
        if ($value && !preg_match($params[0] ?? '/.*/', $value)) {
            $this->addError($field, 'regex');
        }
    }

    private function validateAlpha(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^[a-zA-Z]+$/', $value)) {
            $this->addError($field, 'alpha');
        }
    }

    private function validateAlphanumeric(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            $this->addError($field, 'alphanumeric');
        }
    }

    private function validateAlphaDash(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
            $this->addError($field, 'alpha_dash');
        }
    }

    private function validateSlug(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
            $this->addError($field, 'slug');
        }
    }

    private function validateUsername(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $value)) {
            $this->addError($field, 'username');
        }
    }

    private function validatePhone(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^[\d\s\-\+\(\)]{10,}$/', $value)) {
            $this->addError($field, 'phone');
        }
    }

    private function validateBase64(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^[A-Za-z0-9+\/]{4}([A-Za-z0-9+\/]{4})*([A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?$/', $value)) {
            $this->addError($field, 'base64');
        }
    }

    private function validateCreditCard(string $field, $value, array $params): void
    {
        if ($value) {
            $value = preg_replace('/\s+/', '', $value);
            if (!preg_match('/^\d{13,19}$/', $value) || !$this->luhnCheck($value)) {
                $this->addError($field, 'credit_card');
            }
        }
    }

    // ============================================================================
    // CASE VALIDATORS
    // ============================================================================

    private function validateLowercase(string $field, $value, array $params): void
    {
        if ($value && $value !== strtolower($value)) {
            $this->addError($field, 'lowercase');
        }
    }

    private function validateUppercase(string $field, $value, array $params): void
    {
        if ($value && $value !== strtoupper($value)) {
            $this->addError($field, 'uppercase');
        }
    }

    // ============================================================================
    // COMPARISON VALIDATORS
    // ============================================================================

    private function validateIn(string $field, $value, array $params): void
    {
        if ($value && !in_array($value, $params)) {
            $this->addError($field, 'in', implode(', ', $params));
        }
    }

    private function validateNotIn(string $field, $value, array $params): void
    {
        if ($value && in_array($value, $params)) {
            $this->addError($field, 'not_in', implode(', ', $params));
        }
    }

    private function validateEquals(string $field, $value, array $params): void
    {
        if ($value != ($params[0] ?? null)) {
            $this->addError($field, 'equals', $params[0] ?? '');
        }
    }

    private function validateNotEquals(string $field, $value, array $params): void
    {
        if ($value == ($params[0] ?? null)) {
            $this->addError($field, 'not_equals', $params[0] ?? '');
        }
    }

    private function validateGreaterThan(string $field, $value, array $params): void
    {
        if ($value && (int)$value <= (int)($params[0] ?? 0)) {
            $this->addError($field, 'greater_than', $params[0] ?? 0);
        }
    }

    private function validateLessThan(string $field, $value, array $params): void
    {
        if ($value && (int)$value >= (int)($params[0] ?? 0)) {
            $this->addError($field, 'less_than', $params[0] ?? 0);
        }
    }

    private function validateGreaterThanOrEqual(string $field, $value, array $params): void
    {
        if ($value && (int)$value < (int)($params[0] ?? 0)) {
            $this->addError($field, 'greater_than_or_equal', $params[0] ?? 0);
        }
    }

    private function validateLessThanOrEqual(string $field, $value, array $params): void
    {
        if ($value && (int)$value > (int)($params[0] ?? 0)) {
            $this->addError($field, 'less_than_or_equal', $params[0] ?? 0);
        }
    }

    // ============================================================================
    // FIELD COMPARISON VALIDATORS
    // ============================================================================

    private function validateConfirmed(string $field, $value, array $params): void
    {
        $confirmField = $field . '_confirmation';
        if ($value !== ($this->data[$confirmField] ?? null)) {
            $this->addError($field, 'confirmed');
        }
    }

    private function validateEqualsField(string $field, $value, array $params): void
    {
        $otherField = $params[0] ?? null;
        if ($otherField && $value !== ($this->data[$otherField] ?? null)) {
            $this->addError($field, 'equals_field', $otherField);
        }
    }

    private function validateDifferentField(string $field, $value, array $params): void
    {
        $otherField = $params[0] ?? null;
        if ($otherField && $value === ($this->data[$otherField] ?? null)) {
            $this->addError($field, 'different_field', $otherField);
        }
    }

    // ============================================================================
    // STRING CONTENT VALIDATORS
    // ============================================================================

    private function validateStartsWith(string $field, $value, array $params): void
    {
        $prefix = $params[0] ?? '';
        if ($value && !str_starts_with($value, $prefix)) {
            $this->addError($field, 'starts_with', $prefix);
        }
    }

    private function validateEndsWith(string $field, $value, array $params): void
    {
        $suffix = $params[0] ?? '';
        if ($value && !str_ends_with($value, $suffix)) {
            $this->addError($field, 'ends_with', $suffix);
        }
    }

    private function validateContains(string $field, $value, array $params): void
    {
        $substring = $params[0] ?? '';
        if ($value && strpos($value, $substring) === false) {
            $this->addError($field, 'contains', $substring);
        }
    }

    private function validateNotContains(string $field, $value, array $params): void
    {
        $substring = $params[0] ?? '';
        if ($value && strpos($value, $substring) !== false) {
            $this->addError($field, 'not_contains', $substring);
        }
    }

    // ============================================================================
    // DATE VALIDATORS
    // ============================================================================

    private function validateBefore(string $field, $value, array $params): void
    {
        $beforeDate = $params[0] ?? date('Y-m-d');
        if ($value && strtotime($value) >= strtotime($beforeDate)) {
            $this->addError($field, 'before', $beforeDate);
        }
    }

    private function validateAfter(string $field, $value, array $params): void
    {
        $afterDate = $params[0] ?? date('Y-m-d');
        if ($value && strtotime($value) <= strtotime($afterDate)) {
            $this->addError($field, 'after', $afterDate);
        }
    }

    private function validateBeforeOrEqual(string $field, $value, array $params): void
    {
        $beforeDate = $params[0] ?? date('Y-m-d');
        if ($value && strtotime($value) > strtotime($beforeDate)) {
            $this->addError($field, 'before_or_equal', $beforeDate);
        }
    }

    private function validateAfterOrEqual(string $field, $value, array $params): void
    {
        $afterDate = $params[0] ?? date('Y-m-d');
        if ($value && strtotime($value) < strtotime($afterDate)) {
            $this->addError($field, 'after_or_equal', $afterDate);
        }
    }

    // ============================================================================
    // DATABASE VALIDATORS
    // ============================================================================

    private function validateUnique(string $field, $value, array $params): void
    {
        if (!$value || empty($params)) {
            return;
        }

        $table = $params[0];
        
        try {
            $exists = \Footup\Orm\DB::table($table)
                ->where($field, $value)
                ->exists();
                
            if ($exists) {
                $this->addError($field, 'unique');
            }
        } catch (\Exception $e) {
            // Table might not exist, skip validation
        }
    }

    private function validateDistinct(string $field, $value, array $params): void
    {
        $otherField = $params[0] ?? null;
        if ($otherField && $value === ($this->data[$otherField] ?? null)) {
            $this->addError($field, 'distinct', $otherField);
        }
    }

    // ============================================================================
    // ACCEPTANCE VALIDATORS
    // ============================================================================

    private function validateAccepted(string $field, $value, array $params): void
    {
        if (!in_array($value, [true, 1, '1', 'on', 'yes'])) {
            $this->addError($field, 'accepted');
        }
    }

    private function validateDeclined(string $field, $value, array $params): void
    {
        if (in_array($value, [true, 1, '1', 'on', 'yes'])) {
            $this->addError($field, 'declined');
        }
    }

    private function validatePresent(string $field, $value, array $params): void
    {
        if (!isset($this->data[$field])) {
            $this->addError($field, 'present');
        }
    }

    // ============================================================================
    // NETWORK/SECURITY VALIDATORS
    // ============================================================================

    private function validateMacAddress(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $value)) {
            $this->addError($field, 'mac_address');
        }
    }

    private function validateIban(string $field, $value, array $params): void
    {
        if ($value && !$this->validateIbanChecksum($value)) {
            $this->addError($field, 'iban');
        }
    }

    private function validateIpRange(string $field, $value, array $params): void
    {
        if (!$value || empty($params)) {
            return;
        }

        $ip = ip2long($value);
        $range = $params[0];

        // Format: "192.168.1.0/24" or "192.168.1.0-192.168.1.255"
        if (strpos($range, '/') !== false) {
            [$subnet, $mask] = explode('/', $range);
            $subnetLong = ip2long($subnet);
            $maskLong = -1 << (32 - (int)$mask);
            $inRange = ($ip & $maskLong) === ($subnetLong & $maskLong);
        } elseif (strpos($range, '-') !== false) {
            [$start, $end] = explode('-', $range);
            $inRange = $ip >= ip2long($start) && $ip <= ip2long($end);
        } else {
            $inRange = $ip === ip2long($range);
        }

        if (!$inRange) {
            $this->addError($field, 'ip_range', $range);
        }
    }

    private function validateHostname(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i', $value)) {
            $this->addError($field, 'hostname');
        }
    }

    private function validateDomain(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i', $value)) {
            $this->addError($field, 'domain');
        }
    }

    private function validateNoWhitespace(string $field, $value, array $params): void
    {
        if ($value && preg_match('/\s/', $value)) {
            $this->addError($field, 'no_whitespace');
        }
    }

    private function validateNoSpaces(string $field, $value, array $params): void
    {
        if ($value && strpos($value, ' ') !== false) {
            $this->addError($field, 'no_spaces');
        }
    }

    private function validateWhitespaceOnly(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^\s+$/', $value)) {
            $this->addError($field, 'whitespace_only');
        }
    }

    // ============================================================================
    // BANKING/FINANCE VALIDATORS
    // ============================================================================

    private function validateCreditCardExpired(string $field, $value, array $params): void
    {
        if (!$value || !preg_match('/^(\d{2})\/(\d{4})$/', $value, $matches)) {
            $this->addError($field, 'credit_card_expired');
            return;
        }

        $month = (int)$matches[1];
        $year = (int)$matches[2];

        if ($month < 1 || $month > 12) {
            $this->addError($field, 'credit_card_expired');
            return;
        }

        $expireTime = mktime(0, 0, 0, $month, 1, $year);
        if (time() > $expireTime) {
            $this->addError($field, 'credit_card_expired');
        }
    }

    private function validateCreditCardCvc(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^\d{3,4}$/', $value)) {
            $this->addError($field, 'credit_card_cvc');
        }
    }

    private function validateBankAccountNumber(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^\d{5,17}$/', $value)) {
            $this->addError($field, 'bank_account_number');
        }
    }

    private function validateRoutingNumber(string $field, $value, array $params): void
    {
        // US Routing Number validation (9 digits)
        if ($value && !preg_match('/^\d{9}$/', $value)) {
            $this->addError($field, 'routing_number');
        }
    }

    // ============================================================================
    // FILE/UPLOAD VALIDATORS
    // ============================================================================

    private function validateMimeType(string $field, $value, array $params): void
    {
        if (!$value || empty($params)) {
            return;
        }

        $allowedTypes = $params;
        $fileType = mime_content_type($value) ?? '';

        if (!in_array($fileType, $allowedTypes)) {
            $this->addError($field, 'mime_type', implode(', ', $allowedTypes));
        }
    }

    private function validateFileExtension(string $field, $value, array $params): void
    {
        if (!$value || empty($params)) {
            return;
        }

        $allowedExt = $params;
        $ext = pathinfo($value, PATHINFO_EXTENSION);

        if (!in_array(strtolower($ext), array_map('strtolower', $allowedExt))) {
            $this->addError($field, 'file_extension', implode(', ', $allowedExt));
        }
    }

    private function validateFileSize(string $field, $value, array $params): void
    {
        if (!$value || empty($params)) {
            return;
        }

        $maxSize = (int)$params[0];  // in bytes
        $fileSize = is_file($value) ? filesize($value) : strlen($value);

        if ($fileSize > $maxSize) {
            $this->addError($field, 'file_size', $maxSize);
        }
    }

    private function validateFileSizeMin(string $field, $value, array $params): void
    {
        if (!$value || empty($params)) {
            return;
        }

        $minSize = (int)$params[0];  // in bytes
        $fileSize = is_file($value) ? filesize($value) : strlen($value);

        if ($fileSize < $minSize) {
            $this->addError($field, 'file_size_min', $minSize);
        }
    }

    private function validateFileSizeBetween(string $field, $value, array $params): void
    {
        if (!$value || count($params) < 2) {
            return;
        }

        $minSize = (int)$params[0];
        $maxSize = (int)$params[1];
        $fileSize = is_file($value) ? filesize($value) : strlen($value);

        if ($fileSize < $minSize || $fileSize > $maxSize) {
            $this->addError($field, 'file_size_between', $minSize, $maxSize);
        }
    }

    // ============================================================================
    // GEOGRAPHIC VALIDATORS
    // ============================================================================

    private function validatePostalCode(string $field, $value, array $params): void
    {
        $country = $params[0] ?? 'US';

        $patterns = [
            'US' => '/^\d{5}(?:-\d{4})?$/',
            'CA' => '/^[A-Z]\d[A-Z]\s?\d[A-Z]\d$/i',
            'UK' => '/^[A-Z]{1,2}\d{1,2}\s?\d[A-Z]{2}$/i',
            'DE' => '/^\d{5}$/',
            'FR' => '/^\d{5}$/',
            'NL' => '/^\d{4}\s?[A-Z]{2}$/i',
            'AU' => '/^\d{4}$/',
            'JP' => '/^\d{3}-\d{4}$/',
        ];

        $pattern = $patterns[$country] ?? '/^[\w\d-]+$/';

        if ($value && !preg_match($pattern, $value)) {
            $this->addError($field, 'postal_code', $country);
        }
    }

    private function validateCountry(string $field, $value, array $params): void
    {
        $validCountries = [
            'US', 'CA', 'MX', 'UK', 'FR', 'DE', 'IT', 'ES', 'NL', 'BE',
            'CH', 'AT', 'SE', 'NO', 'DK', 'FI', 'PL', 'CZ', 'PT', 'GR',
            'AU', 'NZ', 'JP', 'CN', 'IN', 'BR', 'RU', 'ZA', 'SG', 'HK',
        ];

        if ($value && !in_array(strtoupper($value), $validCountries)) {
            $this->addError($field, 'country');
        }
    }

    private function validateCoordinates(string $field, $value, array $params): void
    {
        // Format: "40.7128,-74.0060" (latitude,longitude)
        if ($value && !preg_match('/^-?\d{1,3}\.\d+,-?\d{1,3}\.\d+$/', $value)) {
            $this->addError($field, 'coordinates');
        }
    }

    private function validateLatitude(string $field, $value, array $params): void
    {
        if ($value) {
            $lat = (float)$value;
            if ($lat < -90 || $lat > 90) {
                $this->addError($field, 'latitude');
            }
        }
    }

    private function validateLongitude(string $field, $value, array $params): void
    {
        if ($value) {
            $lon = (float)$value;
            if ($lon < -180 || $lon > 180) {
                $this->addError($field, 'longitude');
            }
        }
    }

    // ============================================================================
    // SOCIAL/IDENTITY VALIDATORS
    // ============================================================================

    private function validateSSN(string $field, $value, array $params): void
    {
        // US Social Security Number: XXX-XX-XXXX
        if ($value && !preg_match('/^\d{3}-\d{2}-\d{4}$/', $value)) {
            $this->addError($field, 'ssn');
        }
    }

    private function validatePassport(string $field, $value, array $params): void
    {
        // Generic passport format (2-3 letters, 6-9 digits)
        if ($value && !preg_match('/^[A-Z]{2,3}\d{6,9}$/', $value)) {
            $this->addError($field, 'passport');
        }
    }

    private function validateDrivingLicense(string $field, $value, array $params): void
    {
        // Generic driver's license format
        if ($value && !preg_match('/^[A-Z0-9]{5,20}$/', $value)) {
            $this->addError($field, 'driving_license');
        }
    }

    // ============================================================================
    // ENCODING VALIDATORS
    // ============================================================================

    private function validateHexadecimal(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^[0-9A-Fa-f]+$/', $value)) {
            $this->addError($field, 'hexadecimal');
        }
    }

    private function validateOctal(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^[0-7]+$/', $value)) {
            $this->addError($field, 'octal');
        }
    }

    private function validateBinary(string $field, $value, array $params): void
    {
        if ($value && !preg_match('/^[01]+$/', $value)) {
            $this->addError($field, 'binary');
        }
    }

    private function validateUrl64(string $field, $value, array $params): void
    {
        // Base64 URL safe
        if ($value && !preg_match('/^[A-Za-z0-9_-]+={0,2}$/', $value)) {
            $this->addError($field, 'url64');
        }
    }

    // ============================================================================
    // UNIQUENESS/COMPARISON VALIDATORS
    // ============================================================================

    private function validateUniqueFields(string $field, $value, array $params): void
    {
        // Check that field is unique compared to other fields
        foreach ($params as $otherField) {
            if ($value === ($this->data[$otherField] ?? null)) {
                $this->addError($field, 'unique_fields', $otherField);
                return;
            }
        }
    }

    private function validateUniqueTogether(string $field, $value, array $params): void
    {
        if (!$value || empty($params)) {
            return;
        }

        $table = $params[0];
        $fields = array_slice($params, 1);

        if (empty($fields)) {
            return;
        }

        try {
            $query = \Footup\Orm\DB::table($table);
            foreach ($fields as $f) {
                $query->where($f, $this->data[$f] ?? null);
            }

            if ($query->exists()) {
                $this->addError($field, 'unique_together', implode(',', $fields));
            }
        } catch (\Exception $e) {
            // Table might not exist
        }
    }

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    /**
     * Luhn algorithm for credit card validation
     */
    private function luhnCheck(string $cardNumber): bool
    {
        $digits = str_split($cardNumber);
        $sum = 0;
        $isEven = false;

        for ($i = count($digits) - 1; $i >= 0; $i--) {
            $digit = (int)$digits[$i];

            if ($isEven) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $isEven = !$isEven;
        }

        return $sum % 10 === 0;
    }

    /**
     * IBAN checksum validation
     */
    private function validateIbanChecksum(string $iban): bool
    {
        $iban = strtoupper(str_replace(' ', '', $iban));

        // Check length (IBAN must be 15-34 characters)
        if (strlen($iban) < 15 || strlen($iban) > 34) {
            return false;
        }

        // Move first 4 chars to end
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);

        // Convert letters to numbers (A=10, B=11, ..., Z=35)
        $numeric = '';
        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            if (is_numeric($char)) {
                $numeric .= $char;
            } else {
                $numeric .= (string)(ord($char) - ord('A') + 10);
            }
        }

        // Check mod97 = 1
        return bcmod($numeric, '97') === '1';
    }

    /**
     * Parse rule string into name and params
     */
    private function parseRule(string $rule): array
    {
        if (strpos($rule, ':') === false) {
            return [$rule, []];
        }

        [$name, $params] = explode(':', $rule, 2);
        return [$name, explode(',', $params)];
    }

    /**
     * Check if value is empty
     */
    private function isEmpty($value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    /**
     * Add error message
     * 
     * Supports both :field and {field} syntax for placeholders
     * Supports :param, :param1, :param2 and {param}, {param1}, {param2}
     */
    private function addError(string $field, string $rule, $param1 = null, $param2 = null): void
    {
        $message = text("validator." . $rule, [
          '{field}' => $field,
          '{param}' => $param1,
          '{param1}' => $param1,
          '{param2}' => $param2,
          ':field' => $field,
          ':param' => $param1,
          ':param1' => $param1,
          ':param2' => $param2,
        ]) ?? $this->messages[$rule] ?? $this->defaultMessages[$rule] ?? "Validation failed";
        
        // Support both {field} and :field syntax
        $message = str_replace('{field}', $field, $message);
        $message = str_replace(':field', $field, $message);
        
        // Support both {param} and :param syntax
        $message = str_replace('{param}', $param1, $message);
        $message = str_replace(':param', $param1, $message);
        
        // Support both {param1} and :param1 syntax
        $message = str_replace('{param1}', $param1, $message);
        $message = str_replace(':param1', $param1, $message);
        
        // Support both {param2} and :param2 syntax
        $message = str_replace('{param2}', $param2, $message);
        $message = str_replace(':param2', $param2, $message);

        $this->errors[$field][] = $message;
    }

    /**
     * Get all errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error for field
     */
    public function error(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Check if has errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if validation passed (no errors)
     */
    public function passes(): bool
    {
        return !$this->hasErrors();
    }

    /**
     * Get validated data (only fields from rules)
     */
    public function validated(): array
    {
        $validated = [];
        foreach ($this->rules as $field => $rules) {
            if (isset($this->data[$field])) {
                $validated[$field] = $this->data[$field];
            }
        }
        return $validated;
    }

    /**
     * Quick validation check — can be called with custom messages
     * 
     * Usage:
     *   Validator::check($data, $rules)
     *   Validator::check($data, $rules, $messages)
     */
    public static function check(array $data, array $rules, array $messages = []): array
    {
        $validator = new static($data);
        
        if (!empty($messages)) {
            $validator->setMessages($messages);
        }

        if (!$validator->validate($rules)) {
            return ['valid' => false, 'errors' => $validator->errors()];
        }

        return ['valid' => true, 'data' => $validator->validated()];
    }

    /**
     * Factory method — alternative to check()
     * 
     * Usage:
     *   Validator::make($data, $rules)->validate()
     *   Validator::make($data, $rules, $messages)->validate()
     */
    public static function make(array $data, array $rules, array $messages = []): self
    {
        $validator = new static($data);
        
        if (!empty($messages)) {
            $validator->setMessages($messages);
        }

        $validator->validate($rules);
        return $validator;
    }
}
