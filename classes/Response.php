<?php

class Response
{

	public function __construct(public int $status_code = StatusCode::SUCCESS, public array $data = [], public array $errors = [])
	{

	}

	public function addError($error) 
	{

		$this->errors[] = $error;

	}
}