<?php

class Validation
{
	
	public function __construct(private Closure $validate, private string|Closure $error_message) 
	{}


	public function __invoke(array $request, ?int $id = null) : ?string
	{
		$validate = $this->validate;

		if (!$validate($request, $id)) {

			$message = $this->error_message;

			return is_string($message) ? $message : $message($request, $id);
		}
	
		return null;
	}

	static public function generateRequired(string|array $column) 
	{

		if (is_array($column)) {
			
			return array_map((__METHOD__)(...), $column);
			
		}

		
		$callable = function($request, $id) use ($column) {

			if (isset($id) && !array_keys($request, $column)) {
				
				return true;//the field is not sent but we are in update case so it is fine

			}

			return isset($request[$column]) && $request[$column] !== '';

		};


		return new Validation($callable, GetMessage::msg(Message::REQUIRED_FIELD, $column));
	}



	static public function generateMaxLength(string|array $column, int $length) 
	{
		
		$callable = function($request) use ($column, $length) {

			if (!isset($request[$column])) {
				
				return true;

			}

			return strlen($request[$column]) <= $length;

		};


		return new Validation($callable, GetMessage::msg(Message::MAX_LENGTH_EXCEEDED, $column, $length));
	}



	static public function generateValidDate(string $column) 
	{

		$callable = function($request) use ($column) {

			if (!isset($request[$column])) {
				
				return true;

			}

			return date_create_from_format(Env::$dateTimeFormat, $request[$column]);
		};


		return new Validation($callable, GetMessage::msg(Message::NOT_VALID_DATE_FORMAT, $column));

	}


	static public function generateValidRegex(string $column, string $regex) 
	{

		$callable = function($request) use ($column, $regex) {

			if (!isset($request[$column])) {
				
				return true;

			}

			return preg_match($regex, $request[$column]);

		};

		$format = substr($regex, 3, -2);

		return new Validation($callable, GetMessage::msg(Message::NOT_VALID_FORMAT, $column, $format));

	}


	static public function generateValidColor(string $column) 
	{
												//hex color regex taken ready from stack overflow, put seems ok
		return self::generateValidRegex($column, '/^#([0-9a-fA-F]{3}){1,2}$/');

	}


	static public function generateInList(string $column, array $alloweValues) 
	{

		$callable = function($request) use ($column, $alloweValues) {

			if (!isset($request[$column])) {
				
				return true;

			}

			return in_array($request[$column], $alloweValues);

		};


		return new Validation($callable, GetMessage::msg(Message::NOT_VALID_SELECTION, $column, implode(', ', $alloweValues)));

	}


}