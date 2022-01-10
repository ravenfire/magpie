<?php

namespace Ravenfire\Magpie\Ravenfire;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Game extends Model
{
    public static function gameKey($name, string $publisher, string $yearPublished): string{
        return Str::slug("{$name} {$publisher} {$yearPublished}", "-");
    }
}