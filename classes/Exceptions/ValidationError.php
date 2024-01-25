<?php

class ValidationError extends ApplicationError
{

    public function __construct(string $msg = Message::VALIDATION_ERROR, int $code = StatusCode::VALIDATION_ERROR)
    {
        return parent::__construct($msg, $code);
    }

}