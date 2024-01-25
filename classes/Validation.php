<?php


class Validation
{
	
	private function __construct(private \callable|Closure $validate, private string|\callable|Closure $error_message) 
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

	static public function generateMaxLength(string $column, Model $model) 
	{}

	static public function generateValidDate(string $column, Model $model) 
	{}

	static public function generateInList(string $column, array $alloweValues, Model $model) 
	{}


}