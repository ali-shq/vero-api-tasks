<?php

class Database
{
	const name = 'testDb';

	static private $db;


	

	static function init() 
	{
		self::$db = new PDO('sqlite:'.self::name.'.db', '', '', [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		]);

		self::createTables();
		$stmt = self::$db->query('SELECT 1 FROM construction_stages LIMIT 1');
		if (!$stmt->fetchColumn()) {
			self::loadData();
		}

		return self::$db;
	}

	private static function createTables()
	{
		$sql = file_get_contents('database/structure.sql');
		self::$db->exec($sql);
	}

	private static function loadData()
	{
		$sql = file_get_contents('database/data.sql');
		self::$db->exec($sql);
	}


	static public function execQuery(string $sql, array $params = []) 
	{
		try {

			$stmt = self::$db->prepare($sql);

			
			$stmt->execute($params);

	
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
	
		} catch (PDOException $e) {

			//we need to do something about the initial message
			//ErrorLog::log($e);//for example
			throw new ServerError($e->getMessage(), StatusCode::PDO_EXCEPTION, $e);
		}

	}

	static public function getLastInsertId() 
	{
		return self::$db->lastInsertId();
	}

	static public function addParam(string &$sql_str, $value, ?array &$params) 
	{

		$params ??= [];

		$index = count($params);

		$key = "param_$index";

		$params[$key] = $value;

		$sql_str .= " :$key ";

	}


}