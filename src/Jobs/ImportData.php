<?php

namespace Vcian\LaravelDataBringin\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vcian\LaravelDataBringin\Constants\Constant;
use Vcian\LaravelDataBringin\Models\ImportLog;
use Vcian\LaravelDataBringin\Services\ImportService;

class ImportData implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public bool $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     *
     * @param int $page
     * @param ImportLog $log
     */
    public function __construct(
        private int $page,
        private ImportLog $log
    ) {}

    /**
     * Execute the job.
     *
     * @param ImportService $service
     * @return void
     */
    public function handle(ImportService $service): void
    {
        try {
            $limit = ($this->page - 1) * Constant::PER_PAGE;
            $fileData = collect($service->csvToArray(storage_path("app/import/import.csv")))->skip($limit)->take(Constant::PER_PAGE);
            $table = $this->log->extra_data['table'];
            $columns = $this->log->extra_data['columns'];
            $failed = [];

            foreach ($fileData as $data) {
                try {
                    $prepareData = [];
                    foreach ($columns as $key => $column) {
                        $prepareData[$key] = $data[$column];
                    }
                    DB::table($table)->insert($prepareData);
                } catch (\Exception) {
                    $failed[] = $data;
                }
            }

            // Store success and failed count
            $this->log->increment('failed_count', count($failed));
            $this->log->increment('success_count', ($fileData->count() - count($failed)));
        } catch (\Exception $exception) {
            Log::error($exception);
        }
    }
}
