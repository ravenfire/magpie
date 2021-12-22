<?php

namespace Ravenfire\Magpie\Sources;

use Illuminate\Database\Eloquent\Model;
use Ravenfire\Magpie\Magpie;

abstract class AbstractPrimaryEntity
{
    /** @var Magpie */
    protected $context;

    public function __construct(Magpie $context)
    {
        $this->setContext($context);
    }

    /**
     * @return string
     */
    abstract static public function getKey(): string;

    /**
     * @return string|Model
     */
    abstract static public function getModelClass(): string;

    /**
     * [
     *     'table' => MigrationClass::class
     * ]
     * @return string[]
     */
    abstract public static function getMigrations(): array;

    /**
     * @return Magpie
     */
    public function getContext(): Magpie
    {
        return $this->context;
    }

    /**
     * @param Magpie $context
     * @return AbstractPrimaryEntity
     */
    public function setContext(Magpie $context): AbstractPrimaryEntity
    {
        $this->context = $context;
        return $this;
    }
}