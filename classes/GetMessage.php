<?php

class GetMessage
{


	static function msg(string $string, ...$values) 
	{

		return sprintf($string, ...$values);

	}

	static function getServerErrorMessage(Throwable $e) 
	{
		$development_msg = $e->getMessage()."\nfile:".$e->getFile()."\nline:".$e->getLine()
		."\n\n".$e->getTraceAsString();

		return Env::$is_deveplopment ? $development_msg : Message::SERVER_ERROR;

	}

}