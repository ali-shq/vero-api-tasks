<?php

/**
 * StatusCode a class of constants representing the different http status codes for the App
 */
class StatusCode
{

	const SUCCESS = 200;
	const SUCCESSFULLY_ADDED = 201;

	const REQUEST_ERROR = 400;
	const VALIDATION_ERROR = 401;
	const NOT_FOUND_ERROR = 404;
	const METHOD_NOT_SUPPORTED = 480;

	const SERVER_ERROR = 500;
	const PDO_EXCEPTION = 580;
	const BAD_FILTER = 581;
	const BAD_COLUMN = 582;
	

}