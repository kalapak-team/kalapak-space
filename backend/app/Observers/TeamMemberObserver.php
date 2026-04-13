<?php

namespace App\Observers;

use App\Observers\Concerns\InterceptsAdminActions;

class TeamMemberObserver
{
    use InterceptsAdminActions;
    protected string $resource = 'team_members';
}
