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




	protected function get(array $request) : array
	{
		$id = $request[$this->model->id] ?? null;

		if (isset($id)) {

			return $this->model->getById($id);

		}

		return $this->model->get();
	}


	protected function add(array $request) : array 
	{
		return $this->model->insert($request);
	}


	protected function delete($request) 
	{
		$id = $request[$this->model->id] ?? null;

		if (!isset($id)) {

			throw new RequestError(Message::NOT_FOUND_ROUTE, StatusCode::NOT_FOUND_ERROR);
			
		}

		$this->model->deleteById($id);

		return [];
	}


	protected function edit(array $request) : array 
	{
		$id = $request[$this->model->id] ?? null;

		if (!isset($id)) {

			throw new RequestError(Message::NOT_FOUND_ROUTE, StatusCode::NOT_FOUND_ERROR);
			
		}


		return $this->model->update($request, $id);

	}

	public function getResponse($httpVerb, $request, $key = null) : void
	{
		try {

			$method = self::VERB_TO_METHOD[$httpVerb] ?? null;

			if (!isset($method)) {

				throw new ServerError(GetMessage::msg(Message::NOT_FOUND_METHOD, $httpVerb, __CLASS__));

			}

			if (isset($key)) {

				$request[$this->model->id] = $key;

			}

			$response = new Response($this->$method($request));

		} catch (ServerError $e) {

			$response = new ErrorResponse(GetMessage::getServerErrorMessage($e));

			http_response_code($e->getCode());
			
		} catch (ApplicationError $e) {

			$response = new ErrorResponse($e->getMessage());

			http_response_code($e->getCode());
			
		} catch (Throwable $e) {

			http_response_code(StatusCode::SERVER_ERROR);

			$response = new ErrorResponse(GetMessage::getServerErrorMessage($e));
	
		}

		
		Utils::echoJson($response);
	}


	public function __construct(protected ?Model $model = null)
	{

		$model_class = str_replace('Controller', 'Model', get_class($this));

		if (class_exists($model_class)) {

			$this->model = new $model_class();

		}
	}
}