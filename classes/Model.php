<?php

abstract class Model
{

	private $allColumnSet;

	public $allProperties = [];

	public $id = 'id';

	private $tableName;

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

			throw new RequestError(GetMessage::msg(Message::NOT_FOUND_ERROR, $id, __CLASS__));

		}

		return $data;

	}



	public function update(array $request, int $id): array
	{
		if ($request == []) {

			throw new ServerError(GetMessage::msg(Message::EMPTY_UPDATE, __CLASS__));

		}

		$this->checkValidations($request, true);

		$params = [];

		$query = $this->prepareUpdate($request, $id, $params);

		Database::execQuery($query, $params);

		return $this->getById($id);
	}



	protected function prepareUpdate(array $request, int $id, array &$params = []): string
	{

		$query = 'update "'.$this->tableName.'" set ';


		foreach ($request as $key => $value) {

			$this->ensureColumnExists($key);

			$query .= "\"$key\" = ";

			Database::addParam($query, $value, $params);

			$query .= ',';
		}

		$query = rtrim($query, ',') . " where $this->id = ";
		
		Database::addParam($query, $id, $params);
		
		return $query;

	}


	protected function prepareInsert(array $request, &$params = []) 
	{
	
		$query = 'insert into "'.$this->tableName.'" ';

		if ($request == []) {

			return $query . 'DEFAULT VALUES';

		}

		$query .= '('.$this->prepareColumns(array_keys($request)).') values (';


		foreach ($request as $value) {

			Database::addParam($query, $value, $params);

			$query .= ',';
		}

		$query = rtrim($query, ',').')';
		
		return $query;
	}


	public function insert(array $request): array
	{
	
		$this->checkValidations($request);

		$params = [];

		$query = $this->prepareInsert($request, $params);

		Database::execQuery($query, $params);

		return $this->getById(Database::getLastInsertId());
	}



	protected function checkValidations(array $request, bool $is_update = false) 
	{


	}

	protected function ensureColumnExists(string $column, string $msg = Message::BAD_COLUMN, int $code = StatusCode::BAD_COLUMN) 
	{
			
		if (!isset($this->allColumnSet[$column])) {

			throw new ServerError(GetMessage::msg($msg, $column, __CLASS__), $code);

		}
	}

	protected function prepareColumns(?array $columns): string
	{

		if ($columns == null) {

			return self::ALL_COLUMNS;
		}

		$column_sql = '';

		foreach ($columns as $column) {

			$this->ensureColumnExists($column);

			$column_sql .= "\"$column\" ,";
		}

		return rtrim($column_sql);
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

			//adding quotes (") to the column
			$filter = str_replace($cleared_filter, "\"$cleared_filter\"", $filter);


			//default operator is =, if there was no other operator we add =
			if (trim($filter) === $cleared_filter) {

				$filter .= " =";
			}

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

		foreach ($vars as $name) {

			$new_data[$name] = $data[Utils::snakeCase($name)] ?? null;
		}

		return $new_data;
	}
}
