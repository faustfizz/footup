<?php

declare(strict_types=1);

namespace Footup\Testing;

use Footup\Engine\Application;
use Footup\Http\Request;
use Footup\Orm\DB;

/*
|--------------------------------------------------------------------------
| TestCase — minimal test framework for unit/feature tests
|
| Usage:
|   class UserTest extends TestCase {
|       public function test_can_create_user() {
|           $user = User::create(['name' => 'Ada']);
|           $this->assertEquals('Ada', $user->name);
|           $this->assertDatabaseHas('users', ['name' => 'Ada']);
|       }
|       public function test_can_get_api() {
|           $this->get('/api/users')->assertStatus(200)->assertJsonPath('0.id', 1);
|       }
|   }
|--------------------------------------------------------------------------
*/

class TestCase
{
    protected ?Application $app = null;
    protected ?\Footup\Http\Request $lastRequest = null;
    protected ?\Footup\Http\Response $lastResponse = null;

    public function setUp(): void
    {
        // Override to setup test env
    }

    public function tearDown(): void
    {
        // Override to cleanup
    }

    protected function get(string $path): TestResponse
    {
        return $this->request('GET', $path);
    }

    protected function post(string $path, array $data = []): TestResponse
    {
        return $this->request('POST', $path, $data);
    }

    protected function put(string $path, array $data = []): TestResponse
    {
        return $this->request('PUT', $path, $data);
    }

    protected function delete(string $path): TestResponse
    {
        return $this->request('DELETE', $path);
    }

    protected function request(string $method, string $path, array $data = []): TestResponse
    {
        $request = new Request(method: $method, uri: $path, body: $data);
        $this->lastResponse = Application::current()->handle($request);

        return new TestResponse($this->lastResponse);
    }

    protected function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            throw new \AssertionError($message ?: "Expected {$expected}, got {$actual}");
        }
    }

    protected function assertTrue(bool $condition, string $message = ''): void
    {
        if (!$condition) {
            throw new \AssertionError($message ?: "Expected true, got false");
        }
    }

    protected function assertFalse(bool $condition, string $message = ''): void
    {
        if ($condition) {
            throw new \AssertionError($message ?: "Expected false, got true");
        }
    }

    protected function assertNull(mixed $value, string $message = ''): void
    {
        if ($value !== null) {
            throw new \AssertionError($message ?: "Expected null, got " . var_export($value, true));
        }
    }

    protected function assertNotNull(mixed $value, string $message = ''): void
    {
        if ($value === null) {
            throw new \AssertionError($message ?: "Expected non-null");
        }
    }

    protected function assertDatabaseHas(string $table, array $attributes): void
    {
        $count = DB::table($table)->where(array_key_first($attributes), array_values($attributes)[0])->count();

        if ($count === 0) {
            throw new \AssertionError("Record not found in {$table}");
        }
    }

    protected function assertDatabaseMissing(string $table, array $attributes): void
    {
        $count = DB::table($table)->where(array_key_first($attributes), array_values($attributes)[0])->count();

        if ($count > 0) {
            throw new \AssertionError("Record found in {$table}, but should not exist");
        }
    }
}
