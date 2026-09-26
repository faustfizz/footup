<?php

declare(strict_types=1);

namespace Footup\Http;

/*
|--------------------------------------------------------------------------
| RedirectResponse — HTTP redirects with fluent interface
|
| Usage:
|   redirect('/dashboard')
|   redirect()->route('users.show', ['id' => 1])
|   redirect()->back()
|   redirect('/home')->with('message', 'Saved!')
|   redirect('/admin')->withFlash(['error' => 'Not authorized'])
|--------------------------------------------------------------------------
*/

class RedirectResponse extends Response
{
    protected array $flashData = [];

    public function __construct(string $location = '', int $status = 302)
    {
        parent::__construct('', $status);

        if ($location) {
            $this->to($location);
        }
    }

    /**
     * Redirect to a specific URL
     */
    public function to(string $location): self
    {
        $this->headers['Location'] = $location;

        return $this;
    }

    /**
     * Redirect to a named route
     * e.g. redirect()->route('users.show', ['id' => 1])
     */
    public function route(string $name, array $params = []): self
    {
        $url = route($name, $params);

        return $this->to($url);
    }

    /**
     * Redirect to the previous page (HTTP_REFERER)
     */
    public function back(): self
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';

        return $this->to($referer);
    }

    /**
     * Redirect to an external URL
     */
    public function away(string $url): self
    {
        return $this->to($url);
    }

    /**
     * Set HTTP status code (301, 302, 303, 307, etc.)
     * Default: 302 (Temporary Redirect)
     */
    public function status(int $code): self
    {
        $this->status = $code;

        return $this;
    }

    /**
     * Permanent redirect (301)
     */
    public function permanent(): self
    {
        $this->status = 301;

        return $this;
    }

    /**
     * Temporary redirect (302) — default
     */
    public function temporary(): self
    {
        $this->status = 302;

        return $this;
    }

    /**
     * See Other redirect (303) — use after POST to redirect to GET
     */
    public function seeOther(): self
    {
        $this->status = 303;

        return $this;
    }

    /**
     * Add flash data for the next request
     * e.g. redirect('/home')->with('message', 'Saved successfully!')
     */
    public function with(string $key, mixed $value): self
    {
        $this->flashData[$key] = $value;
        $this->storeFlash();

        return $this;
    }

    /**
     * Add multiple flash messages at once
     * e.g. redirect('/home')->withFlash(['error' => 'Failed', 'count' => 5])
     */
    public function withFlash(array $data): self
    {
        $this->flashData = array_merge($this->flashData, $data);
        $this->storeFlash();

        return $this;
    }

    /**
     * Flash validation errors
     * e.g. redirect()->back()->withErrors($validator->errors())
     */
    public function withErrors(array $errors): self
    {
        return $this->with('errors', $errors);
    }

    /**
     * Flash input data (for repopulating forms)
     * e.g. redirect()->back()->withInput($request->all())
     */
    public function withInput(array $input): self
    {
        return $this->with('_old_input', $input);
    }

    protected function storeFlash(): void
    {
        session_start();
        $_SESSION['_flash'] = $this->flashData;
    }

    public function send(): void
    {
        parent::send();

        if (!headers_sent()) {
            exit(0);
        }
    }
}
