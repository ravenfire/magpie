<?php

namespace Ravenfire\Magpie\Application;

use Ravenfire\Magpie\Application\SqlScripts\SqlTraits;
use Ravenfire\Magpie\Magpie;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Lays foundation for the command files.
 */
class AbstractMagpieCommand extends Command
{
    use SqlTraits;

    /** @var bool */
    static protected $logger_initialized = false;

    /** @var Magpie */
    protected $context;

    /**
     * @param Magpie $context
     * @param $name
     */
    public function __construct(Magpie $context, $name = null)
    {
        $this->setContext($context);
        parent::__construct($name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        if (!static::isLoggerInitialized()) {
            $this->getContext()->getLogger()->pushHandler(new ConsoleHandler($output));
            static::setLoggerInitialized(true);
        }
    }

    /**
     * @return bool
     */
    public static function isLoggerInitialized(): bool
    {
        return self::$logger_initialized;
    }

    /**
     * @param bool $logger_initialized
     */
    public static function setLoggerInitialized(bool $logger_initialized): void
    {
        self::$logger_initialized = $logger_initialized;
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
     * @return AbstractMagpieCommand
     */
    public function setContext(Magpie $context): AbstractMagpieCommand
    {
        $this->context = $context;
        return $this;
    }

//    public function emergency($message, array $context = array())
//    {
//        $this->getContext()->getLogger()->emergency($message, $context);
//    }
//
//    public function alert($message, array $context = array())
//    {
//        $this->getContext()->getLogger()->alert($message, $context);
//    }
//
//    public function critical($message, array $context = array())
//    {
//        $this->getContext()->getLogger()->critical($message, $context);
//    }
//
//    public function error($message, array $context = array())
//    {
//        $this->getContext()->getLogger()->error($message, $context);
//    }
//
//    public function warning($message, array $context = array())
//    {
//        $this->getContext()->getLogger()->warning($message, $context);
//    }
//
//    public function notice($message, array $context = array())
//    {
//        $this->getContext()->getLogger()->notice($message, $context);
//    }
//
//    public function info($message, array $context = array())
//    {
//        $this->getContext()->getLogger()->info($message, $context);
//    }
//
//    public function debug($message, array $context = array())
//    {
//        $this->getContext()->getLogger()->debug($message, $context);
//    }
//
//    public function log($level, $message, array $context = array())
//    {
//        $this->getContext()->getLogger()->log($message, $context);
//    }
}