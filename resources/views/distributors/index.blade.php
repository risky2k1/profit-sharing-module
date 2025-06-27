@extends('main')

@section('content')
    <h2 class="mb-4">Thống kê Nhà phân phối - {{ now()->month . '/' .now()->year }}</h2>
    <ul>
        <li>
            Tổng số Nhà phân phối: {{ $distributors->count() }}<br>
        </li>
        <li>
            Tổng doanh số: {{ number_format($reward->total_sales ?? 0) }}
        </li>
        <li>
            Tổng thưởng của từng NPP đủ điều kiện: {{ number_format($reward->reward_per_distributor ?? 0) }}
        </li>
    </ul>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Mã NPP</th>
            <th>Tên NPP</th>
            <th>Doanh số cá nhân</th>
            <th>Nhánh con</th>
            <th>DS Nhánh</th>
            <th>Danh hiệu</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($distributors as $index => $distributor)
            @php
                $stat = $distributor->monthlyStats->first();
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $distributor->code }}</td>
                <td>{{ $distributor->name }}</td>
                <td>{{ number_format($stat?->personal_sales ?? 0) }}</td>
                <td>
                    @if ($distributor->children->count())
                        <ul class="mb-0 ps-3">
                            @foreach ($distributor->children as $child)
                                @php
                                    $childStat = $child->monthlyStats->first();
                                @endphp
                                <li>
                                    <strong>{{ $child->code }}</strong> - {{ $child->name }}<br>
                                    <small>
                                        DS cá nhân: {{ number_format($childStat?->personal_sales ?? 0) }} |
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        Không có
                    @endif
                </td>
                <td>
                    {{ number_format($distributor->totalBranchSale() ?? 0) }}
                </td>
                <td>
                    @if ($stat->is_qualified)
                        <span class="badge bg-success">Đủ điều kiện</span>
                    @else
                        <span class="badge bg-danger">Không đủ</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>

    </table>
@endsection
