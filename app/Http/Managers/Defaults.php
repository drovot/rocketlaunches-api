<?php

declare(strict_types=1);

namespace App\Http\Managers;

class Defaults
{

    public const REQUEST_LIMIT = 5;
    public const REQUEST_LIMIT_MAX = 50;
    public const REQUEST_PAGE = 1;
    public const REQUEST_DETAILED = false;

    public const DATABASE_ORDER_ASC = "asc";
    public const DATABASE_ORDER_DESC = "desc";

    public const DATABASE_COLUMN_START_NET = "startNet";
    public const DATABASE_COLUMN_CREATED = "created_at";

    public const STATUS_PUBLISHED = false;
}
