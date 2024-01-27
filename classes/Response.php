<?php

/**
 * Response the class for the error-free response, the error property will be null
 */
class Response
{
	public $error;
	
	/**
	 * __construct
	 *
	 * @param  array $data
	 * @return void
	 */
	public function __construct(public array $data = [])
	{
	}

}