<?php

declare(strict_types=1);

namespace Footup\Utils\Validator;

use Footup\Database\DB;
use Footup\Utils\Arrays\ArrDots;

/*
|--------------------------------------------------------------------------
| Validator — simple rule-based validation
|
| Usage:
|   $v = new Validator($_POST, [
|       'email' => 'required|email',
|       'age' => 'required|integer|min:18',
|   ]);
|   if ($v->fails()) return response(['errors' => $v->errors()], 422);
|--------------------------------------------------------------------------
*/

class Validator
{
  protected array $errors = [];
  protected array $rules = [];
  const WILD = '*';

  public function __construct(public array $data, array $rules, protected array $messages = [])
  {
    Validate::addRuleSet($this)
      ->validate($rules);
  }

  /**
   * Add a new rule to the validator.
   * ```php
   * function (Validator $validator, array $data, $pattern, $rule, array $parameters) {
   *     foreach ($validator->getValues($data, $pattern) as $attribute => $value) {
   *         if (null === $value) {
   *             continue;
   *         }
   *         if (in_array($value, $parameters)) {
   *             continue;
   *         }
   *
   *         $validator->addError($attribute, $rule, [':values' => implode(', ', $parameters)]);
   *     }
   * }
   * ```
   *
   * @param string $name
   * @param callable $callable
   * @return Validator
   * @see Validator::setRuleMessage()
   */
  public function addRule(string $name, callable $callable)
  {
    $this->rules[$name] = $callable;
    return $this;
  }


  /**
   * @param string $pattern
   *
   * @return \Generator
   */
  public function getValues($pattern)
  {
    foreach (ArrDots::collate($this->data, $pattern, static::WILD) as $attribute => $value) {
      yield $attribute => $value;
    }
  }

  /**
   * @param string $pattern
   *
   * @return mixed|null First matching value or null
   */
  public function getValue($pattern)
  {
    $imploded = ArrDots::implode($this->data);
    $pattern = sprintf('/^%s$/', str_replace(static::WILD, '[0-9]+', $pattern));

    foreach ($imploded as $attribute => $value) {
      if (preg_match($pattern, $attribute) == 0) {
        continue;
      }

      return $value;
    }

    return null;
  }

  public function validate(array $rules): void
  {
    foreach ($rules as $field => $ruleString) {
      $value = $this->data[$field] ?? null;
      $ruleParts = explode('|', $ruleString);

      foreach ($ruleParts as $rulePart) {
        [$rule, $param] = $this->parseRule($rulePart);

        if (! isset($this->rules[$rule])) {
          continue;
        }

        if (! $this->rules[$rule]($value, $param, $this->data)) {
          $this->addError($field, $rule, $param);
        }
      }
    }
  }

  protected function parseRule(string $rule): array
  {
    if (str_contains($rule, ':')) {
      [$rule, $param] = explode(':', $rule, 2);

      return [$rule, $param];
    }

    return [$rule, null];
  }

  public function addError(string $field, string $rule, ?array $params = []): void
  {
    $key = "{$field}.{$rule}";

    if (isset($this->messages[$key])) {
      $this->errors[$field][] = $this->messages[$key];

      return;
    }

    $replacements = array_merge([
        ':attribute' => $field,
        '!(\S+)\|(\S+)' => true,
        ':rule' => $rule,
    ], $params ?? []);

    $message = text("validator." . $rule, $replacements);

    $this->errors[$field][] = $message;
  }

  protected function checkUnique(string $table, mixed $value): bool
  {
    $count = DB::table($table)->where(explode('.', $table)[1] ?? 'value', $value)->count();

    return $count === 0;
  }

  public function fails(): bool
  {
    return ! empty($this->errors);
  }

  public function passes(): bool
  {
    return empty($this->errors);
  }

  public function errors(): array
  {
    return $this->errors;
  }

  public function add(string $rule, callable $callback): self
  {
    $this->rules[$rule] = $callback;

    return $this;
  }
}
