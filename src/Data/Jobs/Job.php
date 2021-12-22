<?php

namespace Ravenfire\Magpie\Data\Jobs;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    public static function createName(): string
    {
        $animals = ['walrus', 'tucan', 'anglefish', 'polar-bear'];
        $adjectives = ['gleeful', 'mournful', 'giddy', 'fervent', 'joyous', 'melancholy'];

        $animal = $animals[array_rand($animals)];
        $adjective = $adjectives[array_rand($adjectives)];

        return "{$adjective}-{$animal}-on-" . Carbon::now()->format("Y-m-d");
    }
}