<?php

class Validation
{
		
	/**
	 *
	 * @param  Closure $validate the function that will validate the record
	 * @param  string|Closure $errorMessage the error message given in case of error
	 */
	public function __construct(private Closure $validate, private string|Closure $errorMessage) 
	{}

	
	/**
	 * __invoke
	 *
	 * @param  array $record
	 * @param  int $id
	 * @return string|null
	 * 
	 * calling of the validation, the isValid Closure is called on the record
	 * if no error is found null is returned, otherwise a string with the error
	 * message is returned.
	 * The message itself can be a Closure called on the record or a predefined string
	 */
	public function __invoke(array $record, ?int $id = null) : ?string
	{
		$validate = $this->validate;

		if (!$validate($record, $id)) {

			$message = $this->errorMessage;

			return is_string($message) ? $message : $message($record, $id);
		}
	
		return null;
	}

	
		
	/**
	 * generateRequired
	 *
	 * @param  string|array $column the field(s) that is/are required to be not null and not empty strings
	 * @return Validation|array the Validation(s) that validate that the field(s) have values
	 * 
	 */
	static public function generateRequired(string|array $column) :array|Validation 
	{

		if (is_array($column)) {
			
			return array_map((__METHOD__)(...), $column);
			
		}

		
		$callable = function($record, $id) use ($column) {

			if (isset($id) && !array_key_exists($column, $record)) {
				
				return true;//the field is not sent but we are in update case so it is fine

			}

			return isset($record[$column]) && $record[$column] !== '';

		};


		return new Validation($callable, GetMessage::msg(Message::REQUIRED_FIELD, $column));
	}


	
	/**
	 * generateMaxLength
	 *
	 * @param  string $column the field whose length we are setting max-length to
	 * @param  int $length the max allowed length
	 * @return Validation for the specifications above
	 */
	static public function generateMaxLength(string $column, int $length) : Validation
	{
		
		$callable = function($record) use ($column, $length) {

			if (!isset($record[$column])) {
				
				return true;

			}

			return strlen($record[$column]) <= $length;

		};


		return new Validation($callable, GetMessage::msg(Message::MAX_LENGTH_EXCEEDED, $column, $length));
	}


		
	/**
	 * generateValidDate
	 *
	 * @param  string $column the date field in the request
	 * @return Validation that ensures that the date complies with the format Env::$dateTimeFormat
	 */
	static public function generateValidDate(string $column) : Validation
	{

		$callable = function($record) use ($column) {

			if (!isset($record[$column])) {
				
				return true;

			}

			return date_create_from_format(Env::$dateTimeFormat, $record[$column]);
		};


		return new Validation($callable, GetMessage::msg(Message::NOT_VALID_DATE_FORMAT, $column));

	}



	
	/**
	 * generateValidRegex
	 *
	 * @param  mixed $column the field to be validated against the regex
	 * @param  mixed $regex the regex that should be statisfied
	 * @return Validation the validation testing the given field for the regex
	 */
	static public function generateValidRegex(string $column, string $regex) : Validation
	{

		$callable = function($record) use ($column, $regex) {

			if (!isset($record[$column])) {
				
				return true;

			}

			return preg_match($regex, $record[$column]);

		};

		$format = substr($regex, 3, -2);

		return new Validation($callable, GetMessage::msg(Message::NOT_VALID_FORMAT, $column, $format));

	}

	
	/**
	 * generateValidColor
	 *
	 * @param  mixed $column the color field name
	 * @return Validation the validation testing whether the data is a valid hex color
	 */
	static public function generateValidColor(string $column) : Validation
	{
												//hex color regex taken ready from stack overflow, put seems ok
		return self::generateValidRegex($column, '/^#([0-9a-fA-F]{3}){1,2}$/');

	}

	
	/**
	 * generateInList
	 *
	 * @param  mixed $column the column whose value will be tested
	 * @param  mixed $alloweValues the list of allowed values
	 * @return Validation the validation testing that the column's value is contained in the list
	 */
	static public function generateInList(string $column, array $alloweValues) : Validation
	{

		$callable = function($record) use ($column, $alloweValues) {

			if (!isset($record[$column])) {
				
				return true;

			}

			return in_array($record[$column], $alloweValues);

		};


		return new Validation($callable, GetMessage::msg(Message::NOT_VALID_SELECTION, $column, implode(', ', $alloweValues)));

	}


}