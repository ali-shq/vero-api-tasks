<?php

class GetMessage
{


	static function msg(string $string, ...$values) 
	{

		return sprintf($string, $values);

	}

}