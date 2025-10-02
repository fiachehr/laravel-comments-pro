<?php

namespace Fiachehr\Comments\Facades;

use Illuminate\Support\Facades\Facade;

class Reactions extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'comments.reactions.service';
    }
}
