<?php

namespace Ravenfire\Magpie\Data;

use Illuminate\Database\Capsule\Manager as Capsule;
use Ravenfire\Magpie\Magpie;

class DataManager
{
    /** @var Magpie */
    protected $context;

    /** @var Capsule */
    protected $capsule;

    public function __construct(Magpie $context, $should_init = true)
    {
        $this->setContext($context);
        if ($should_init) {
            $this->init();
        }
    }

    /**
     * @return void
     */
    public function init()
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'   => 'sqlite',
            'database' => MAGPIE_ROOT . '/data/db.sqlite',
        ]);

        // Make this Capsule instance available globally via static methods... (optional)
        $capsule->setAsGlobal();

        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $capsule->bootEloquent();

        $this->capsule = $capsule;
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
     * @return DataManager
     */
    public function setContext(Magpie $context): DataManager
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return Capsule
     */
    public function getCapsule(): Capsule
    {
        return $this->capsule;
    }

    /**
     * @param Capsule $capsule
     * @return DataManager
     */
    public function setCapsule(Capsule $capsule): DataManager
    {
        $this->capsule = $capsule;
        return $this;
    }
}