<?php

namespace Ravenfire\Magpie\Sources;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Ravenfire\Magpie\Data\MigrationManager;
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
     * @param OutputInterface $output
     * @return void
     */
    public function run(OutputInterface $output)
    {
        $this->initConsole($output);
        $this->execute();
    }

    /**
     * @return void
     */
    abstract public function execute();

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
    public function onInstall()
    {
    }

    public function onUnInstall()
    {
    }

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

    protected function buildContext(array $context): array
    {
        return array_merge($context, $this->buildDefaultLoggingContext());
    }

    protected function buildDefaultLoggingContext(): array
    {
        return array_merge($this->getSourceLoggingContext(), $this->getDefaultLoggingContext());
    }

    private function getSourceLoggingContext(): array
    {
        return [
            'source' => [
                'key' => static::getKey(),
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