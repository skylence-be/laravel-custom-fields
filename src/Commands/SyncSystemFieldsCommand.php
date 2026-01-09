<?php

declare(strict_types=1);

namespace Xve\LaravelCustomFields\Commands;

use Illuminate\Console\Command;
use Xve\LaravelCustomFields\Services\SystemFieldsRegistry;

class SyncSystemFieldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom-fields:sync-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync registered system fields to the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Syncing system fields...');

        $fields = SystemFieldsRegistry::all();

        if (empty($fields)) {
            $this->warn('No system fields registered.');

            return self::SUCCESS;
        }

        $this->table(
            ['Model', 'Fields'],
            collect($fields)->map(fn ($modelFields, $model) => [
                str($model)->afterLast('\\')->toString(),
                count($modelFields),
            ])->toArray()
        );

        $result = SystemFieldsRegistry::sync();

        $this->info("Created: {$result['created']} field(s)");
        $this->info("Updated: {$result['updated']} field(s)");

        return self::SUCCESS;
    }
}
