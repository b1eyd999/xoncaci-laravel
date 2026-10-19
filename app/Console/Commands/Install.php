<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;

/**
 * Runs on every deploy but does something only once: an empty database gets
 * the starting catalogue and the first admin. After that the owner's own
 * edits are never overwritten.
 */
class Install extends Command
{
    protected $signature = 'xonca:install';

    protected $description = 'Fill an empty database with the starting catalogue and the first admin';

    public function handle(): int
    {
        if (User::exists()) {
            $this->line('Already installed — nothing to do.');

            return self::SUCCESS;
        }

        if (blank(env('ADMIN_PASSWORD')) && app()->isProduction()) {
            $this->error('ADMIN_PASSWORD is not set in .env — refusing to create an admin with the sample password.');

            return self::FAILURE;
        }

        $this->call('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]);
        $this->info('Installed.');

        return self::SUCCESS;
    }
}
