<?php

/**
 * Pod status types enum.
 */

declare(strict_types=1);

namespace Poduptime;

use CommerceGuys\Enum\AbstractEnum;

final class PodStatus extends AbstractEnum
{
    const DOWN           = 0;
    const UP             = 1;
    const RECHECK        = 2;
    const PAUSED         = 3;
    const SYSTEM_DELETED = 4;
    const USER_DELETED   = 5;
}
