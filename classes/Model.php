<?php

abstract class Model
{
	public $id;

	const ALL_COLUMNS = '*';
	

	public function get($where = [], ?array $columns = null) {

		$params = [];

		$this->prepareWhere($where, $params);


	}

	protected function prepareColumns(?array $columns) 
	{

		if ($columns == null) {

			$columns = Utils::snakeCase(get_object_vars($this));

		}

		

	}

	protected function prepareWhere(array $where, array &$params) : string 
	{

		$where_sql = '';



		return $where_sql;

	}

	public function __construct($data) {

		if(is_object($data)) {

			$vars = get_object_vars($this);

			foreach ($vars as $name => $value) {

				if (isset($data->$name)) {

					$this->$name = $data->$name;
				}
			}
		}
	}
}