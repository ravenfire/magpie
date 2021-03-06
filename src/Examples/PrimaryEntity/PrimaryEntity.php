<?php

namespace Ravenfire\Magpie\Examples\PrimaryEntity;

use Ravenfire\Magpie\Sources\AbstractPrimaryEntity;

class PrimaryEntity extends AbstractPrimaryEntity //Micahel changed from ExamplePrimaryEntity
{
    static public function getModelClass(): string
    {
        return "\Some\Class"; // @todo
    }

    static public function getKey(): string
    {
        return 'primary-entity';
    }

    public static function getMigrations(): array
    {
        return [
            PrimaryMigration::class,
        ];
    }
}