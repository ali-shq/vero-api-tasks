<?php


/**
 * Utils a collection of util functions
 */
class Utils
{
			
		
	/**
	 * snakeCase
	 *
	 * @param  string|array $str the string or array of strings to be converted to snake_case
	 * @return string|array the resulting string or array of strings
	 */
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

		
	/**
	 * dd echo as json (dump) and die
	 *
	 * @param  mixed $data the variadic to be json echo-ed
	 * @return void
	 */
	static function dd(...$data) 
	{
		foreach ($data as $row) {

			echo json_encode($row)."\n";

		}


		die();

	}
	
	
	/**
	 * vd var_dump and die
	 *
	 * @param  mixed $data the variadic to be var_dump-ed
	 * @return void
	 */
	static function vd(...$data) : void
	{
		var_dump(...$data);

		die();

	}
	
	/**
	 * captalize
	 *
	 * @param  string $str the string whose first character will be capitalized
	 * @return string the resulting string
	 */
	static function captalize(string $str) : string 
	{
		$chars = mb_str_split($str);
		$chars[0] = mb_strtoupper($chars[0]);
		return implode('', $chars);
	}

		
	/**
	 * echoJson echo the json encoded data
	 *
	 * @param  mixed $data 
	 * @return void
	 */
	static function echoJson($data) : void
	{
		echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);	
	}

	
	/**
	 * standartDateTime return a strandart formated datetime string, if the provided
	 * string is not of the standart format (as defined in the Env::$dateTimeFormat) 
	 * the strtotime function will be used to convert it to datetime before returning
	 * the same format back
	 * null will be returned for a null input
	 * 
	 * @param  ?string $datetime the datetime string whose format will be checked
	 * @return ?string
	 */
	static function standartDateTime(?string $datetime) : ?string
	{


		if (!$datetime) {

			return null;

		}

		$format = Env::$dateTimeFormat;//or DATE_ATOM if we will leave it ISO

		$dateTime =	date_create_from_format($format, $datetime);

		return $dateTime ? $dateTime->format($format) : date($format, strtotime($datetime));

	}

	
	/**
	 * dateDiffInHours returns the difference in hours between two dates, only full days and hours 
	 * are included, minutes and seconds in the difference are ignored
	 * null will be returned if either $endDateTime or $startDateTime is null
	 *
	 * @param  ?string $endDateTime
	 * @param  ?string $startDateTime
	 * @param  ?string $dateFormat the date-format the $endDateTime and $startDateTime are of, if left null
	 * the Env::$dateTimeFormat will be used
	 * @return int
	 */
	static function dateDiffInHours(?string $endDateTime, ?string $startDateTime, ?string $dateFormat = null) : ?int
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