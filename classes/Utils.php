<?php

class Utils
{
			
	/**
	 * snakeCase
	 *
	 * @param  mixed $str
	 * @return string
	 */
	static function snakeCase(array|string $str) : string|array 
	{
		
		if (is_array($str)) {
			
			return array_map((__METHOD__)(...), $str);
			
		}


		$new_str = '';

		$chars = mb_str_split($str);

		foreach ($chars as $char) {

			$lower_case = strtolower($char);

			$new_str .= ($char != $lower_case ? "_$lower_case" : $char);

		}


		return $new_str;

	}

	static function dd(...$data) 
	{
		foreach ($data as $row) {

			echo json_encode($row)."\n";

		}


		die();

	}

	static function vd(...$data) 
	{
		var_dump(...$data);

		die();

	}

	

}