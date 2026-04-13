<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown by Eloquent observers when an admin's action is intercepted
 * because they lack the required per-resource permission.
 * The action has already been queued as an ApprovalRequest.
 */
class ActionInterceptedException extends Exception
{
    public function __construct(string $message = 'Your action has been queued for super-admin approval.')
    {
        parent::__construct($message);
    }
}
