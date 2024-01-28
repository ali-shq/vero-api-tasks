<?php

/**
 * Model is the abstract base class used for DB interactions, each class extending the Model class 
 * represents the interactions with a given database table
 * the table name is the snake-case version of the Model class name, removing the Model part
 * for eg. ConstructionStagesModel translates to the costruction_stages table. 
 * Similarly the fields present inside $allProperties will represent the relevant columns of the table, after
 * being snake-cased
 */
abstract class Model
{

	private $tableName;

	private $allColumnSet;

	
	/**
	 * represents the list of fields the Model record has
	 *
	 * @var array
	 */
	public $allProperties = [];
	
	/**
	 * represents the primary key of the table associated with the model
	 *
	 * @var string
	 */
	public $id = 'id';


	
	/**
	 * represents an array of key-values, with keys being particular Model::$allProperties fields
	 * over which a closure represented by the associated values will be called to get the actual value 
	 *
	 * @var array
	 */
	protected $overWriteGetValues = [];
	


	/**
	 * represents an array of default key-values that will be added to an Model::insert request if its keys are not present in the
	 * provided input
	 * The keys being particular Model::$allProperties fields associated with a default value represented by the associative value, 
	 * if the value provided is a closure it will be called on the record being added
	 *
	 * @var array
	 */
	protected $defaultValues = [];
	


	/**
	 * represent the validations that insert and update request will have to satisfy
	 *
	 * @var array
	 */
	protected $validations = [];



	const ALL_COLUMNS = '*';

	const ALL_OPERATORS = ['>=', '<', '>', '<=', '!='];

	
	/**
	 * __construct does the initial setup, $allProperties should have been defined before the constructor is called
	 * over-write the constructor for additional setup, but always do call the parent constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->allProperties[] = $this->id;

		$this->allColumnSet = array_flip(array_map(Utils::snakeCase(...), $this->allProperties));

		$this->tableName = Utils::snakeCase(str_replace('Model', '', get_class($this)));
	}


	
	/**
	 * get
	 * read records from the database
	 *
	 * @param  array $where an associative array of filtering conditions like ['id' => 5]. Multiple conditions are allowed,
	 * and other than the implicit equality operator, the other operators present in the Model::ALL_OPERATIORS can be 
	 * joined with a field as a key like 
	 * for eg. ['endDate >' => '2021-07-17', 'endDate <' => '2024-11-14', 'id !=' => 7]
	 * @param  ?array $fields represend the columns that will be selected from the database, if left null all columns
	 * will be selected
	 * @return array list of associative arrays representing the records read from the database
	 */

