<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Http
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Http;

use Footup\Utils\Shared;

class RedirectResponse extends Response
{
    
    /**
     * @param string $uri
     * @param string $method
     * @param integer $status
     */
    public function __construct($status = 302) {
        parent::__construct('php://memory', $status);
        $this->redirecting = true;
    }

    /**
     * Sets the URI to redirect to and, optionally, the HTTP status code to use.
     * If no code is provided it will be automatically determined.
     *
     * @param string       $uri    The URI to redirect to
     * @param integer|null $code   HTTP status code
     * @param string       $method
     *
     * @return self
     */
    public function to(string $uri, $code = 302, string $method = 'auto'){
        return $this->redirect($uri, $method, $code);
    }

    /**
     * Helper function to return to previous page.
     *
     * Example:
     *  return redirect()->back();
     *
     * @param integer|null $code
     * @param string       $method
     *
     * @return self
     */
    public function back($code = 302, string $method = 'auto')
    {
        return $this->redirect(previous_url(), $method, $code);
    }

    /**
     * Specifies that the current $_GET and $_POST arrays should be
     * packaged up with the response.
     *
     * It will then be available via the 'old()' helper function.
     *
     * @return self
     */
    public function withInput()
    {
        $session = Shared::loadSession();

        $session->setFlash('$_GET', $_GET ?? []);
        $session->setFlash('$_POST', $_POST ?? []);

        return $this;
    }

    /**
     * Adds a key and message to the session as Flashdata.
     *
     * @param string       $key
     * @param string|array $message
     *
     * @return $this
     */
    public function with(string $key, $message)
    {
        $session = Shared::loadSession();
        $session->setFlash($key, $message);

        return $this;
    }
}
