<?php
/**
 * FOOTUP - 0.1.3 - 11.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup;
use Footup\Orm\BaseModel;

class Model extends BaseModel{
    /**
     * Permet de passer un array de la forme $data = [ 'data' => [] ] avant insertion
     * les callbacks doivent obligatoirement retourner $data
     * 
     * @var array
     */
    protected $beforeInsert         = [];

    /**
     * Permet de passer un array de la forme $data = [ 'data' => [], 'where'    => ,'limit' =>, 'offset'    =>  ] 
     * avant recuperation
     * les callbacks doivent obligatoirement retourner $data
     * 
     * @var array
     */
	protected $beforeFind           = ["beforeFind"];

    /**
     * Permet de passer un array de la forme $data = [ 'id' => $primaryKeyValue ] avant suppression
     * les callbacks doivent obligatoirement retourner $data
     * 
     * @var array
     */
	protected $beforeDelete         = [];
    
    /**
     * Permet de passer un array de la forme $data = [ 'id' =>  $primaryKeyValue, 'data' => [] ] avant modification
     * 
     * @var array
     */
	protected $beforeUpdate         = [];
    
    /**
     * Permet de passer un array de la forme $data = [ 'id' =>  $primaryKeyValue, 'data' => [] ] après insertion
     * 
     * @var array
     */
	protected $afterInsert          = [];

    /**
     * Permet de passer un array de la forme $data = [ 'data' => [ ModelObjectFetched ] ] après recupération
     * les callbacks doivent obligatoirement retourner $data
     * 
     * @var array
     */
	protected $afterFind            = [];

    /**
     * Permet de passer un array de la forme $data = [ 'id' => $primaryKeyValue, 'result'   => bool ] 
     * après suppression
     * 
     * @var array
     */
	protected $afterDelete          = [];
    
    /**
     * Permet de passer un array de la forme $data = [ 'id' =>  $primaryKeyValue, 'data' => [], 'result'  => bool ] 
     * après modification
     * 
     * @var array
     */
	protected $afterUpdate          = [];
}