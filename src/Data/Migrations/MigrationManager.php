<?php

namespace Ravenfire\Magpie\Data\Migrations;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use InvalidArgumentException;
use Ravenfire\Magpie\Data\Logs\LogsTableMigration;
use Ravenfire\Magpie\Magpie;
use Ravenfire\Magpie\Sources\AbstractSource;

class MigrationManager
{
    static protected $migrations = [
        LogsTableMigration::class,
    ];

    /**
     * @var Magpie
     */
    protected $context;

    /**
     * @return string[]
     */
    public static function getMigrations(): array
    {
        return self::$migrations;
    }

    /**
     * @param Magpie $context
     */
    public function __construct(Magpie $context)
    {
        $this->context = $context;
    }

    public function upMagpie(callable $log_callback)
    {
        $this->up(static::getMigrations(), $log_callback);
    }

    public function up(array $migrations, callable $log_callback = null)
    {
        foreach ($migrations as $migration_class) {
            /** @var Migration $migration */
            $migration = new $migration_class();

            if (!$migration instanceof AbstractMigration) {
                throw new InvalidArgumentException("Migration must be a valid migration");
            }

            if ($log_callback) {
                $log_callback($migration_class);
            }

            if (!Manager::schema()->hasTable($migration::getTableName())) {
                $migration->up();
            }
        }
    }

    public function downMagpie(callable $log_callback)
    {
        $this->down(static::getMigrations(), $log_callback);
    }

    public function down(array $migrations, callable $log_callback = null)
    {
        foreach ($migrations as $migration_class) {
            /** @var Migration $migration */
            $migration = new $migration_class();

            if (!$migration instanceof AbstractMigration) {
                throw new InvalidArgumentException("Migration must be a valid migration");
            }

            if ($log_callback) {
                $log_callback($migration_class);
            }

            if (Manager::schema()->hasTable($migration::getTableName())) {
                $migration->down();
            }
        }
    }

    public function upAllSources(callable $log_callback)
    {
        foreach ($this->getContext()->getAllSources() as $source_class => $source) {
            /** @var AbstractSource $source_class */
            $this->up($source_class::getMigrations(), $log_callback);
        }
    }

    /**
     * @return Magpie
     */
    public function getContext(): Magpie
    {
        return $this->context;
    }

    public function downAllSources(callable $log_callback)
    {
        foreach ($this->getContext()->getAllSources() as $source_class => $source) {
            /** @var AbstractSource $source_class */
            $this->down($source_class::getMigrations(), $log_callback);
        }
    }
}