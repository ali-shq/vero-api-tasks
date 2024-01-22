<?php

abstract class Controller
{

	const GET = 'get';
	const POST = 'post';
	const PUT = 'put';
	const PATCH = 'patch';
	const DELETE = 'delete';


	const WITH_DATA_VERBS = [self::POST, self::PUT, self::PATCH];


	const VERB_TO_METHOD = [
							self::GET => 'get',
							self::POST => 'add',
							self::PUT => 'edit',
							self::DELETE => 'delete',
							self::PATCH => 'edit',
						];

	const NOT_FOUND_ROUTE = 'No such route';

	protected function get() {}

	protected function add() {}

	protected function delete() {}

	protected function edit() {}


	public function __construct()
	{

		$uri = strtolower(trim((string)$_SERVER['PATH_INFO'], '/'));
		$httpVerb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

		$wildcards = [
			':any' => '[^/]+',
			':num' => '[0-9]+',
		];

		$routes = [
			'get constructionStages' => [
				'class' => 'ConstructionStages',
				'method' => 'getAll',
			],
			'get constructionStages/(:num)' => [
				'class' => 'ConstructionStages',
				'method' => 'getSingle',
			],
			'post constructionStages' => [
				'class' => 'ConstructionStages',
				'method' => 'post',
				'bodyType' => 'ConstructionStagesCreate'
			],
		];

		$response = [
			'error' => 'No such route',
		];

		if ($uri) {

			foreach ($routes as $pattern => $target) {
				$pattern = str_replace(array_keys($wildcards), array_values($wildcards), $pattern);
				if (preg_match('#^'.$pattern.'$#i', "{$httpVerb} {$uri}", $matches)) {
					$params = [];
					array_shift($matches);
					if ($httpVerb === 'post') {
						$data = json_decode(file_get_contents('php://input'));
						$params = [new $target['bodyType']($data)];
					}
					$params = array_merge($params, $matches);
					$response = call_user_func_array([new $target['class'], $target['method']], $params);
					break;
				}
			}

			echo json_encode($response, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		}
	}
}