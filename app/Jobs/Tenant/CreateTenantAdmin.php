<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant;
use App\Models\Tenant\Organization;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateTenantAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Tenant $tenant)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->tenant->run(function ($tenant) {
            $user = User::create($tenant->only('name', 'email', 'password'));
            $user->assignRole('Admin');
            Log::debug("Data => " . json_encode($tenant->only('company_name')));
            $data = $tenant->only('company_name');
            Organization::create(['company_name' => $data['company_name']]);
        });
    }
}
