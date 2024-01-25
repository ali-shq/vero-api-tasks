<?php

class RequestError extends ApplicationError
{

    public function __construct(string $msg = 'There are errors in the request!', int $code = StatusCode::REQUEST_ERROR)
    {
        return parent::__construct($msg, $code);
    }

}