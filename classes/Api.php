<?php

class Api
{

	const ANY = '[^/]+';
	const NUM = '[0-9]+';
	
	const ROUTES_FOLDER = 'classes/Controllers';

	const WILD_CARDS = [
		//':any' => '([^/]+)',
		':num' => '(/[0-9]+)?',
	];


	static $allRoutes = null;


	static private function getAllRoutes() : array 
	{
		if (isset(self::$allRoutes)) {

			return self::$allRoutes;

		}

		self::$allRoutes = [];

		$controllers = array_filter(scandir(self::ROUTES_FOLDER), function ($file) {

			return is_file(Api::ROUTES_FOLDER.'/'.$file);

		});

		foreach ($controllers as $controller_file) {

			self::$allRoutes[str_replace('.php', '', $controller_file)] = str_replace('Controller.php', '', $controller_file);

		}

		return self::$allRoutes;

	} 
	
	static function route()
	{
		try {

			header('Content-Type: application/json; charset=utf-8');

			Database::init();
			
			
			$uri = strtolower(trim($_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI'], '/'));
			
			$httpVerb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';
	
			$request = in_array($httpVerb, Controller::WITH_DATA_VERBS) ? json_decode(file_get_contents('php://input'), true) : [];

			
			foreach (self::getAllRoutes() as $controller => $route) {


				foreach (self::WILD_CARDS as $params) {

					if (preg_match('#^'.$route.$params.'$#i', $uri, $matches)) {

						$controller = new $controller();

						return $controller->getResponse($httpVerb, $request, $matches[1]);
					}

				}

			}

			http_response_code(StatusCode::NOT_FOUND_ERROR);

			$response = new ErrorResponse(Message::NOT_FOUND_ROUTE);


		} catch (Throwable $e) {

			http_response_code(StatusCode::SERVER_ERROR);

			$response = new ErrorResponse(GetMessage::getServerErrorMessage($e));
	
		}

		Utils::echoJson($response);

	}
}