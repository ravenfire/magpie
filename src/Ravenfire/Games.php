<?php

namespace Ravenfire\Magpie\Ravenfire;

use Ravenfire\Magpie\Sources\AbstractPrimaryEntity;

class Games extends AbstractPrimaryEntity
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
        return Game::class;
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