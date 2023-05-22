<?php

namespace Vcian\LaravelDataBringin\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Vcian\LaravelDataBringin\Constants\Constant;
use Vcian\LaravelDataBringin\Jobs\ImportData;
use Vcian\LaravelDataBringin\Models\ImportLog;

/**
 * Import Service
 */
class ImportService
{
    /**
     * @return Collection
     */
    public function getTables(): Collection
    {
        return collect(Schema::getAllTables())->pluck('Tables_in_'.DB::connection()->getDatabaseName());
    }

    /**
     * @param string $table
     * @return Collection
     */
    public function getTableColumns(string $table): Collection
    {
        if (! Schema::hasTable($table)) {
            return collect();
        }

        return collect(DB::select("describe `{$table}`"))->map(function ($column) {
            return [
                'name' => $column->Field,
                'required' => $column->Null === 'NO',
            ];
        })->reject(function ($column) {
            return in_array($column['name'], ['id', 'deleted_at']);
        });
    }

    /**
     * @param string $fileName
     * @return array
     */
    public function csvToArray(string $fileName): array
    {
        // open csv file
        if (! ($fp = fopen($fileName, 'r'))) {
            return [];
        }

        //read csv headers
        $key = fgetcsv($fp, '1024', ',');

        // parse csv rows into array
        $data = [];
        while ($row = fgetcsv($fp, '1024', ',')) {
            $data[] = array_combine($key, $row);
        }

        // release file handle
        fclose($fp);

        // return data
        return $data;
    }

    /**
     * @param string $filePath
     * @return bool|array
     */
    public function getCsvColumns(string $filePath): bool|array
    {
        $file = fopen($filePath, 'r');
        $columns = fgetcsv($file, '1024', ',');
        fclose($file);
        return $columns;
    }

    /**
     * @param array $request
     * @param ImportLog $log
     * @return LengthAwarePaginator
     */
    public function getFileData(array $request, ImportLog $log): LengthAwarePaginator
    {
        $perPage = $request['perPage'] ?? Constant::PER_PAGE;
        $currentPage = Paginator::resolveCurrentPage();
        $data = $this->csvToArray(storage_path("app/{$log->file_path}"));
        $items = collect($data)->slice(($currentPage - 1) * $perPage, $perPage)->all();
        return new LengthAwarePaginator($items, count($data), $perPage, $currentPage, [
            'path' => Paginator::resolveCurrentPath(),
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function saveData(): void
    {
        $log = ImportLog::findOrFail(session('import.id'));
        $pages = ceil($log->total_count / Constant::IMPORT_PER_PAGE);
        $jobs = collect()->range(1, $pages)->map(fn($page) => new ImportData($page, $log));
        $batch = Bus::batch($jobs)
            ->name('Import Data')
            ->onQueue('imports')
            ->dispatch();
        $log->batch_id = $batch->id;
        $log->save();
    }

    /**
     * @param int $index
     * @return void
     */
    public function delete(int $index): void
    {
        $log = ImportLog::findOrFail(session('import.id'));

        // Read the file into an array
        $filePath = storage_path("app/{$log->file_path}");
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);

        // Check if the row index is valid
        if (isset($lines[$index])) {
            // Remove the row from the array
            unset($lines[$index]);

            // Write the updated array back to the file
            file_put_contents($filePath, implode(PHP_EOL, $lines));
            $log->decrement('total_count');
        }
    }

}
