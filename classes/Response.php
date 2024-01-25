<?php

class Response
{
	public $error;

	public function __construct(public array $data = [])
	{
	}

}