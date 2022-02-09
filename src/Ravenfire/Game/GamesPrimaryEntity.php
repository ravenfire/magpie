<?php

namespace Ravenfire\Magpie\Ravenfire\Game;

use Ravenfire\Magpie\Ravenfire\SqlCommands\SqlCount;
use Ravenfire\Magpie\Ravenfire\SqlCommands\SqlFind;
use Ravenfire\Magpie\Ravenfire\SqlCommands\SqlJoin;
use Ravenfire\Magpie\Sources\AbstractPrimaryEntity;

/**
 * Established PrimaryEntity
 */
class GamesPrimaryEntity extends AbstractPrimaryEntity
{
    public static function getNewCommands(): array
    {
        return [
            SqlCount::class,
            SqlJoin::class,
            SqlFind::class
        ];
    }

    /**
     * @inheritDoc
     */
    static public function getKey(): string
    {
        return "games";
    }

    /**
     * @inheritDoc
     */
    static public function getModelClass(): string
    {
        return GameModel::class;
    }

    /**
     * @inheritDoc
     */
    public static function getMigrations(): array
    {
        return [
            GamesMigration::class,
        ];
    }
}