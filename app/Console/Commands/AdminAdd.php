<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AdminAdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:add {vatsim_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add VATSIM ID to admins';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $vatsim_id = $this->argument('vatsim_id');
        $user = User::find($vatsim_id);
        if (!$user) {
            $this->error('VATSIM ID not found!');
            return self::INVALID;
        }
        $user->setAttribute('isAdmin', true);
        $user->save();
        $this->info("VATSIM ID $vatsim_id added to admins");
        return self::SUCCESS;
    }
}
