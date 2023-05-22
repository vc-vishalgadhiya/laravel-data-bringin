<?php

namespace Vcian\LaravelDataBringin\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Vcian\LaravelDataBringin\Constants\Constant;
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
        $data['fileColumns'] = $log ? $this->importService->getCsvColumns(storage_path("app/{$log->file_path}")) : collect();
        $data['fileData'] = $log ? $this->importService->getFileData($request->all(), $log) : collect();

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
                $path = Storage::disk('local')->putFileAs('import', $file, "import.csv");
                $log = ImportLog::create([
                    'total_count' => count($this->importService->csvToArray(storage_path("app/{$path}"))),
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
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

    /**
     * @param int $index
     * @return RedirectResponse
     */
    public function delete(int $index): RedirectResponse
    {
        try {
            $this->importService->delete($index);
            return redirect()->back()->withSuccess('Record Deleted Successfully.');
        } catch (\Exception $exception) {
            return redirect()->back()->withError($exception->getMessage());
        }
    }

    /**
     * @return View
     */
    public function logs(): View
    {
        return view('data-bringin::logs', [
            'logs' => ImportLog::latest()->paginate($request['perPage'] ?? Constant::PER_PAGE)
        ]);
    }

    /**
     * @param int $id
     * @return StreamedResponse
     */
    public function downloadFailedRecords(int $id): StreamedResponse
    {
        $log = ImportLog::findOrFail($id);
        return Storage::disk('local')->download($log->failed_file_path);
    }
}
