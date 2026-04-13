<?php

namespace App\Observers;

use App\Observers\Concerns\InterceptsAdminActions;

class MediaObserver
{
    use InterceptsAdminActions;
    protected string $resource = 'media';
}
