<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Footup\Http\Request;
use Footup\Http\Response;
use Footup\Routing\Controller;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->welcome();
    }

    // Demonstrates the View layer: Core\View was injected into the base
    // Controller's constructor automatically because it's bound in the
    // container below, so $this->view() is just there to use.
    public function welcome()
    { 
        return Contact::whereIdcont(6)->first()->formFor();
        return $this->view('home', [
            'title' => 'It works!',
            'items' => ['Container', 'Router', 'Middleware', 'View', 'ExceptionHandler'],
        ]);
    }
}