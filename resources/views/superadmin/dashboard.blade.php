@extends('layouts.superadmin')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-semibold text-gray-700">Total Users</h3>
        <p class="text-3xl font-bold text-blue-600">{{ $stats['total_users'] }}</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-semibold text-gray-700">Total Locations</h3>
        <p class="text-3xl font-bold text-green-600">{{ $stats['total_locations'] }}</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-semibold text-gray-700">Total Reviews</h3>
        <p class="text-3xl font-bold text-purple-600">{{ $stats['total_reviews'] }}</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-semibold text-gray-700">AI Replies</h3>
        <p class="text-3xl font-bold text-orange-600">{{ $stats['total_ai_replies'] }}</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow card">
        <h3 class="text-xl font-semibold text-gray-700">Total AI Usage</h3>
        <p class="text-3xl font-bold text-teal-600">
            ${{ $tokenData->isEmpty() ? '0.000000000' : number_format($totalCost, 9) }}</p>
    </div>
</div>
<div class="col flex justify-center mb-8 s12">
    @if(!$tokenData->isEmpty())
        <div id="tokenCostChart" class="bg-white p-6 rounded-lg shadow"></div>
    @else
        <p class="center-align">No cost data available.</p>
    @endif
</div>

<div class="card bg-white rounded-lg shadow p-6 mb-4">
    <div class="card-header py-3">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="mb-5 text-xl font-semibold text-gray-700">Recent Users</h6>
            </div>
            <div class="col-auto">
                <div class="btn-group" role="group">
                    <button id="desktopView" class="btn btn-primary btn-sm">
                        <i class="fa fa-desktop"></i> Desktop View
                    </button>
                    <button id="mobileView" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-mobile"></i> Mobile View
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-wrapper">
            <table class="table card-list-table" id="usersTable">
                <thead class="text-gray-700 uppercase bg-gray-400">
                    <tr>
                        <th id="name">Name</th>
                        <th id="email">Email</th>
                        <th id="loc">Locations</th>
                        <th id="date">Registered At</th>
                        <th id="act">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td data-title="Name">{{ $user->name }}</td>
                            <td data-title="Email">{{ $user->email }}</td>
                            <td id="locations" data-title="Locations">{{ $user->locations->count() }}</td>
                            <td id="date" data-title="Registered At">{{ $user->created_at->format('d M Y') }}</td>
                            <td id="act" data-title="Actions">
                                <a href="{{ route('superadmin.users.data', $user->id) }}" class="btn btn-primary btn-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


@push('styles')
    <style>
        #tokenCostChart {
            padding: 0.5rem 1rem 1rem 0.5rem;
            width: min(100%, 1200px) !important;
            background: white;
            /* height: 700px !important; */
            height: min(745px, 50vh) !important;
            margin: auto 0;
        }

        .col.flex.justify-center.mb-8.s12 {
            height: calc(min(745px, 50vh) - 1rem);
        }

        main.flex-1.p-8 {
            width: 100%;
        }

        .card {
            max-width: 100%;
        }

        ul.pagination {
            display: flex;
            gap: 1rem;
            padding: 0.5rem;

            >* {
                background: none;
            }
        }

        select,
        input {
            border-radius: 5px !important;
        }

        div#usersTable_filter {
            margin-bottom: 1rem;
        }

        td#locations {
            text-align: center;
        }

        td#act {
            color: blue;
            text-align: center;
        }

        td#date {
            text-align: center;
        }

        th#name::before,
        th#name::after,
        th#email::before,
        th#email::after {
            left: 6ch;
        }

        th#loc::before,
        th#loc::after {
            left: 10.5ch;
        }

        th#date::before,
        th#date::after {
            left: 13.5ch;
        }

        th#act::before,
        th#act::after {
            left: 8.5ch;
        }

        th::before,
        th:after {
            inset-block: 0;
        }

        tr.odd {
            background: whitesmoke;
        }

        .btn-group {
            gap: 1rem;
            display: flex;

            >* {
                border: 1px solid;
                border-radius: 5px;
                padding: 0.5rem 1rem;
                color: seagreen;
            }
        }

        .card-list-table {
            width: 100%;
            table-layout: fixed;
        }

        tr:hover td {
            background: whitesmoke;
        }

        @media (max-width: 768px) {
            .grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .large-screen .card-list-table thead {
                display: table-header-group;
            }

            .card-list-table:not(.large-screen) thead {
                display: none;
            }

            .card-list-table:not(.large-screen) tbody tr {
                display: block;
                margin-bottom: 10px;
                box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
            }

            .card-list-table:not(.large-screen) tbody td {
                display: block;
                text-align: right;
                position: relative;
                padding: 10px;
            }

            .card-list-table:not(.large-screen) tbody td:before {
                content: attr(data-title);
                position: absolute;
                left: 10px;
                width: 40%;
                text-align: left;
                font-weight: bold;
            }

            .card.bg-white.rounded-lg.shadow.p-6.mb-4 {
                padding: 0px;
            }

            td#locations {
                text-align: right;
            }

            td#date {
                text-align: right;
            }
        }

        @media (min-width: 768px) and (max-width: 1024px) {
            .grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
        }

        @media (min-width: 1024px) {
            .grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 2rem;
            }
        }
    </style>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tokenData = @json($tokenData);
            const labels = tokenData.map(data => data.grouped_time);
            const costs = tokenData.map(data => Number(data.total_cost));
            const options = {
                chart: {
                    type: 'line',
                    height: '100%',
                    width: '100%',
                    zoom: { enabled: true },
                    toolbar: { show: true }
                },
                series: [
                    {
                        name: 'Total AI Usage Cost ($)',
                        data: costs
                    }
                ],
                xaxis: {
                    categories: labels,
                    title: { text: 'Timestamp' }
                },
                yaxis: {
                    title: { text: 'Total Cost ($)' },
                    labels: {
                        formatter: function (value) {
                            return value.toFixed(9);
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return `$${value.toFixed(9)}`;
                        }
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                dataLabels: { enabled: false },
                grid: { borderColor: '#f1f1f1' }
            };
            const chart = new ApexCharts(document.querySelector("#tokenCostChart"), options);
            chart.render();
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#usersTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                language: {
                    searchPlaceholder: "Search users...",
                    search: "",
                },
                columnDefs: [
                    {
                        targets: 'no-sort',
                        orderable: false
                    }
                ]
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            const $table = $('.card-list-table');
            const $desktopBtn = $('#desktopView');
            const $mobileBtn = $('#mobileView');

            // Initial state
            $table.addClass('large-screen');

            $desktopBtn.on('click', function () {
                $table.addClass('large-screen');
                $desktopBtn.removeClass('btn-outline-secondary').addClass('btn-primary');
                $mobileBtn.removeClass('btn-primary').addClass('btn-outline-secondary');
            });

            $mobileBtn.on('click', function () {
                $table.removeClass('large-screen');
                $mobileBtn.removeClass('btn-outline-secondary').addClass('btn-primary');
                $desktopBtn.removeClass('btn-primary').addClass('btn-outline-secondary');
            });
        });
    </script>
@endpush
@endsection