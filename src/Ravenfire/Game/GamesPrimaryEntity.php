<?php

namespace Ravenfire\Magpie\Ravenfire\Game;

use Ravenfire\Magpie\Ravenfire\Sql\SqlCountScript;
use Ravenfire\Magpie\Ravenfire\Sql\SqlFindScript;
use Ravenfire\Magpie\Ravenfire\Sql\SqlJoinScript;
use Ravenfire\Magpie\Sources\AbstractPrimaryEntity;

/**
 * Established PrimaryEntity
 */
class GamesPrimaryEntity extends AbstractPrimaryEntity
{
    public static function getNewCommands(): array
    {
        return [
            SqlCountScript::class,
            SqlJoinScript::class,
            SqlFindScript::class
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