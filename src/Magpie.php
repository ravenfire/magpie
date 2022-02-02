<?php

namespace Ravenfire\Magpie;

use Exception;
use InvalidArgumentException;
use Monolog\Logger;
use Ravenfire\Magpie\Application\ManageData\InstallCommand;
use Ravenfire\Magpie\Application\ManageData\ResetCommand;
use Ravenfire\Magpie\Application\ManageData\SetupCommand;
use Ravenfire\Magpie\Application\ManageData\TeardownCommand;
use Ravenfire\Magpie\Application\ManageData\UninstallCommand;
use Ravenfire\Magpie\Application\RunAllCommand;
use Ravenfire\Magpie\Application\SqlScripts\SqlCountScript;
use Ravenfire\Magpie\Application\SqlScripts\SqlFindScript;
use Ravenfire\Magpie\Application\SqlScripts\SqlJoinScript;
use Ravenfire\Magpie\Data\DataManager;
use Ravenfire\Magpie\Sources\AbstractPrimaryEntity;
use Ravenfire\Magpie\Sources\AbstractSource;
use Symfony\Component\Console\Application;

if (!defined('MAGPIE_ROOT')) {
    define('MAGPIE_ROOT', __DIR__ . '/../');
}

class Magpie
{
    /** @var Config */
    protected $config;

    /** @var Logger */
    protected $logger;

    /** @var DataManager */
    protected $data;

    /** @var AbstractPrimaryEntity */
    protected $primary_entity;

    /** @var AbstractSource[] */
    protected $sources = [];

    /** @var string[] */
    protected $sources_map = [];

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->data = new DataManager($this);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function run()
    {
//        Capsule::statement("insert into flights ('name', 'airline') VALUES ('a', 'me')");
//        
//        $r = Capsule::table('flights')->insert([
//            'name' => 'One',
//            'airline' => 'Southwest'
//        ]);
////
////        $r = Capsule::insert('flights', [
////            'name' => 'Two',
////            'airline' => 'United'
////        ]);
//        
//        $r = Capsule::table('flights')->get();
//        foreach ($r as $i) {
//            $b = true;
//        }
//        foreach (['A', 'B', 'C'] as $item) {
//            $this->logger->info($item);
//            sleep(3);
//        }

//        $b = $this->getSource('data-example');
//        die();
        $application = new Application();

        // Run Sources Commands
        $application->add(new RunAllCommand($this));

        // Migration Commands
        $application->add(new SetupCommand($this));
        $application->add(new TeardownCommand($this));
        $application->add(new InstallCommand($this));
        $application->add(new UninstallCommand($this));
        $application->add(new SqlCountScript($this));
        $application->add(new SqlJoinScript($this));
        $application->add(new SqlFindScript($this));


        // @Todo: I can't do any logging to the terminal until the console runs the application.
        $application->run();
    }

    /**
     * @return AbstractSource[]
     */
    public function getAllSources(): array
    {
        return $this->sources;
    }

    /**
     * @param AbstractSource[] $sources
     */
    public function setAllSources(array $sources = []): void
    {
        $this->sources = $sources;
    }

    /**
     * @param string $source_class that MUST implement AbstractSource
     * @return AbstractSource The source you just registered, so you can configure it more if you like
     * @throws Exception if $source_class does not exist
     */
    public function addSource(string $source_class): AbstractSource
    {
        if (!class_exists($source_class)) {
            throw new Exception("Could not find source `{$source_class}`");
        }

        $source = new $source_class($this);
        if (!$source instanceof AbstractSource) {
            throw new Exception("Source `{$source_class}` must extend " . AbstractSource::class);
        }

        $this->sources[$source_class] = $source;
        $this->sources_map[$source::getKey()] = $source_class;

        return $this->getSource($source_class);
    }

    /**
     * @param string $key_or_class
     * @return AbstractSource|null
     */
    public function getSource(string $key_or_class): ?AbstractSource
    {
        // Were we given the class?
        if (array_key_exists($key_or_class, $this->sources)) {
            return $this->sources[$key_or_class];
        }

        // Were we given the registered key? Look recursively
        if (array_key_exists($key_or_class, $this->sources_map)) {
            return $this->getSource($this->sources_map[$key_or_class]);
        }

        // This must not exist
        return null;
    }

    /**
     * @return string[]
     */
    public function getSourcesMap(): array
    {
        return $this->sources_map;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
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
     * @return Magpie
     */
    public function setLogger(Logger $logger): Magpie
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return DataManager
     */
    public function getData(): DataManager
    {
        return $this->data;
    }

    /**
     * @param DataManager $data
     * @return Magpie
     */
    public function setData(DataManager $data): Magpie
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param AbstractPrimaryEntity|string $primary_entity
     * @return Magpie
     */
    public function setPrimaryEntity($primary_entity): Magpie
    {
        if (is_string($primary_entity) and class_exists($primary_entity)) {
            $primary_entity = new $primary_entity($this);
        }

        if (!$primary_entity instanceof AbstractPrimaryEntity) {
            throw new InvalidArgumentException("Primary Entity Must Extend AbstractPrimaryEntity");
        }

        $this->primary_entity = $primary_entity;
        return $this;
    }

    /**
     * @return AbstractPrimaryEntity
     */
    public function getPrimaryEntity(): AbstractPrimaryEntity
    {
        return $this->primary_entity;
    }
}