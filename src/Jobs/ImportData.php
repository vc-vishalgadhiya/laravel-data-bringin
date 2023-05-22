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
use Illuminate\Support\Facades\Storage;
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
            $skip = ($this->page - 1) * Constant::IMPORT_PER_PAGE;
            $fileData = collect($service->csvToArray(storage_path("app/{$this->log->file_path}")))->skip($skip)->take(Constant::IMPORT_PER_PAGE);
            $table = $this->log->extra_data['table'];
            $columns = $this->log->extra_data['columns'];
            $failed = collect();

            foreach ($fileData as $data) {
                try {
                    $prepareData = [];
                    foreach ($columns as $key => $column) {
                        $prepareData[$key] = $data[$column];
                    }
                    DB::table($table)->insert($prepareData);
                } catch (\Exception) {
                    $failed->push($data);
                }
            }

            // Store success and failed count
            $this->log->increment('failed_count', $failed->count());
            $this->log->increment('success_count', ($fileData->count() - $failed->count()));

            // Store failed records in csv file
            if($failed->count() > 0) {
                if(is_null($this->log->failed_file_path)) {
                    $file = 'import/failed/'.time().'.csv';
                    $columns = implode(',', $service->getCsvColumns(storage_path("app/{$this->log->file_path}")));
                    Storage::disk('local')->put($file, $columns);
                    $this->log->failed_file_path = $file;
                    $this->log->save();
                }
                foreach ($failed as $fail) {
                    $row = implode(',', $fail);
                    Storage::disk('local')->append($this->log->failed_file_path, $row);
                }
            }
        } catch (\Exception $exception) {
            Log::error($exception);
        }
    }
}
