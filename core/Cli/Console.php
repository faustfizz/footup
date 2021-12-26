<?php

/**
 * FOOTUP - 0.1.4 - 12.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Cli
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 * @license MIT
 */
namespace Footup\Cli;

use Footup\Controller;

class Console extends CLI
{

    /**
     * Register options and arguments on the given $options object
     *
     * @param Options $options
     * @return void
     */
    protected function setup(Options $options)
    {
        $options->setHelp(
            "*************************** FOOTUP *******************************\n".
            "**          Command Line Generator for FOOTUP Framework         **\n".
            "*************************** ****** *******************************\n"
        );
        $options->setCommandHelp("View all commands and Options below:");

        $options->registerOption('version', 'Print version', 'v');
        $options->registerCommand('make', 'Generate Controller and/or Model and/or Middle for your app');
        $options->registerCommand('make:scaffold', "Generate Controller, Model, Middle, View and Assets for your app with one command\neg: php foot make:scaffold name");
        $options->registerCommand('make:controller', 'Generate Controller for your app');
        $options->registerCommand('make:model', 'Generate Model for your app');
        $options->registerCommand('make:middle', 'Generate Middle for your app');

        $options->registerOption('scaffold', 'Generate Controller, Model, Middle, View and Assets for your app with one command', 's', 'name', "make");
        $options->registerOption('controller', 'Generate Controller for your app', 'c', 'name', "make");
        $options->registerOption('model', 'Generate Model for your app', 'm', 'name', "make");
        $options->registerOption('middle', 'Generate Middle for your app', 'w', 'name', "make");

        $options->registerArgument('name', 'The name of the controller', true, 'make:controller');
        $options->registerArgument('name', 'The name of the model', true, 'make:model');
        $options->registerArgument('name', 'The name of the middle', true, 'make:middle');
        $options->registerArgument('name', 'The name of the middle', true, 'make:scaffold');

        $options->registerCommand('compact', 'Display the help text in a more compact manner');
    }

    /**
     * Your main program
     *
     * Arguments and options have been parsed when this is run
     *
     * @param Options $options
     * @return void
     */
    protected function main(Options $options)
    {
        if ($options->getOpt('version')) {
            $this->info('Foot v0.0.1 / FOOTUP v0.1.4');
            exit;
        }

        switch ($options->getCmd()) {
            case 'make':
                if(!$options->getOpt("controller") && !$options->getOpt("model") && !$options->getOpt("middle") && !$options->getOpt("scaffold"))
                {
                    $this->error("You can't call the make command without option !");
                    exit;
                }

                $log = array();

                if($options->getOpt("controller"))
                {
                    $class = $options->getOpt("controller");
                    $generator = new Generator($class);
                    $log = array_merge($log, $generator->genController());
                }

                if($options->getOpt("model"))
                {
                    $class = $options->getOpt("model");
                    $generator = new Generator($class);
                    $log = array_merge($log, $generator->genModel());
                }

                if($options->getOpt("middle"))
                {
                    $class = $options->getOpt("middle");
                    $generator = new Generator($class);
                    $log = array_merge($log, $generator->genMiddle());
                }

                if($options->getOpt("scaffold"))
                {
                    $class = $options->getOpt("scaffold");
                    $generator = new Generator($class);
                    $log = array_merge($log, $generator->scaffold());
                }

                $this->info("All generations :");
                foreach (array_filter($log) as $value) {
                    # code...
                    $this->success($value);
                }
                exit;
            case 'make:scaffold':
                if(count($options->getArgs()) >= 1)
                {
                    $log = array();

                    $n = trim($options->getArgs()[0]);

                    if(!empty($n)){
                        $generator = new Generator($n);
                        $log = array_merge($log, $generator->scaffold());
                    }else{
                        $this->error("You can't call the command make:scaffold without specifying a name");
                        exit;
                    }

                    $this->info("All generations :");
                    foreach (array_filter($log) as $value) {
                        # code...
                        $this->success($value);
                    }

                    exit;
                }
                $this->error('You must specify a name of the class to generate !');
                exit;
            case 'make:controller':
                if(count($options->getArgs()) >= 1)
                {
                    $log = array();

                    $n = trim($options->getArgs()[0]);

                    if(!empty($n)){
                        $generator = new Generator($n);
                        $log = array_merge($log, $generator->genController());
                    }else{
                        $this->error("You can't call the command make:controller without specifying a name");
                        exit;
                    }

                    $this->info("All generations :");
                    foreach (array_filter($log) as $value) {
                        # code...
                        $this->success($value);
                    }

                    exit;
                }
                $this->error('You must specify a name of the class to generate !');
                exit;
            case 'make:model':
                if(count($options->getArgs()) >= 1)
                {
                    $log = array();

                    $n = trim($options->getArgs()[0]);

                    if(!empty($n)){
                        $generator = new Generator($n);
                        $log = array_merge($log, $generator->genModel());
                    }else{
                        $this->error("You can't call the command make:model without specifying a name");
                        exit;
                    }

                    $this->info("All generations :");
                    foreach (array_filter($log) as $value) {
                        # code...
                        $this->success($value);
                    }

                    exit;
                }
                $this->error('You must specify a name of the class to generate !');
                exit;
            case 'make:middle':
                if(count($options->getArgs()) >= 1)
                {
                    $log = array();

                    $n = trim($options->getArgs()[0]);

                    if(!empty($n)){
                        $generator = new Generator($n);
                        $log = array_merge($log, $generator->genMiddle());
                    }else{
                        $this->error("You can't call the command make:middle without specifying a name");
                        exit;
                    }

                    $this->info("All generations :");
                    foreach (array_filter($log) as $value) {
                        # code...
                        $this->success($value);
                    }

                    exit;
                }
                $this->error('You must specify a name of the class to generate !');
                exit;
            case 'compact':
                $options->useCompactHelp();
                echo $options->help();
                exit;
            default:
                $this->error('No known command was called, we show the default help instead:');
                echo $options->help();
                exit;
        }

    }
}
