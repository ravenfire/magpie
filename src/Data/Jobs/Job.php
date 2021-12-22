<?php

namespace Ravenfire\Magpie\Data\Jobs;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    public function __construct(array $attributes = [])
    {
        if (!array_key_exists('name', $attributes)) {
            $attributes['name'] = static::createName();
        }
        parent::__construct($attributes);
    }

    public static function createName(): string
    {
        $animals = ['walrus', 'tucan', 'anglefish', 'polar-bear'];
        $adjectives = ['gleeful', 'mournful', 'giddy', 'fervent', 'joyous', 'melancholy'];
        $tag = array_rand($adjectives) . "-" . array_rand($animals);

        return "{$tag}-on-" . Carbon::now()->format("Y-m-d");
    }
}