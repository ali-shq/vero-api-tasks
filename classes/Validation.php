<?php

/**
 * Validation a class used to validate records, by a custom provided closure
 * in case of failure a custom message is given back.
 * The class contains a number of static functions also that generate pre-defined Validation objects
 * for common use cases like required field or regex validations
 */
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
	 * calling of the validation, the validate Closure is called on the record
	 * if no error is found null is returned, otherwise a string with the error
	 * message is returned.
	 * The message itself can be a Closure called on the record or a predefined string
	 *
	 * @param  array $record
	 * @param  int $id
	 * @return string|null
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
	 * @param  string|array $field the field(s) that is/are required to be not null and not empty strings
	 * @return Validation|array the Validation(s) that validate that the field(s) have values
	 * 
	 */
	static public function generateRequired(string|array $field) :array|Validation 
	{

		if (is_array($field)) {
			
			return array_map((__METHOD__)(...), $field);
			
		}

		
		$callable = function($record, $id) use ($field) {

			if (isset($id) && !array_key_exists($field, $record)) {
				
				return true;//the field is not sent but we are in update case so it is fine

			}

			return isset($record[$field]) && $record[$field] !== '';

		};


		return new Validation($callable, GetMessage::msg(Message::REQUIRED_FIELD, $field));
	}


	
	/**
	 * generateMaxLength
	 *
	 * @param  string $field the field whose length we are setting max-length to
	 * @param  int $length the max allowed length
	 * @return Validation for the specifications above
	 */
	static public function generateMaxLength(string $field, int $length) : Validation
	{
		
		$callable = function($record) use ($field, $length) {

			if (!isset($record[$field])) {
				
				return true;

			}

			return strlen($record[$field]) <= $length;

		};


		return new Validation($callable, GetMessage::msg(Message::MAX_LENGTH_EXCEEDED, $field, $length));
	}


		
	/**
	 * generateValidDate
	 *
	 * @param  string $field the date field in the request
	 * @return Validation that ensures that the date complies with the format Env::$dateTimeFormat
	 */
	static public function generateValidDate(string $field) : Validation
	{

		$callable = function($record) use ($field) {

			if (!isset($record[$field])) {
				
				return true;

			}

			return date_create_from_format(Env::$dateTimeFormat, $record[$field]);
		};


		return new Validation($callable, GetMessage::msg(Message::NOT_VALID_DATE_FORMAT, $field));

	}



	
	/**
	 * generateValidRegex
	 *
	 * @param  mixed $field the field to be validated against the regex
	 * @param  mixed $regex the regex that should be statisfied
	 * @return Validation the validation testing the given field for the regex
	 */
	static public function generateValidRegex(string $field, string $regex) : Validation
	{

		$callable = function($record) use ($field, $regex) {

			if (!isset($record[$field])) {
				
				return true;

			}

			return preg_match($regex, $record[$field]);

		};

		$format = substr($regex, 3, -2);

		return new Validation($callable, GetMessage::msg(Message::NOT_VALID_FORMAT, $field, $format));

	}

	
	/**
	 * generateValidColor
	 *
	 * @param  mixed $field the color field name
	 * @return Validation the validation testing whether the data is a valid hex color
	 */
	static public function generateValidColor(string $field) : Validation
	{
												//hex color regex taken ready from stack overflow, put seems ok
		return self::generateValidRegex($field, '/^#([0-9a-fA-F]{3}){1,2}$/');

	}

	
	/**
	 * generateInList
	 *
	 * @param  mixed $field the column whose value will be tested
	 * @param  mixed $alloweValues the list of allowed values
	 * @return Validation the validation testing that the column's value is contained in the list
	 */
	static public function generateInList(string $field, array $alloweValues) : Validation
	{

		$callable = function($record) use ($field, $alloweValues) {

			if (!isset($record[$field])) {
				
				return true;

			}

			return in_array($record[$field], $alloweValues);

		};


		return new Validation($callable, GetMessage::msg(Message::NOT_VALID_SELECTION, $field, implode(', ', $alloweValues)));

	}


}