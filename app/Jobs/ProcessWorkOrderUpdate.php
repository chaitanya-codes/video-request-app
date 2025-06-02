<?php

namespace App\Jobs;

use App\Models\WorkorderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWorkOrderUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $workOrderStatus;
    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct(WorkorderStatus $workOrderStatus)
    {
        $this->workOrderStatus = $workOrderStatus;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        \Log::info('Job triggered for workOrderStatus ID: ' . $this->workOrderStatus->id);
    }
}
