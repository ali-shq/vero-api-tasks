<?php

class Utils
{
			
	
	static function snakeCase(array|string $str) : string|array 
	{
		
		if (is_array($str)) {
			
			return array_map((__METHOD__)(...), $str);
			
		}


		$new_str = '';

		$chars = mb_str_split($str);

		foreach ($chars as $ind => $char) {

			$lower_case = strtolower($char);

			$new_str .= ($ind != 0 && $char != $lower_case ? "_$lower_case" : $lower_case);

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

	static function captalize(string $str) : string 
	{
		$chars = mb_str_split($str);
		$chars[0] = mb_strtoupper($chars[0]);
		return implode('', $chars);
	}

	
	static function echoJson($data) 
	{
		echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);	
	}


	static function standartDateTime(?string $datetime) 
	{


		if (!$datetime) {

			return null;

		}

		$format = Env::$dateTimeFormat;//or DATE_ATOM if we will leave it ISO

		$dateTime =	date_create_from_format($format, $datetime);

		return $dateTime ? $dateTime->format($format) : date($format, strtotime($datetime));

	}


	static function dateDiffInHours(?string $endDateTime, ?string $startDateTime, string $dateFormat = null) : ?int
	{
		if ($endDateTime === null || $startDateTime === null) {
			
			return null;
			
		}
		
		
		if ($dateFormat === null) {
			
			$dateFormat = Env::$dateTimeFormat;
			
		}


		$diff = date_diff(date_create_from_format($dateFormat, $endDateTime), date_create_from_format($dateFormat, $startDateTime));

		return ($diff->days * 24 + $diff->h);
	}

}