<?php

/**
 * ServerError
 * the exception that will be thrown when something goes wrong from Api side, i.e. an error that is not a bad request error
 */
class ServerError extends ApplicationError
{

    
    public function __construct(string $development_msg = '', 
                                int $code = StatusCode::SERVER_ERROR, 
                                \Throwable|null $previous = null)
    {
        return parent::__construct($development_msg, $code, $previous);
    }

}