<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('demo:install {--fresh : Wipe all data and reseed (used to reset the public demo)}')]
#[Description('Run migrations and seed demo data when the database is empty.')]
class DemoInstall extends Command
{
    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->call('migrate:fresh', ['--force' => true, '--seed' => true]);

            return self::SUCCESS;
        }

        $this->call('migrate', ['--force' => true]);

        // Demo data from before course photos existed: reseed once so the catalog gets its images.
        if (Course::where('slug', 'laravel-basics')->whereNull('image_url')->exists()) {
            $this->call('migrate:fresh', ['--force' => true, '--seed' => true]);

            return self::SUCCESS;
        }

        if (Course::count() === 0) {
            $this->call('db:seed', ['--force' => true]);
        }

        return self::SUCCESS;
    }
}
