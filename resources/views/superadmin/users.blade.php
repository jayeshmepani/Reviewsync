@extends('layouts.superadmin')

@section('title', 'Manage Users')

@section('content')

@if(session('success'))
    <div class="bg-green-500 text-white p-4 mb-4 rounded">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold">Manage Users</h2>
        <button onclick="openModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Add New User
        </button>
    </div>

    <!-- Users Table -->
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
                                <td id="act" class="px-6 py-4 whitespace-nowrap space-x-2">
                                    <a href="{{ route('superadmin.users.edit', $user->id) }}"
                                        class="text-blue-600 hover:text-blue-900">Edit</a>
                                    <a href="{{ route('superadmin.users.data', $user->id) }}"
                                        class="text-green-600 hover:text-green-900">View Data</a>
                                    <form action="{{ route('superadmin.users.delete', $user->id) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Add New User -->
    <div id="addUserModal" class="fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
            <h2 class="text-2xl font-semibold mb-6">Add New User</h2>

            <form id="createUserForm" action="{{ route('superadmin.users.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                    <input type="text" name="name" id="name"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" name="email" id="email"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" name="password" id="password"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Confirm
                        Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Create User
                    </button>
                    <button type="button" class="text-gray-600 hover:text-gray-800"
                        onclick="closeModal()">Cancel</button>
                </div>
            </form>

        </div>
    </div>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
        <script>
            function openModal() {
                document.getElementById('addUserModal').classList.remove('hidden');
            }

            function closeModal() {
                document.getElementById('addUserModal').classList.add('hidden');
            }
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
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
        <style>
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
    @endpush
    @endsection