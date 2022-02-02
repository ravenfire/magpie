<?php

namespace Ravenfire\Magpie\Data\Audit;

use Illuminate\Database\Eloquent\Model;

/**
 * Establishes model for Audit.
 */
class Audit extends Model
{
    /**
     * @var string
     */
    protected $table = 'audit';
}