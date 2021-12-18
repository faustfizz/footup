<?php
/**
 * FOOTUP - 0.1.3 - 11.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup
 * @version 0.1.3
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup;
use App\Config\Config;
use Footup\Http\Request;
use Footup\Http\Response;
use Footup\Http\Session;

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
     * @var \Footup\Http\Request
     */
    protected $request = null;

    /**
     * @var \Footup\Http\Response
     */
    protected $response = null;

    /**
     * @var \Footup\Http\Session
     */
    protected $session = null;

    public function __construct()
    {
        $this->request  = new Request();
        $this->response = new Response();
        $this->session  = new Session();
    }

    /**
     * Controller constructor.
     * @param \Footup\Http\Request $request
     * @param \Footup\Http\Response $response
     * @param \Footup\Http\Session $session
     * @return $this
     */
    public function __boot(\Footup\Http\Request $request, \Footup\Http\Response $response = null, \Footup\Http\Session $session = null)
    {
        $this->request  = $request;
        $this->response = is_null($response) ? $this->response : $response;
        $this->session  = is_null($session) ? $this->session : $session;
        return $this;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function json($data)
    {
        return json_encode($data);
    }

    function view( $path , $data = null )
    {
        extract($data);
        $path = trim($path, "/");
        $config = new Config();
        return include_once($config->view_path . $path . $config->view_ext);
    }


    /**
     * Get [ \Footup\Routing\Middle ] or [ controllerMethod => \Footup\Routing\Middle ]
     *
     * @param string|null $index
     * @return  string|array
     */ 
    public function getGlobalMiddles($index = null)
    {
        return isset($this->globalMiddles[$index]) ? $this->globalMiddles[$index] : $this->globalMiddles;
    }

    /**
     * Get [ \App\Controller\Home => \Footup\Routing\Middle ] or [ \App\Controller\Home => 
     *                                                               [ controllerMethod => \Footup\Routing\Middle ]
     *                                                           ]
     *
     * @param  string|null $index
     * @return  array|string|null
     */ 
    public function getMiddles($index = null)
    {
        return isset($this->middles[$index]) ? $this->middles[$index] : null;
    }
}