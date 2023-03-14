<?php

namespace App\Config;

class Paginator
{
	/**
	 * --------------------------------------------------------------------------
	 * Templates Names located at core/Paginator/Views
	 * --------------------------------------------------------------------------
	 *
	 * Pagination links are rendered out using views to configure their
	 * appearance. 
	 *
	 * Within each view, the Pager object will be available as $paginator
	 *
	 * @var array<string>
	 */
	public $templates = [
		'default' // default.php
	];

	/**
	 * --------------------------------------------------------------------------
	 * Items Per Page
	 * --------------------------------------------------------------------------
	 *
	 * The default number of results shown in a single page.
	 *
	 * @var integer
	 */
	public $perPage = 20;

	/**
     * Default value for query string key to specify the current page.
     *
     * https://example.com?page=1
	 *
	 * @var string
	 */
	public $pageName = "page";

	/**
     * items on each side
	 *
	 * @var integer
	 */
	public $onEachSide = 1;

	public function __construct(array $config = [])
	{
		if(!empty($config))
		{
			foreach($config as $key => $val)
			{
				if(property_exists($this, $key))
				{
					$this->{$key} = $val;
				}
			}
		}
	}
	

}
