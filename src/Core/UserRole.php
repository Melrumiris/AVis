<?php

declare(strict_types=1);

enum UserRole: string
{
    case User = 'user';
    case Admin = 'admin';
}
