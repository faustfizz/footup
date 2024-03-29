<?php
/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup
 * @version 0.1.3
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup;

use Exception;
use Footup\Http\Request;
use Footup\Http\Response;
use Footup\Http\Session;
use Footup\Utils\Shared;
use Locale;

class Controller
{
    /**
     * @var array [ \Footup\Routing\Middle ] or [ controllerMethod => \Footup\Routing\Middle ]
     */
    protected $globalMiddles = [];

    /**
     * @var array [ \App\Controller\Home => \Footup\Routing\Middle ] or [ \App\Controller\Home => 
     *                                                                 [ controllerMethod => \Footup\Routing\Middle ]
     *                                                                  ]
     */
    protected $middles = [];

    /**
     * @var Request
     */
    protected $request = null;

    /**
     * @var Response
     */
    protected $response = null;

    /**
     * @var Session
     */
    protected $session = null;

    public function __construct()
    {
        $this->session = Shared::loadSession();
    }

    /**
     * Controller constructor.
     * @param Request $request
     * @param Response $response
     * 
     * @return $this
     */
    public function __boot(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        /**
         * Default lang
         */
        if (function_exists("setlocale")) {
            \setlocale(LC_ALL, $this->request->getLang());
        }
        if (class_exists("Locale")) {
            Locale::setDefault($this->request->getLang());
        }

        return $this;
    }

    /**
     * @param array $data
     * @param integer $status
     * @param array $headers
     * 
     * @return Response|void
     */
    protected function json($data = [], $status = 200, $headers = [])
    {
        return $this->response->json($data, $status, $headers);
    }

    /**
     * Render a view
     *
     * @param string $path
     * @param array|object $data
     * @param string $ext
     * @return Response|void
     */
    function view($path, $data = null, $ext = VIEW_EXT)
    {
        extract($data);
        $path = trim($path, "/");

        if (!file_exists(VIEW_PATH . $path . "." . $ext)) {
            throw new Exception(text("View.missedFile", [$path . "." . $ext]));
        }
        ob_start();
        include_once(VIEW_PATH . $path . "." . $ext);
        $body = ob_get_clean();
        return $this->response->body($body)->send();
    }


    /**
     * Get [ \Footup\Routing\Middle ] or [ controllerMethod => \Footup\Routing\Middle ]
     *
     * @param string|null $index
     * @return  string|array
     */
    public function getGlobalMiddles($index)
    {
        return $this->globalMiddles[$index] ?? $this->globalMiddles;
    }

    /**
     * Get [ \App\Controller\Home => \Footup\Routing\Middle ] or [ \App\Controller\Home => 
     *                                                               [ controllerMethod => \Footup\Routing\Middle ]
     *                                                           ]
     *
     * @param  string|null $index
     * @return  array|string|null
     */
    public function getMiddles($index)
    {
        return $this->middles[$index] ?? null;
    }
}