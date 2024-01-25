<?php

class Message
{

	const SUCCESS = 'Ok';

	const VALIDATION_ERROR = 'There are errors in the request';
	const NOT_FOUND_ERROR = 'The requested resource was not found, for value %s and resource %s';
	const NOT_FOUND_ROUTE = 'No such route';
	const NOT_FOUND_METHOD = 'No such method for verb: %s and class: %s';
	

	const SERVER_ERROR = 'The request could not be processed due to an unexpected error';	
	const BAD_COLUMN = "Can not find column: %s for class: %s";
	const BAD_FILTER = "Can not find filter: %s for class: %s";
	const EMPTY_UPDATE = "Update request is empty for resource: %s";
	

	const PDO_EXCEPTION = 'PDO Error';


}