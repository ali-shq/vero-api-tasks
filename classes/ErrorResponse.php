<?php

/**
 * ErrorResponse the class for has error response, the data property will be null
 */
class ErrorResponse
{

	public $data = null;
	
	/**
	 * __construct
	 *
	 * @param  string $error the error message
	 * @return void
	 */
	public function __construct(public string $error)
	{

	}

}