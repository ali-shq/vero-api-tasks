<?php

abstract class Model
{

	private $tableName;

	private $allColumnSet;


	public $allProperties = [];

	public $id = 'id';



	protected $overWriteGetValues = [];

	protected $defaultValues = [];

	protected $validations = [];



	const ALL_COLUMNS = '*';

	const ALL_OPERATORS = ['>=', '<', '>', '<=', '!='];


	public function __construct()
	{
		$this->allProperties[] = $this->id;

		$this->allColumnSet = array_flip(array_map(Utils::snakeCase(...), $this->allProperties));

		$this->tableName = Utils::snakeCase(str_replace('Model', '', get_class($this)));
	}



	public function get($where = [], ?array $columns = null): array
	{
		$params = [];

		$query = 'select ' . $this->prepareColumns($columns) . ' from "' . $this->tableName . '"' . $this->prepareWhere($where, $params);

		$data = Database::execQuery($query, $params);

		return $this->translateDBData($data);
	}



	public function getById(int $id) : array 
	{

		$data = $this->get([$this->id => $id]);

		if ($data == []) {

			throw new RequestError(GetMessage::msg(Message::NOT_FOUND_ERROR, $id, self::getResourceName($this)), StatusCode::NOT_FOUND_ERROR);

		}

		return $data;

	}



	public function update(array $request, int $id): array
	{

		unset($request[$this->id]);

		$this->checkValidations($request, $id);


		if ($request == []) {

			throw new RequestError(GetMessage::msg(Message::EMPTY_UPDATE, self::getResourceName($this)));

		}


		$params = [];

		$query = $this->prepareUpdate($request, $id, $params);

		$data = $this->translateDBData(Database::execQuery($query, $params));

		if ($data == []) {

			throw new RequestError(GetMessage::msg(Message::NOT_FOUND_ERROR, $id, self::getResourceName($this)), StatusCode::NOT_FOUND_ERROR);

		}

		return $data;

		//return $this->getById($id);
	}


	static function getResourceName (Model $model) 
	{

		return str_replace('model', '', strtolower(get_class($model)));

	}


	protected function prepareUpdate(array $request, int $id, array &$params = []): string
	{

		$query = 'update "'.$this->tableName.'" set ';


		foreach ($request as $key => $value) {

			$col = Utils::snakeCase($key);

			$this->ensureColumnExists($col);

			$query .= "\"$col\" = ";

			Database::addParam($query, $value, $params);

			$query .= ',';
		}

		$query = rtrim($query, ',') . " where $this->id = ";
		
		Database::addParam($query, $id, $params);
		
		return $query.' returning *';

	}


	protected function prepareInsert(array $request, &$params = []): string 
	{

		
		$query = 'insert into "'.$this->tableName.'" ';


		if ($request == []) {

			return $query . 'DEFAULT VALUES';

		}

		$query .= '('.$this->prepareColumns(array_keys($request)).') VALUES(';


		foreach ($request as $value) {

			Database::addParam($query, $value, $params);

			$query .= ',';
		}

		$query = rtrim($query, ',').') returning *';
		
		return $query;
	}


	public function deleteById(int $id): void
	{

		$where = [$this->id => $id];

		$this->delete($where);

	}



	public function delete(array $where): void
	{

		$params = [];

		$query = 'delete from "'.$this->tableName.'" '.$this->prepareWhere($where, $params);

		Database::execQuery($query, $params);

	}


	public function insert(array $request) : array
	{
		
		$request += $this->defaultValues;
	
		$this->checkValidations($request);

		unset($request[$this->id]);

		$params = [];

		$query = $this->prepareInsert($request, $params);

		return $this->translateDBData(Database::execQuery($query, $params));

		//return $this->getById(Database::getLastInsertId());
	}



	protected function checkValidations(array $request, ?int $id = null) 
	{
		$error_count = 0;

		$error_message = '';
		
		foreach ($this->validations as $validation) {

			$validation_error = $validation($request, $id);

			if ($validation_error === null) {

				continue;

			}

			$error_count++;

			$error_message .= $validation_error."\n";

		}



		if ($error_count) {

			throw new ValidationError(GetMessage::msg(Message::VALIDATION_ERROR, $error_count)."\n".$error_message);
		}

	}

	protected function ensureColumnExists(string $column, string $msg = Message::BAD_COLUMN, int $code = StatusCode::BAD_COLUMN) 
	{
			
		if (!isset($this->allColumnSet[$column])) {

			throw new ServerError(GetMessage::msg($msg, $column, get_class($this)), $code);

		}
	}

	protected function prepareColumns(?array $columns): string
	{

		if ($columns == null) {

			return self::ALL_COLUMNS;
		}

		$column_sql = '';

		foreach ($columns as $column) {

			$col = Utils::snakeCase($column);

			$this->ensureColumnExists($col);

			$column_sql .= "\"$col\", ";
		}

		return rtrim($column_sql, ', ');
	}


	protected function prepareWhere(array $where, ?array &$params): string
	{

		if ($where == []) {

			return '';
		}


		$where_sql = ' where ';

		foreach ($where as $key => $value) {

			$filter = Utils::snakeCase($key);

			$cleared_filter = trim(str_replace(self::ALL_OPERATORS, '', $filter));

			$this->ensureColumnExists($cleared_filter, Message::BAD_FILTER, StatusCode::BAD_FILTER);

			//default operator is =, if there was no other operator we add =
			if (trim($filter) === $cleared_filter) {

				$filter .= " =";
			}

			//adding quotes (") to the column
			$filter = str_replace($cleared_filter, "\"$cleared_filter\"", $filter);

			$where_sql .= "$filter ";

			Database::addParam($where_sql, $value, $params);

			$where_sql .= " and ";
		}

		return substr($where_sql, 0, -strlen(" and "));
	}

	private function translateDBData(array $data): array
	{
		$vars = $this->allProperties;

		$new_data = [];

		foreach ($data as $ind => $row) {

			foreach ($vars as $name) {

				$callback = $this->overWriteGetValues[$name] ?? null;

				$value = $row[Utils::snakeCase($name)] ?? null;

				$new_data[$ind][$name] = $callback ? $callback($value) : $value;
			}
	
		}


		return $new_data;
	}
}
