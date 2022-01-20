<?php

namespace Ravenfire\Magpie\Sources;

use Illuminate\Database\Eloquent\Model;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Ravenfire\Magpie\Data\Audit\Audit;
use Ravenfire\Magpie\Data\Jobs\Job;
use Ravenfire\Magpie\Data\Migrations\MigrationManager;
use Ravenfire\Magpie\Magpie;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
abstract class AbstractSource implements LoggerInterface
{
    /** @var Magpie */
    protected $context;

    /** @var Logger */
    protected $logger;

    /** @var array */
    protected $default_logging_context = [];

    /** @var bool */
    protected $console_initialized = false;

    /** @var Job */
    private $job;

    abstract static public function getPrimaryEntityJoinColumnName();

    /**
     * @return string
     */
    abstract static public function getKey(): string;

    /**
     * [
     *     'table' => MigrationClass::class
     * ]
     * @return string[]
     */
    public function findChanges(Model $newData, Model $existing_source, $columnsToCheck)
    {
        $changes = [];
        foreach ($columnsToCheck as $columnToCheck) {
            if ($newData->$columnToCheck != $existing_source->$columnToCheck) {
                $existing_source->$columnToCheck = $newData->$columnToCheck;
                $changes[$columnToCheck] = $newData;
            }
        }
        return $changes;
    }

    /**
     * @return array
     */
    public static function getMigrations(): array
    {
        return [];
    }

    /**
     *
     */
    public function __construct(Magpie $context)
    {
        $this->setContext($context);
        $this->setLogger($context->getLogger()->withName(static::getKey()));
        // @todo: this may not be the best place for this
    }

    /**
     * Creates a new Eloquent Model for each row and calls `$this->save($model)`
     * Also logs as desired, which is saved as well
     * @return void
     */
    abstract public function execute();

    /**
     * @return void
     */
    public function onInstall()
    {
    }

    /**
     * @return void
     */
    public function onUnInstall()
    {
    }

