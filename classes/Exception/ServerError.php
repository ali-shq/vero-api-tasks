<?php

class ServerError extends ApplicationError
{

    
    //this might not be SOLID, but it came easier to have this line of parameters
    public function __construct(string $development_msg = '', 
                                int $code = StatusCode::SERVER_ERROR, 
                                \Throwable|null $previous = null)
    {
        return parent::__construct(Env::$is_deveplopment ? $development_msg : Message::SERVER_ERROR, $code, $previous);
    }

}