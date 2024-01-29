<?php

/**
 * a class of constants representing the different error messages for the App
 */
class Message
{

	const VALIDATION_ERROR = 'There is/are %s error(s) in the request';
	const REQUIRED_FIELD = 'The field [%s] is required';
	const MAX_LENGTH_EXCEEDED = 'The field [%s] has max length of %s';
	const NOT_VALID_FORMAT = 'The field [%s] does not match the expected format: %s';
	const NOT_VALID_DATE_FORMAT = 'The field [%s] is not a valid ISO8601 datetime format';
	const NOT_VALID_SELECTION = 'The field [%s] must be one of these values: %s';
	const END_NOT_GREATER_THAN_START = 'The field [%s] must be greater than [%s]';
	

	const NOT_FOUND_ERROR = 'The requested resource was not found, for value %s and resource %s';
	const NOT_FOUND_ROUTE = 'No such route';
	const NOT_FOUND_METHOD = 'No such method for verb: %s and class: %s';
	

	const SERVER_ERROR = 'The request could not be processed due to an unexpected error';	
	const BAD_COLUMN = "Can not find column: %s for class: %s";
	const BAD_FILTER = "Can not find filter: %s for class: %s";
	const EMPTY_UPDATE = "Update request is empty for resource: %s";
	

	const PDO_EXCEPTION = 'PDO Error';


}