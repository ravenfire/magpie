<?php

namespace Ravenfire\Magpie\Ravenfire\Game;

use Ravenfire\Magpie\Sources\AbstractPrimaryEntity;

/**
 * Established PrimaryEntity
 */
class GamesPrimaryEntity extends AbstractPrimaryEntity
{
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