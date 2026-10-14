<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class AccountDeactivatedException extends HttpException
{
    public string $errorType = 'invalid_request_error';
    public string $errorCode = 'account_deactivated';

    public function __construct(string $message = 'You do not have an account because it has been deleted or deactivated.')
    {
        parent::__construct(401, $message);
    }
}
