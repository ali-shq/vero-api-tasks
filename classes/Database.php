<?php

/**
 * Database
 * the PDO wrapper
 */
class Database
{
	const name = 'testDb';

	static private $db;


	
	
	/**
	 * init
	 * initiate the PDO connection and do the intital table creation and population
	 * @return void
	 */
	static function init() : void
	{
		self::$db = new PDO('sqlite:'.self::name.'.db', '', '', [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		]);

		self::createTables();
		$stmt = self::$db->query('SELECT 1 FROM construction_stages LIMIT 1');
		if (!$stmt->fetchColumn()) {
			self::loadData();
		}

	}
	
	/**
	 * createTables
	 * create the initial tables
	 *
	 * @return void
	 */
	private static function createTables()
	{
		$sql = file_get_contents('database/structure.sql');
		self::$db->exec($sql);
	}
	
	/**
	 * loadData
	 * load initial data to the table
	 * @return void
	 */
	private static function loadData()
	{
		$sql = file_get_contents('database/data.sql');
		self::$db->exec($sql);
	}

	
	/**
	 * execQuery
	 * execute the query
	 * @param  string $sql the sql string to be executed
	 * @param  array $params the parameters => values array 
	 * @return array
	 */
	static public function execQuery(string $sql, array $params = []) : array
	{
		try {

			$stmt = self::$db->prepare($sql);
			
			$stmt->execute($params);

			return $stmt->fetchAll(PDO::FETCH_ASSOC);
	
		} catch (PDOException $e) {

			throw new ServerError($e->getMessage(), StatusCode::PDO_EXCEPTION, $e);
		}

	}
	
	/**
	 * getLastInsertId get the last database inserted id back
	 *
	 * @return mixed
	 */
	static public function getLastInsertId() : mixed
	{
		return self::$db->lastInsertId();
	}
	
	/**
	 * addParam
	 *
	 * @param  string $sqlStr the current sql string to which the additional parameter placeholder will be added
	 * @param  mixed  $value the value the parameter has
	 * @param  array  $params the array to which the parameter is added
	 * @return void
	 */
	static public function addParam(string &$sqlStr, $value, ?array &$params) : void
	{

		$params ??= [];

		$index = count($params);

		$key = "param_$index";

		$params[$key] = $value;

		$sqlStr .= " :$key ";

	}


}