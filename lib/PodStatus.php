<?php

use CommerceGuys\Enum\AbstractEnum;

final class PodStatus extends AbstractEnum
{
    const Down           = 0;
    const Up             = 1;
    const Recheck        = 2;
    const Paused         = 3;
    const System_Deleted = 4;
    const User_Deleted   = 5;
}
