<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case Participant = 'participant';
    case Creator = 'creator';
    case OrganizationAdmin = 'organization_admin';
    case SuperAdmin = 'super_admin';

    public function canCreateQuiz(): bool
    {
        return in_array($this, [self::Creator, self::OrganizationAdmin, self::SuperAdmin], true);
    }
}
