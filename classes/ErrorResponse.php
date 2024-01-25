<?php

class ErrorResponse
{

	public $data = null;

	public function __construct(public string $error)
	{

	}

}