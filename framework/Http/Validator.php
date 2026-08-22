<?php

declare(strict_types=1);

namespace Footup\Http;

use Footup\Database\DB;

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

  public function __construct(protected array $data, array $rules, protected array $messages = [])
  {
    $this->rules = [
      'required' => fn($v) => $v !== '' && $v !== null,
      'email' => fn($v) => filter_var($v, FILTER_VALIDATE_EMAIL) !== false,
      'integer' => fn($v) => is_numeric($v) && intval($v) == $v,
      'numeric' => fn($v) => is_numeric($v),
      'min' => fn($v, $p) => strlen((string)$v) >= (int)$p,
      'max' => fn($v, $p) => strlen((string)$v) <= (int)$p,
      'in' => fn($v, $p) => in_array($v, explode(',', $p), true),
      'regex' => fn($v, $p) => preg_match($p, (string)$v) === 1,
      'confirmed' => fn($v, $p, $d) => ($d[$p . '_confirmation'] ?? null) === $v,
      'unique' => fn($v, $p, $d) => $this->checkUnique($p, $v),
    ];
    
    $this->validate($rules);
  }

  protected function validate(array $rules): void
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

  protected function addError(string $field, string $rule, ?string $param): void
  {
    $key = "{$field}.{$rule}";

    if (isset($this->messages[$key])) {
      $this->errors[$field][] = $this->messages[$key];

      return;
    }

    $message = match ($rule) {
      'required' => "{$field} is required",
      'email' => "{$field} must be a valid email",
      'integer' => "{$field} must be an integer",
      'numeric' => "{$field} must be numeric",
      'min' => "{$field} must be at least {$param} characters",
      'max' => "{$field} must be at most {$param} characters",
      'in' => "{$field} must be one of: {$param}",
      'confirmed' => "{$field} must match {$field}_confirmation",
      'unique' => "{$field} must be unique",
      default => "{$field} is invalid",
    };

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
