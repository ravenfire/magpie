<?php

namespace Ravenfire\Magpie\Ravenfire\Game;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Establishes model for GamePrimaryEntity.
 */
class GameModel extends Model
{
    /** @var string */
    protected $table = 'games';

    /**
     * Generates game key by taking the required game data as arguements
     *
     * @param $name
     * @param string $publisher
     * @param string $yearPublished
     * @return string
     */
    public static function gameKey($name, string $publisher, string $yearPublished): string
    {
        return Str::slug("{$name} {$publisher} {$yearPublished}", "-");
    }
}