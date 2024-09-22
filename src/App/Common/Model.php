<?php

declare(strict_types=1);

namespace App\Common;

use App\Database;

//Abstract since we won't have separate instances of it
abstract class Model
{
    //Property promotion - database needs to accessible to all models (children of this class)
    public function __construct(protected Database $database)
    {
    }
}