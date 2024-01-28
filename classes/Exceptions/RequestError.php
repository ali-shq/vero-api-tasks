<?php

/**
 * RequestError is intended for the client app due errors, i.e. errors linked with the request but that are not
 * validation errors, like requesting a non-existing route
 */
class RequestError extends ApplicationError
{

    public function __construct(string $msg = 'There are errors in the request!', int $code = StatusCode::REQUEST_ERROR)
    {
        return parent::__construct($msg, $code);
    }

}