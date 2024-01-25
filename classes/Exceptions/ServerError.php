<?php

class ServerError extends ApplicationError
{

    
    public function __construct(string $development_msg = '', 
                                int $code = StatusCode::SERVER_ERROR, 
                                \Throwable|null $previous = null)
    {
        return parent::__construct($development_msg, $code, $previous);
    }

}