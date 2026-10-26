<?php

namespace App\Console\Commands;

use App\Services\OneDriveService;
use Illuminate\Console\Command;

class SyncPendingOneDriveVoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onedrive:sync-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically move locally stored voice recordings to OneDrive and delete them from local disk once connection is restored';

    /**
     * Execute the console command.
     */
    public function handle(OneDriveService $oneDrive)
    {
        $this->info('Checking OneDrive connection and pending voice recordings...');

        if (!$oneDrive->isConnected()) {
            $this->error('OneDrive is not authenticated yet. Please visit /onedrive/login first.');
            return 1;
        }

        $result = $oneDrive->syncPendingVoiceResponses();

        if (!$result['success']) {
            $this->error($result['message'] ?? 'Sync failed.');
            return 1;
        }

        $synced = $result['synced'];
        $failed = $result['failed'];

        $this->info("Sync completed: {$synced} file(s) moved to OneDrive and deleted locally. {$failed} failed.");

        return 0;
    }
}
