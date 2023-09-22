<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Utils\Token
 * @version 0.1
 * @link https://github.com/adhocore/php-jwt
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Footup\Utils\Token;

trait ValidatesJWT
{
    /**
     * Throw up if input parameters invalid.
     *
     * @codeCoverageIgnore
     */
    protected function validateConfig($key, string $algo, int $maxAge, int $leeway)
    {
        if (empty($key)) {
            throw new JWTException(text('Token.invalidTokenKeyEmpty'), static::ERROR_KEY_EMPTY);
        }

        if (!isset($this->algos[$algo])) {
            throw new JWTException(text('Token.invalidTokenAlgo', [$algo]), static::ERROR_ALGO_UNSUPPORTED);
        }

        if ($maxAge < 1) {
            throw new JWTException(text('Token.invalidTokenAge'), static::ERROR_INVALID_MAXAGE);
        }

        if ($leeway < 0 || $leeway > 120) {
            throw new JWTException(text('Token.invalidTokenLeeway'), static::ERROR_INVALID_LEEWAY);
        }
    }

    /**
     * Throw up if header invalid.
     */
    protected function validateHeader(array $header)
    {
        if (empty($header['alg'])) {
            throw new JWTException(text('Token.invalidTokenMissingAlgo'), static::ERROR_ALGO_MISSING);
        }
        if (empty($this->algos[$header['alg']])) {
            throw new JWTException(text('Token.invalidTokenAlgo'), static::ERROR_ALGO_UNSUPPORTED);
        }

        $this->validateKid($header);
    }

    /**
     * Throw up if kid exists and invalid.
     */
    protected function validateKid(array $header)
    {
        if (!isset($header['kid'])) {
            return;
        }
        if (empty($this->keys[$header['kid']])) {
            throw new JWTException(text('Token.invalidTokenKeyUnknown'), static::ERROR_KID_UNKNOWN);
        }

        $this->key = $this->keys[$header['kid']];
    }

    /**
     * Throw up if timestamp claims like iat, exp, nbf are invalid.
     */
    protected function validateTimestamps(array $payload)
    {
        $timestamp = $this->timestamp ?: \time();
        $checks    = [
            ['exp', $this->leeway /*          */ , static::ERROR_TOKEN_EXPIRED, 'Expired'],
            ['iat', $this->maxAge - $this->leeway, static::ERROR_TOKEN_EXPIRED, 'Expired'],
            ['nbf', -$this->leeway, static::ERROR_TOKEN_NOT_NOW, 'Not now'],
        ];

        foreach ($checks as list($key, $offset, $code, $error)) {
            if (isset($payload[$key])) {
                $offset += $payload[$key];
                $fail    = $key === 'nbf' ? $timestamp <= $offset : $timestamp >= $offset;

                if ($fail) {
                    throw new JWTException(text('Token.invalidToken', [$error]), $code);
                }
            }
        }
    }

    /**
     * Throw up if key is not resource or file path to private key.
     */
    protected function validateKey()
    {
        if (\is_string($key = $this->key)) {
            if (\substr($key, 0, 7) !== 'file://') {
                $key = 'file://' . $key;
            }

            $this->key = \openssl_get_privatekey($key, $this->passphrase ?: '');
        }

        if (\PHP_VERSION_ID < 80000 && !\is_resource($this->key)) {
            throw new JWTException(text('Token.invalidKey'), static::ERROR_KEY_INVALID);
        }

        if (\PHP_VERSION_ID > 80000 && !(
            $this->key instanceof \OpenSSLAsymmetricKey
            || $this->key instanceof \OpenSSLCertificate
            || $this->key instanceof \OpenSSLCertificateSigningRequest
        )) {
            throw new JWTException(text('Token.invalidKey'), static::ERROR_KEY_INVALID);
        }
    }

    /**
     * Throw up if last json_encode/decode was a failure.
     */
    protected function validateLastJson()
    {
        if (\JSON_ERROR_NONE === \json_last_error()) {
            return;
        }

        throw new JWTException(text('Token.jsonFailed', [\json_last_error_msg()]), static::ERROR_JSON_FAILED);
    }
}
