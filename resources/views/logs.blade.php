@extends('data-bringin::layouts.app')

@section('content')
    <div class="wizard cus-form-wizard">
        <div class="form-horizontal">
            <div class="form-group">
                <div class="col-md-12">
                    <div id="sample_2_wrapper">
                        <div class="main-table import-table-scroll">
                            <div id="sample_1_wrapper" class="dataTables_wrapper no-footer DTS">
                                <div class="dataTables_scrollBody">
                                    <div class="dataTables_scrollHead">
                                        <div class="dataTables_scrollHeadInner">
                                            <table
                                                class="table import-table table-hover table-bordered imported_data dataTable no-footer display nowrap"
                                                role="grid"
                                                aria-describedby="sample_1_info"
                                            >
                                                <thead class="header">
                                                <tr role="row">
                                                    <th>Id</th>
                                                    <th>Total Records</th>
                                                    <th>Success Records</th>
                                                    <th>Failed Records</th>
                                                    <th>Download Failed Records</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($logs as $log)
                                                <tr>
                                                    <td>{{ $log->id }}</td>
                                                    <td>{{ $log->total_count }}</td>
                                                    <td>{{ $log->success_count }}</td>
                                                    <td>{{ $log->failed_count }}</td>
                                                    <td>
                                                        @if($log->failed_count > 0)
                                                            <a href="{{ route('data_bringin.failed_records.download', $log) }}">
                                                                <i class="fa fa-download" aria-hidden="true"></i>
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                            {{ $logs->links() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