    /**
     * @param Job $job
     * @param OutputInterface $output
     * @return void
     */
    public function run(Job $job, OutputInterface $output)
    {
        $this->setJob($job);
        $this->initConsole($output);
        $this->execute();
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    protected function initConsole(OutputInterface $output)
    {
        if (!$this->isConsoleInitialized()) {
            // @todo: Check to see if this has already been done
            $console_handler = new ConsoleHandler($output);

            // Remove the Context for the Console
            $console_handler->pushProcessor(function ($record) {
                $record['context'] = [];
                return $record;
            });

            $this->getLogger()->pushHandler($console_handler);
            $this->setConsoleInitialized(true);
        }
    }

    /**
     * @return void
     */
    public function install()
    {
        $migrations = new MigrationManager($this->getContext());
        $migrations->up(
            static::getMigrations(),
            function ($migration_class) {
                $this->debug("Installing Migration: `{$migration_class}`");
            }
        );

        $this->onInstall();
    }

    /**
     * @return void
     */
    public function uninstall()
    {
        $migrations = new MigrationManager($this->getContext());
        $migrations->down(
            array_reverse(static::getMigrations()),
            function ($migration_class) {
                $this->alert("Uninstalling Migration: `{$migration_class}`");
            }
        );

        $this->onUnInstall();
    }

    /**
     * Checks for new primary entity and source info, updated source info, and updates the audit table as needed.
     *
     * @param Model $source
     * @param $primary
     * @param $existing_source
     * @param array $sourceColumns
     * @return void
     */
    protected function save(Model $source, $primary, $existing_source, array $sourceColumns)
    {
        // Create new primary entity and source if game doesn't already exist.
        if ($primary !== null) {
            $primary->save();
            $column_name = static::getPrimaryEntityJoinColumnName();
            $source->{$column_name} = $primary->id;
            $source->save();
        }

        // Update source if changes are found.
        if ($existing_source !== null) {
            $changes = $this->findChanges($source, $existing_source, $sourceColumns);
            $source->id = $existing_source->id;
            $existing_source->save();
        } else {
            $changes = null;
        }

        // Creates a new Audit Record for new source info or if something changed.
        $this->updateAuditTable($source, $changes);
    }

    /**
     * Checks for and only saves new and updated data.
     *
     * @param $source
     * @param $changes
     * @return false|void
     */
    public function updateAuditTable($source, $changes)
    {
        if ($changes === []) {
            return false;
        }

        if ($changes !== null) {
            foreach ($changes as $key => $value) {
                $audit = new Audit();
                $this->essentialAuditColumns($source, $audit);
                $audit->column_name = json_encode($key);
                $audit->old_value = json_encode($source[$key]);
                $audit->new_value = json_encode($value);
                $audit->save();
            }
            return false;
        }

        $audit = new Audit();
        $this->essentialAuditColumns($source, $audit);
        $audit->save();
    }

    /**
     * Populates the essential audit columns
     *
     * @param $source
     * @param $audit
     * @return void
     */
    public function essentialAuditColumns($source, $audit)
    {
        $audit->job_id = $this->getJob()->id;
        $audit->record_id = $source->id;
        $audit->source_key = static::getKey();
    }

    /**
     * @param array $context
     * @return array
     */
    protected function buildContext(array $context): array
    {
        return array_merge($context, $this->buildDefaultLoggingContext());
    }

    /**
     * @return array
     */
    protected function buildDefaultLoggingContext(): array
    {
        return array_merge($this->getSourceLoggingContext(), $this->getDefaultLoggingContext());
    }

    /**
     * @return array[]
     */
    private function getSourceLoggingContext(): array
    {
        return [
            'source' => [
                'key' => static::getKey(),
                'job_id' => $this->getJob()->id ?? null,
            ]
        ];
    }

    /**
     * @return bool
     */
    public function isConsoleInitialized(): bool
    {
        return $this->console_initialized;
    }

    /**
     * @param bool $console_initialized
     */
    public function setConsoleInitialized(bool $console_initialized): void
    {
        $this->console_initialized = $console_initialized;
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     * @return AbstractSource
     */
    public function setLogger(Logger $logger): AbstractSource
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return Magpie
     */
    public function getContext(): Magpie
    {
        return $this->context;
    }

    /**
     * @param Magpie $context
     * @return AbstractSource
     */
    public function setContext(Magpie $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return Job
     */
    public function getJob(): ?Job
    {
        return $this->job;
    }


    /**
     * @param Job $job
     * @return AbstractSource
     */
    public function setJob(Job $job): AbstractSource
    {
        $this->job = $job;
        return $this;
    }

    public function debug($message, array $context = array())
    {
        $this->getLogger()->debug($message, $this->buildContext($context));
    }

    /**
     * @return array
     */
    public function getDefaultLoggingContext(): array
    {
        return $this->default_logging_context;
    }

    /**
     * @param array $default_logging_context
     * @return AbstractSource
     */
    public function setDefaultLoggingContext(array $default_logging_context): AbstractSource
    {
        $this->default_logging_context = $default_logging_context;
        return $this;
    }

    public function alert($message, array $context = array())
    {
        $this->getLogger()->alert($message, $this->buildContext($context));
    }

    public function emergency($message, array $context = array())
    {
        $this->getLogger()->emergency($message, $this->buildContext($context));
    }

    public function critical($message, array $context = array())
    {
        $this->getLogger()->critical($message, $this->buildContext($context));
    }

    public function error($message, array $context = array())
    {
        $this->getLogger()->error($message, $this->buildContext($context));
    }

    public function warning($message, array $context = array())
    {
        $this->getLogger()->warning($message, $this->buildContext($context));
    }

    public function notice($message, array $context = array())
    {
        $this->getLogger()->notice($message, $this->buildContext($context));
    }

    public function info($message, array $context = array())
    {
        $this->getLogger()->info($message, $this->buildContext($context));
    }

    public function log($level, $message, array $context = array())
    {
        $this->getContext()->getLogger()->log($level, $message, $this->buildContext($context));
    }
}