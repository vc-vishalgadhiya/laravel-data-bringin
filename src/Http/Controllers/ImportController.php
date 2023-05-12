<?php

namespace Vcian\LaravelDataBringin\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Vcian\LaravelDataBringin\Http\Requests\ImportRequest;
use Vcian\LaravelDataBringin\Http\Requests\StoreImportRequest;
use Vcian\LaravelDataBringin\Models\ImportLog;
use Vcian\LaravelDataBringin\Services\ImportService;

class ImportController extends Controller
{
    /**
     * @param ImportService $importService
     */
    public function __construct(
        private ImportService $importService
    ) {}

    /**
     * @param ImportRequest $request
     * @return View|RedirectResponse
     */
    public function index(ImportRequest $request): View|RedirectResponse
    {
        if ($request->step > session('import.step') && $request->step != 4) {
            return to_route('data_bringin.index');
        }
        $data = [];
        $log = ImportLog::find(session('import.id'));
        $table = $request->table ?? ($log->extra_data['table'] ?? null);
        $data['tables'] = $this->importService->getTables();
        $data['tableColumns'] = $table ? $this->importService->getTableColumns($table) : collect();
        $data['selectedTable'] = $table;
        $data['selectedColumns'] = $log->extra_data['columns'] ?? collect();
        $data['fileColumns'] = $log ? $this->importService->getCsvColumns(storage_path("app/import/import.csv")) : collect();
        $data['fileData'] = $log ? $this->importService->csvToArray(storage_path("app/import/import.csv")) : collect();

        return view('data-bringin::import', $data);
    }

    /**
     * @param StoreImportRequest $request
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function store(StoreImportRequest $request): RedirectResponse
    {
        switch ($request->step) {
            case 1:
                session()->forget('import');
                $file = $request->file('file');
                $path = Storage::disk('local')->putFileAs('import', $file, 'import.csv');
                $log = ImportLog::create([
                    'total_count' => count($this->importService->csvToArray(storage_path("app/$path"))),
                    'file_name'  => $file->getClientOriginalName(),
                ]);
                session(['import.step' => 2, 'import.id' => $log->id]);
                break;
            case 2:
                $columns = collect($request->columns)->filter();
                if(!$columns->count()) {
                    return redirect()->back();
                }
                $log = ImportLog::findOrFail(session('import.id'));
                $log->extra_data = ['table' => $request->table, 'columns' => $columns];
                $log->save();
                session(['import.step' => 3]);
                break;
            case 3:
                $this->importService->saveData();
                session()->forget('import');
                break;
        }
        return to_route('data_bringin.index', ['step' => ++$request->step]);
    }

    public function deleteRecord(int $id): RedirectResponse
    {
        try {
            $data = collect(session('import.data'))->reject(function (array $data) use ($id) {
                return $data['Id'] == $id;
            })->values();
            session(['import.data' => $data]);

            return redirect()->back()->withSuccess('Record Deleted Successfully.');
        } catch (\Exception $exception) {
            return redirect()->back()->withError($exception->getMessage());
        }
    }
}
