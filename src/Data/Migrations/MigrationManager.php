<?php

namespace Ravenfire\Magpie\Data\Migrations;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use InvalidArgumentException;
use Ravenfire\Magpie\Data\Audit\AuditTableMigration;
use Ravenfire\Magpie\Data\Jobs\JobsTableMigration;
use Ravenfire\Magpie\Data\Logs\LogsTableMigration;
use Ravenfire\Magpie\Magpie;
use Ravenfire\Magpie\Sources\AbstractSource;

/**
 *
 */
class MigrationManager
{
    /**
     * @var string[]
     */
    static protected $migrations = [
        LogsTableMigration::class,
        AuditTableMigration::class,
        JobsTableMigration::class
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

    /**
     * @param callable $log_callback
     * @return void
     */
    public function upMagpie(callable $log_callback)
    {
        $this->up(static::getMigrations(), $log_callback);
    }

    /**
     * @param array $migrations
     * @param callable|null $log_callback
     * @return void
     */
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

    /**
     * @param callable $log_callback
     * @return void
     */
    public function downMagpie(callable $log_callback)
    {
        $this->down(static::getMigrations(), $log_callback);
    }

    /**
     * @param array $migrations
     * @param callable|null $log_callback
     * @return void
     */
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

    /**
     * @param callable $log_callback
     * @return void
     */
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

    /**
     * @param callable $log_callback
     * @return void
     */
    public function downAllSources(callable $log_callback)
    {
        foreach ($this->getContext()->getAllSources() as $source_class => $source) {
            /** @var AbstractSource $source_class */
            $this->down($source_class::getMigrations(), $log_callback);
        }
    }
}