	public function get($where = [], ?array $fields = null): array
	{
		$params = [];

		$query = 'select ' . $this->prepareColumns($fields) . ' from "' . $this->tableName . '"' . $this->prepareWhere($where, $params);

		$data = Database::execQuery($query, $params);

		return $this->translateDBData($data);
	}


	
	/**
	 * getById
	 * return a single record from the database, represented by the id given as parameter, 
	 * a RequestError exception will be thrown if no such record is found
	 *
	 * @param  mixed $id
	 * @return array a list containing a single associative array representing the database record
	 */
	public function getById($id) : array 
	{
		$data = $this->get([$this->id => $id]);

		if ($data == []) {

			throw new RequestError(GetMessage::msg(Message::NOT_FOUND_ERROR, $id, self::getResourceName($this)), StatusCode::NOT_FOUND_ERROR);

		}

		return $data;

	}


	
	/**
	 * update
	 * updates a database record represented by the provided id, only the fields that are part of the request will be updated
	 * a RequestError will be thrown if an empty request is sent (i.e. one without any fields to update) or if the record
	 * represented by the id parameter is not found
	 * @param  array $request is an associative array with fields to update
	 * @param  mixed $id
	 * @return array return the updated record
	 */
	public function update(array $request, $id): array
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

	
	/**
	 * getResourceName is a static function used to translate a Model class name into a lower-cased string without the model
	 * part, for eg. ConstructionStagesModel -> constructionstages, mainly used to communicate with the front-end developers
	 * @param  Model $model
	 * @return string
	 */
	static function getResourceName (Model $model) : string
	{

		return str_replace('model', '', strtolower(get_class($model)));

	}

	
	/**
	 * prepareUpdate
	 * a helper function that prepares the sql string that needs to be executed in the database to achieve the requested update
	 * and adds the parameters needed for that execution to the params array
	 *
	 * @param  array $request the initial update request
	 * @param  mixed $id the id of the record to be updated
	 * @param  array $params the array where new parameters needed for the query execution will be added by the function
	 * @return string the sql string to be executed
	 */
	protected function prepareUpdate(array $request, $id, array &$params = []): string
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

	
	/**
	 * prepareInsert
	 * a helper function that prepares the sql string that will be executed to add the record in the database
	 * table associated with the model 
	 * and adds the parameters needed for that execution to the params array
	 *
	 * @param  array $request represents the record to be added
	 * @param  array $params the array where new parameters needed for the query execution will be added by the function
	 * @return string the sql string to be executed
	 */
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


		
	/**
	 * deleteById
	 * deletes a single record from the database table associated with the model, 
	 * represented by the id provided as parameter
	 * no error message will be thrown if the record is not found
	 *
	 * @param  mixed $id
	 * @return void
	 */
	public function deleteById($id): void
	{

		$where = [$this->id => $id];

		$this->delete($where);

	}


	
	/**
	 * delete
	 * delete records from the database table associated with the model fullfiling the where condition
	 * be CAREFULL an empty $where will delete all records from that table
	 * for a more detailed description of the where condition see the Model::get function with the logic being the same
	 * as to a Model::get where parameter
	 *
	 * @param  mixed $where
	 * @return void
	 */
	public function delete(array $where): void
	{

		$params = [];

		$query = 'delete from "'.$this->tableName.'" '.$this->prepareWhere($where, $params);

		Database::execQuery($query, $params);

	}


		
	/**
	 * insert
	 * add a record whose values are given by the request array to the database and return back the
	 * added record
	 * @param  array $request
	 * @return array
	 */
	public function insert(array $request) : array
	{
		
		$this->addDefaultValues($request);
	
		$this->checkValidations($request);

		unset($request[$this->id]);

		$params = [];

		$query = $this->prepareInsert($request, $params);

		return $this->translateDBData(Database::execQuery($query, $params));

		//return $this->getById(Database::getLastInsertId());
	}


		
	/**
	 * addDefaultValues
	 * adds default values to the request array if the keys inside the Model::$defaultValues are not part of the request
	 *
	 * @param  array $request
	 * @return void
	 */
	public function addDefaultValues(array &$request) : void
	{

		$defaults = [];

		foreach ($this->defaultValues as $key => $value) {
	
			$defaults[$key] = is_callable($value) ? $value($request) : $value;

		}

		$request += $defaults;

	}

		
	/**
	 * checkValidations
	 * check whether the record values represented by the request satisfy the validations associated with the Model.
	 * If the check fails, a ValidationError with the associated fail-validation messages will be thrown 
	 * 
	 * @param  array $request
	 * @param  mixed $id the id of the record being updated or null if the check is done on a new record
	 * @return void
	 */
	public function checkValidations(array $request, $id = null) 
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

		
	/**
	 * ensureColumnExists
	 * checks whether the column passed as input is present in the Model::$allProperties array (after being snake-cased)
	 * and throws a ServerError if such is not the case with the given message and code
	 *
	 * @param  string $column
	 * @param  string $msg
	 * @param  int $code
	 * @return void
	 */
	protected function ensureColumnExists(string $column, string $msg = Message::BAD_COLUMN, int $code = StatusCode::BAD_COLUMN) 
	{
			
		if (!isset($this->allColumnSet[$column])) {

			throw new ServerError(GetMessage::msg($msg, $column, get_class($this)), $code);

		}
	}

		
	/**
	 * prepareColumns
	 * prepares a string with columns passed as input in double quotes and snake-cased
	 * will return '*' if null is passed instead as a paramter
	 * @param  ?array $columns
	 * @return string
	 */
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


		
	/**
	 * prepareWhere
	 * prepares the where... part to be used in select or delete queries
	 *
	 * @param  array $where the associative array representing the where filter, see the Model::get for a more detailed explanation
	 * @param  array $params the array to which additional parameters to be passed to the database execute function are added
	 * @return string the resulting sql string
	 */
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


	
	/**
	 * translateDBData
	 * takes as input data read from the database and returns a list of associative arrays whose keys are taken from
	 * the Model::$allProperties array, the values will in general be taken from the respective (snake-cased) columns of the
	 * table unless the field has been included in the Model::$overWriteGetValues property as a key, in which case
	 * an additional closure could be called on the field's value 
	 *
	 * @param  array $data list of database records representing the inital values to be used for the new return array
	 * @return array the resulting new list of associative arrays
	 */
	protected function translateDBData(array $data): array
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
