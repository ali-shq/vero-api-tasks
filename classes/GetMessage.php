<?php

/**
 * a helper class that prepares the error messages
 */
class GetMessage
{

	
	/**
	 * msg 
	 * prepares the non-server error messages, i.e. error messages not associated with a server error
	 *
	 * @param  string $string
	 * @param  mixed $values variadic string values
	 * @return string
	 */
	static function msg(string $string, ...$values) : string
	{

		return sprintf($string, ...$values);

	}
	
	/**
	 * getServerErrorMessage
	 * prepares the server error messages, i.e. error messages associated with a server error
	 * if the Env::$isDeveplopment is set to false only a generic message with be displied as defined in 
	 * Message::SERVER_ERROR, otherwise if the Env::$isDevelopment is set to true a more complete message will
	 * be shown
	 * @param  Throwable $e
	 * @return string
	 */
	static function getServerErrorMessage(Throwable $e) : string 
	{
		$developmentMsg = $e->getMessage()."\nfile:".$e->getFile()."\nline:".$e->getLine()
		."\n\n".$e->getTraceAsString();

		return Env::$isDeveplopment ? $developmentMsg : Message::SERVER_ERROR;

	}

}