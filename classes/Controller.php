<?php

/**
 * Controller the base abstract class that manages the http request
 */
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


	protected $statusCodeOnSuccess = StatusCode::SUCCESS;

	
	/**
	 * get
	 * manages the get request
	 * @param  array $request only the model->id will be a valid filter by default
	 * @return array a list with records
	 */
	protected function get(array $request) : array
	{
		$id = $request[$this->model->id] ?? null;

		if (isset($id)) {

			return $this->model->getById($id);

		}

		return $this->model->get();
	}

	
	/**
	 * add
	 * manages the post request
	 * @param  array $request
	 * @return array a list containing only the the newly added record
	 */
	protected function add(array $request) : array 
	{
		$this->statusCodeOnSuccess = StatusCode::SUCCESSFULLY_ADDED;

		return $this->model->insert($request);
	}

	
	/**
	 * delete
	 * manages the delete request
	 * @param  array $request
	 * @return void
	 */
	protected function delete($request) 
	{
		$id = $request[$this->model->id] ?? null;

		$this->ensureIdWasSent($id);

		$this->model->deleteById($id);

		return [];
	}

	
	/**
	 * edit
	 * manages the patch request
	 * @param  array $request
	 * @return array a list with only the updated record, with all fields
	 */
	protected function edit(array $request) : array 
	{
		$id = $request[$this->model->id] ?? null;

		$this->ensureIdWasSent($id);

		return $this->model->update($request, $id);

	}

	
	/**
	 * ensureIdWasSent
	 * check whether an id was present in the url, throws a RequestError if not
	 * will be used for methods like patch and delete that require an id
	 * @param  mixed $id
	 * @return void
	 */
	protected function ensureIdWasSent($id) : void
	{
		if (!isset($id)) {

			throw new RequestError(Message::NOT_FOUND_ROUTE, StatusCode::NOT_FOUND_ERROR);
			
		}

	}
	
	/**
	 * getResponse
	 * provide the api's response for the received request as a json echo
	 * for the response's data one of the add, edit, delete, or get methods will be called
	 * @param  string $httpVerb the http method
	 * @param  array $request the body of the request sent
	 * @param  mixed $key the value after the main route, generally the id
	 * @return void
	 */
	public function getResponse(string $httpVerb, array $request, $key = null) : void
	{
		try {

			$method = self::VERB_TO_METHOD[$httpVerb] ?? null;

			if (!isset($method)) {

				throw new ServerError(GetMessage::msg(Message::NOT_FOUND_METHOD, $httpVerb, __CLASS__));

			}

			if (isset($key)) {

				$request[$this->model?->id] = $key;

			}

			$response = new Response($this->$method($request));

			http_response_code($this->statusCodeOnSuccess);

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

	
	/**
	 * __construct
	 *
	 * @param  ?Model $model
	 * @return void
	 */
	public function __construct(protected ?Model $model = null)
	{

		$modelClass = str_replace('Controller', 'Model', get_class($this));

		if (!$model && class_exists($modelClass)) {

			$this->model = new $modelClass();

		}
	}
}