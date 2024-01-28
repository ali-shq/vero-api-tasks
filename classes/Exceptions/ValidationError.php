<?php

/**
 * ValidationError the exception type that will be thrown when a record does not pass it's model's validations
 */
class ValidationError extends ApplicationError
{

    public function __construct(string $msg = Message::VALIDATION_ERROR, int $code = StatusCode::VALIDATION_ERROR)
    {
        return parent::__construct($msg, $code);
    }

}