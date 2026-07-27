<?php

namespace App\Observers;

use App\Models\TeamMember;
use App\Observers\Concerns\InterceptsAdminActions;
use App\Support\PublicApiCache;

class TeamMemberObserver
{
    use InterceptsAdminActions;

    protected string $resource = 'team_members';

    public function saved(TeamMember $member): void
    {
        PublicApiCache::forgetTeam();
    }

    public function deleted(TeamMember $member): void
    {
        PublicApiCache::forgetTeam();
    }
}
