<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Orm
 * @version 0.3
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Orm\Traits;

use Exception;

trait Events
{
    /**
     * @var bool $allow_callbacks activer les évenements 
     */
	protected $allow_callbacks      = true;
	
    /**
     * @var bool $tmp_callbacks activer les évenements temporairement
     */
	protected $tmp_callbacks;

    /**
     * Permet de passer un array de la forme `$data = [ 'data' => [] ]` avant insertion
     * les callbacks doivent obligatoirement retourner $data
     * 
     * @var array
     */
    protected $beforeInsert         = [];

    /**
     * Permet de passer un array de la forme `$data = [ 'data' => [], 'where'    => ,'limit' =>, 'offset'    =>  ]`
     * avant recuperation les callbacks doivent obligatoirement retourner $data
     * 
     * @var array
     */
	protected $beforeFind           = [];

    /**
     * Permet de passer un array de la forme `$data = [ 'id' => $primaryKeyValue ]` avant suppression
     * les callbacks doivent obligatoirement retourner $data
     * 
     * @var array
     */
	protected $beforeDelete         = [];
    
    /**
     * Permet de passer un array de la forme `$data = [ 'id' =>  $primaryKeyValue, 'data' => [] ]` avant modification
     * 
     * @var array
     */
	protected $beforeUpdate         = [];
    
    /**
     * Permet de passer un array de la forme `$data = [ 'id' =>  $primaryKeyValue, 'data' => [] ]` après insertion
     * 
     * @var array
     */
	protected $afterInsert          = [];

    /**
     * Permet de passer un array de la forme `$data = [ 'data' => [ ModelObjectFetched ] ]` après recupération
     * les callbacks doivent obligatoirement retourner $data
     * 
     * @var array
     */
	protected $afterFind            = [];

    /**
     * Permet de passer un array de la forme `$data = [ 'id' => $primaryKeyValue, 'result'   => bool ]`
     * après suppression
     * 
     * @var array
     */
	protected $afterDelete          = [];
    
    /**
     * Permet de passer un array de la forme `$data = [ 'id' =>  $primaryKeyValue, 'data' => [], 'result'  => bool ]` 
     * après modification
     * 
     * @var array
     */
	protected $afterUpdate          = [];

    /**
     * Trigger events
     *
     * @param string $event
     * @param array $eventData
     * @return array
     */
    protected function trigger(string $event, array $eventData)
    {
        // Ensure it's a valid event
        if (! isset($this->{$event}) || empty($this->{$event}))
        {
            return $eventData;
        }

        foreach ($this->{$event} as $callback)
        {
            if (! method_exists($this, $callback))
            {
                throw new Exception(text("Db.undefinedMethod", [$callback , get_class($this)]));
            }

            $eventData = $this->{$callback}($eventData);
        }

        return $eventData;
    }

	/**
	 * Set the value of tmp_callbacks
	 *
	 * @return \Footup\Orm\BaseModel
	 */ 
	public function allowCallbacks($value = true)
	{
		$this->tmp_callbacks = $value;
		return $this;
	}
}