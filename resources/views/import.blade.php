@extends('data-bringin::layouts.app')

@section('content')
    <div class="wizard cus-form-wizard">
        <div class="wizard-inner">
            <div class="connecting-line"></div>
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" @class(['active' => (request()->step ?? 1) == 1])>
                    <a data-toggle="tab" aria-controls="step1" role="tab">
                        <span class="number"><i class="fa fa-upload"></i></span>
                        <span class="desc">
                            <span class="name">Upload CSV</span>
                            <span class="short-des">Import your CSV file and follow the steps</span>
                        </span>
                        <span class="count-step">1</span>
                    </a>
                </li>
                <li role="presentation" @class(['active' => request()->step == 2, 'disabled' => request()->step < 2])>
                    <a data-toggle="tab" aria-controls="step2" role="tab">
                        <span class="number"><i class="fa fa-object-group"></i></span>
                        <span class="desc">
                            <span class="name">Mapping</span>
                            <span class="short-des">Map the CSV columns with selected table column</span>
                        </span>
                        <span class="count-step">2</span>
                    </a>
                </li>
                <li role="presentation"
                    @class(['active' => request()->step == 3, 'disabled' => request()->step < 3]) class="disabled">
                    <a data-toggle="tab" aria-controls="step3" role="tab">
                        <span class="number"><i class="fa fa-gear"></i></span>
                        <span class="desc">
                            <span class="name">Manage</span>
                            <span class="short-des">After uploading you can review data and manage data</span>
                        </span>
                        <span class="count-step">3</span>
                    </a>
                </li>
                <li role="presentation" @class(['disabled' => request()->step < 4]) class="disabled">
                    <a data-toggle="tab" aria-controls="summarydata" role="tab">
                        <span class="number"><i class="fa fa-file-excel-o"></i></span>
                        <span class="desc">
                            <span class="name">Result</span>
                            <span class="short-des">See the imported data result with success or error</span>
                        </span>
                        <span class="count-step">4</span>
                    </a>
                </li>
            </ul>
        </div>
        <div id="bar" class="progress progress-striped" role="progressbar">
            <div class="progress-bar progress-bar-success"
                 style="width: {{ round((request()->step ?? 1) * 100 / 4, 2) }}%"></div>
        </div>
        <div class="form-horizontal">
            <div class="tab-content">
                <div
                    @class(['active' => (request()->step ?? 1) == 1, 'tab-pane', 'first-step']) role="tabpanel"
                    id="step1">
                    <form method="post" action="{{ route('data_bringin.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="step" value="{{ request()->step ?? 1 }}">
                        <div class="row">
                            <div class="import-contact-list">
                                <div class="import-box-list col-lg-12 col-sm-6">
                                    <div class="import-contact-item">
                                        <div class="import-contact-heading">
                                            <h2>Import CSV File</h2>
                                        </div>
                                        <div class="import-info-box upload-section">
                                            <div class="form-group upload-csv">
                                                <label>Upload CSV File</label>
                                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                                    <div class="input-group">
                                                        <input class="form-control @error('file') is-invalid @enderror"
                                                               type="file"
                                                               name="file"
                                                               id="file"
                                                               accept=".csv"/>
                                                        @error('file')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group upload-csv import-csv-box">
                                                <div class="download-save-continue">
                                                    <div class="save-and-continue">
                                                        <button type="submit"
                                                                class="btn btn-primary next-step continue-step1">Next
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="info-content"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                @if(request()->step == 2)
                    <div @class(['active' => request()->step == 2, 'tab-pane']) role="tabpanel" id="step2">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="note wizard-cus-note">
                                    <span class="label label-danger">NOTE!</span>
                                    <span class="bold">Please match column with the original form value.</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <span class="errorMsg" style="color: red; display: none;"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <form method="get">
                                    <div class="form-group">
                                        <input type="hidden" name="step" value="{{ request()->step }}">
                                        <div class="select-dropdown tab-content imported_data table_header">
                                            <label>Select Database Table</label>
                                            <select class="select @error('table') is-invalid @enderror" name="table" onchange="this.form.submit()">
                                                <option selected disabled>Select Table</option>
                                                @foreach($tables as $table)
                                                    <option @selected($table == $selectedTable)>
                                                        {{ $table }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('table')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <form method="post" action="{{ route('data_bringin.store') }}">
                            @csrf
                            <input type="hidden" name="step" value="{{ request()->step }}">
                            <input type="hidden" name="table" value="{{ $selectedTable }}">
                            <div class="form-group mapping-step">
                                <div class="col-md-12 col-sm-12">
                                    @if($selectedTable)
                                        <div id="sample_1_wrapper">
                                            <div class="mapping-viewtable mCustomScrollbar _mCS_1">
                                                <div id="mCSB_1"
                                                     class="mCustomScrollBox mCS-light-3 mCSB_horizontal mCSB_inside"
                                                     style="max-height: none;" tabindex="0">
                                                    <div id="mCSB_1_container" class="mCSB_container"
                                                         style="position: relative; top: 0px; left: 0px; width: 771px; min-width: 100%; overflow-x: inherit;"
                                                         dir="ltr">
                                                        <table
                                                            class="table imported_data maping-table table table-striped table-bordered table-hover manage-taskTbl dataTable"
                                                            role="grid" aria-describedby="sample_1_info">
                                                            <thead>
                                                            <tr class="header_row">
                                                                <th>Database Table Column</th>
                                                                <th>CSV File Column</th>
                                                            </tr>
                                                            @foreach($tableColumns as $column)
                                                            <tr>
                                                                <th>
                                                                    {{ $column['name'] }}
                                                                    @if($column['required'])
                                                                        <span class="text-danger">*</span>
                                                                    @endif
                                                                </th>
                                                                <th>
                                                                    <div class="select-dropdown">
                                                                        <select class="firstname-class"
                                                                                name="columns[{{$column['name']}}]">
                                                                            <option selected value=''>Select
                                                                                Column
                                                                            </option>
                                                                            @foreach($fileColumns as $val)
                                                                                <option @selected(isset($selectedColumns[$column['name']]) && $selectedColumns[$column['name']] == $val)>{{ $val }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </th>
                                                            </tr>
                                                            @endforeach
                                                            </thead>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-actions">
                                        <ul class="list-inline">
                                            <li>
                                                <a href="{{ route('data_bringin.index', ['step' => --request()->step]) }}"
                                                   class="btn btn-default prev-step">Previous</a>
                                            </li>
                                            <li>
                                                <button type="submit"
                                                        class="btn btn-primary next-step continue-step2">Continue
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
                @if(request()->step == 3)
                    <div @class(['active' => request()->step == 3, 'tab-pane']) role="tabpanel" id="step3">
                        <form method="post" action="{{ route('data_bringin.store') }}">
                            @csrf
                            <input type="hidden" name="step" value="{{ request()->step }}">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <div id="sample_2_wrapper">
                                        <div class="main-table import-table-scroll">
                                            <div id="sample_1_wrapper" class="dataTables_wrapper no-footer DTS">
                                                <div class="dataTables_scrollBody">
                                                    <div class="dataTables_scrollHead">
                                                        <div class="dataTables_scrollHeadInner">
                                                            <table
                                                                class="table import-table table-hover table-bordered imported_data dataTable display nowrap"
                                                                role="grid"
                                                                aria-describedby="sample_1_info"
                                                            >
                                                                <thead class="header">
                                                                <tr role="row">
                                                                    <th></th>
                                                                    <th>Id</th>
                                                                    @foreach($tableColumns as $column)
                                                                        @if(isset($selectedColumns[$column['name']]))
                                                                            <th>{{ $column['name'] }}</th>
                                                                        @endif
                                                                    @endforeach
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                @foreach($fileData as $key => $data)
                                                                    <tr role="row" class="odd" id="record1">
                                                                        <td class="text-center">
                                                                            <a href="{{ route('data_bringin.delete', ++$key) }}"
                                                                               onclick="return confirm('Are you sure you want to delete this record?')"
                                                                               class="bg-transparent border-0 p-0">
                                                                                <i class="fa fa-trash text-danger"></i>
                                                                            </a>
                                                                        </td>
                                                                        <td>{{ $key }}</td>
                                                                        @foreach($tableColumns as $column)
                                                                            @if(isset($selectedColumns[$column['name']]))
                                                                                <td>{{ $data[$selectedColumns[$column['name']]] ?? '' }}</td>
                                                                            @endif
                                                                        @endforeach
                                                                    </tr>
                                                                @endforeach
                                                                </tbody>
                                                            </table>
                                                            {{ $fileData->withQueryString()->links() }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-actions">
                                        <ul class="list-inline">
                                            <li>
                                                <a href="{{ route('data_bringin.index', ['step' => --request()->step]) }}"
                                                   class="btn btn-default prev-step">Previous</a>
                                            </li>
                                            <li>
                                                <button type="submit"
                                                        class="btn btn-primary btn-info-full next-step continue-step3">
                                                    Save
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
                @if(request()->step == 4)
                    <div @class(['active' => request()->step == 4, 'tab-pane']) role="tabpanel" id="summarydata">
                        <div class="form-group">
                            <div class="col-md-12">
                                <div id="sample_3_wrapper">
                                    <div class="note note-success">
                                        <p><b>Uploaded CSV file data is importing. Please click <a href="{{ route('data_bringin.logs') }}" title="Logs" target="_blank">here</a> to view the result.</b> &nbsp;<i
                                                class="fa fa-check-circle greeg-color"></i></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-actions">
                                    <ul class="list-inline">
                                        <li>
                                            <a href="{{ route('data_bringin.index') }}"
                                               class="btn btn-primary next-step continue-step4">Finish</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
