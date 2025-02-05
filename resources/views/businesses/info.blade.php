@extends('layouts.app')

@section('content')
<div class="breadcrumb">
    <a href="{{ url('/businesses') }}">Businesses</a>
</div>
<div class="card-content">
    <h4>Manage Businesses</h4>
    <p>View and manage your registered businesses.</p>
</div>
<div class="container profile-wrapper">
    <div class="businesses-section">

        <form id="syncForm" action="{{ route('business.sync.options') }}" method="GET" class="sync-form">
            @csrf
            <button type="submit" class="btn green darken-2" id="syncButton">
                <i class="material-icons">sync</i>
                Sync Business
            </button>
        </form>

        <h2>
            <i class="material-icons">business</i>
            My Businesses
        </h2>

        <div class="table-view">
            <table class="table" id="usersTable">
                <thead>
                    <tr>
                        <th>
                            <a
                                href="{{ route('businesses', ['sort' => $currentDirection === 'asc' ? 'desc' : 'asc', 'search' => $searchTerm]) }}">
                                Name
                                @if($currentDirection === 'asc')
                                    <i class="fa-solid fa-arrow-down-a-z"></i>
                                @else
                                    <i class="fa-solid fa-arrow-up-z-a"></i>
                                @endif
                            </a>
                        </th>
                        <th>Category</th>
                        <th style="width: 25%;">Description</th>
                        <th>Address</th>
                        <th>Mobile No.</th>
                        <th>Website</th>
                        <th id="qr">QR Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="businessTableBody">
                    @foreach($locations as $location)
                        <tr>
                            <td>{{ $location->title }}</td>
                            <td>{{ $location->primary_category }}</td>
                            <td title="{{ $location->description ?: 'No description available' }}">
                                {{ $location->description ?: 'No description available' }}
                            </td>
                            <td>{{ $location->formatted_address ?: 'Unknown address' }}</td>
                            <td>{{ $location->international_phone_number ?: 'Not available' }}</td>
                            <td>
                                <a style="color: blue;" href="{{ $location->website }}" target="_blank"
                                    title="{{ $location->website ?: 'Not available' }}">
                                    {{ $location->website ? 'Visit Site' : 'Not available' }}
                                </a>
                            </td>
                            <td id="qr">
                                <div class="qr-code-popup" id="qrCodePopup{{ $location->id }}">
                                    <div class="popup-content">
                                        <button type="button" class="close-btn" aria-label="Close"
                                            onclick="closeQrPopup('{{ $location->id }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24"
                                                width="24">
                                                <path d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                                            </svg>
                                        </button>
                                        <div id="qrCodeZoom{{ $location->id }}" class="qr-code-zoom"></div>
                                        <button class="download-btn"
                                            onclick="downloadQrCode('{{ $location->title }}', '{{ $location->id }}')"
                                            title="Download QR">
                                            <i class="material-icons">file_download</i> Download
                                        </button>
                                    </div>
                                </div>

                                <div class="qr-code-container" onclick="showQrPopup('{{ $location->id }}', this)">
                                    {!! $location->qr_code !!}
                                </div>
                            </td>

                            <td>
                                <div class="act-btn">
                                    <a href="{{ route('review_page', ['location' => $location->id]) }}"
                                        class="btn-icon green darken-2" title="Review Page">
                                        <i class="material-icons">rate_review</i>
                                    </a>
                                    <a href="{{ route('businesses.reviews', $location->id) }}" class="btn green darken-2">
                                        <i class="material-icons">reviews</i>
                                    </a>
                                </div>

                                @if (Auth::id() == $location->user_id)
                                    <form action="{{ route('businesses.delete', $location) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon red darken-2" title="Delete Business">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginated grid-wrapper -->
        @include('businesses.grid', ['locations' => $locations])

    </div>
</div>

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#usersTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                language: {
                    searchPlaceholder: "Search...",
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
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('businessSearch');
            const urlParams = new URLSearchParams(window.location.search);

            if (searchInput) {
                const currentSearch = urlParams.get('search');
                if (currentSearch) {
                    searchInput.value = currentSearch;
                }

                searchInput.addEventListener('input', debounce(handleSearch, 350));
                searchInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        handleSearch();
                    }
                });
            }
        });

        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        function handleSearch() {
            const searchInput = document.getElementById('businessSearch');
            if (!searchInput) return;

            const searchText = searchInput.value.trim();
            updateUrlAndRefresh('search', searchText);
        }

        function updateUrlAndRefresh(param, value) {
            const url = new URL(window.location.href);

            if (value) {
                url.searchParams.set(param, value);
            } else {
                url.searchParams.delete(param);
            }

            url.searchParams.set('page', '1');

            document.body.style.cursor = 'wait';

            window.location.href = url.toString();
        }

        function changePage(page) {
            const url = new URL(window.location.href);
            url.searchParams.set('page', page);
            window.location.href = url.toString();
        }

        function showQrPopup(locationId, element) {
            const qrCodePopup = document.getElementById(`qrCodePopup${locationId}`);
            const qrCodeZoom = document.getElementById(`qrCodeZoom${locationId}`);

            // Find the correct QR code inside the clicked row
            const qrCodeContainer = element;
            const svgElement = qrCodeContainer.querySelector('svg');

            if (!svgElement) {
                alert('QR code not found.');
                return;
            }

            // Clear and append the correct QR code
            qrCodeZoom.innerHTML = '';
            const clonedSvg = svgElement.cloneNode(true);
            qrCodeZoom.appendChild(clonedSvg);

            // Update the download button
            const downloadBtn = qrCodePopup.querySelector('.download-btn');
            downloadBtn.onclick = () => downloadQrCode(locationId, clonedSvg);

            // Show the popup
            qrCodePopup.style.display = 'flex';
        }

        function closeQrPopup(locationId) {
            document.getElementById(`qrCodePopup${locationId}`).style.display = 'none';
        }

        function downloadQrCode(title, svgElement) {
            const serializer = new XMLSerializer();
            const svgString = serializer.serializeToString(svgElement);
            const svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(svgBlob);

            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();

            img.onload = () => {
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
                URL.revokeObjectURL(url);

                const link = document.createElement('a');
                link.download = `${title}-qr-code.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            };

            img.src = url;
        }
    </script>
@endpush

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">

    <style>
        :root {
            --lineHeight: 1.79;
            --clamp: 4;
        }

        .business-selection-item {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }

        .business-selection-item:last-child {
            border-bottom: none;
        }

        .business-selection-item label {
            display: flex;
            align-items: center;
            color: rgba(0, 0, 0, 0.87);
        }

        .business-selection-item input[type="checkbox"] {
            margin-right: 12px;
        }

        .business-selection-item span {
            display: block;
            line-height: 1.4;
        }

        .business-selection-item small {
            color: rgba(0, 0, 0, 0.6);
        }

        #syncModal .modal-content {
            max-height: 70vh;
            overflow-y: auto;
        }

        #syncModal .modal-footer {
            padding: 16px;
        }

        .business-selection-item {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }

        .business-selection-item:last-child {
            border-bottom: none;
        }

        .business-selection-item label {
            display: flex;
            align-items: center;
            color: rgba(0, 0, 0, 0.87);
        }

        .business-selection-item input[type="checkbox"] {
            margin-right: 12px;
        }

        #syncModal .modal-content {
            max-height: 70vh;
            overflow-y: auto;
        }

        .sync-form {
            margin: 0;
            display: flex;
            justify-content: flex-end;

            .btn {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0 1.5rem;
                height: 42px;
                border-radius: 4px;
                font-weight: 500;
                text-transform: none;
            }

            .btn i {
                font-size: 20px;
            }
        }

        .card-content {
            display: grid;
            justify-items: center;
            margin-bottom: 1rem;
        }

        .businesses-section {
            background: ghostwhite;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease-in-out;

            h2 {
                margin-bottom: 1.5rem;
                padding-bottom: 0.5rem !important;
            }
        }

        form button.btn.green.darken-2 {
            display: flex;
            align-items: center;
            /* float: inline-end; */
        }

        .qr-code-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .popup-content {
            position: relative;
            background: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            max-width: 90%;
            max-height: 90%;
            overflow: auto;
        }

        .close-btn {
            position: relative;
            left: 100%;
            margin-left: -2.5rem;
            z-index: 9999;
            cursor: pointer;
            display: grid;
            grid-row: 3;
            background: hsl(240 100% 100% / 1);
            width: 2rem !important;
            padding: 3px;
            margin-bottom: 1rem;
            box-shadow: 1px 1px 5px hsl(0deg 0% 0% / 10%), -1px -1px 5px hsl(0deg 0% 0% / 10%);

            svg {
                scale: 1.29;
            }
        }

        .download-btn {
            margin-top: 1rem;
            background: hsl(215deg 37% 23%);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;

            i {
                vertical-align: middle;
            }
        }

        button#downloadQrCodeBtn {
            display: flex;
            margin: 0 !important;
            gap: 0.5rem;
            align-items: center;
            margin-top: 1rem;
            background: #43a047;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .download-btn:hover {
            background: #388e3c;
        }

        .qr-code-container {
            cursor: pointer;
        }

        #qrCodeZoom {
            margin: 1rem 0;
        }

        .act-btn {
            gap: 0.7rem;

            .green.darken-2 {
                width: max-content;
                display: flex;
                gap: 0.5rem;
                align-items: center;
                vertical-align: middle;
            }

        }

        input.form-control.form-control-sm {
            padding: 0 1rem !important;
        }

        .table-view {
            .row {
                display: flex;
                align-items: center;
            }

            label {
                border: 2px solid hsl(208deg 100% 89% / 90%);
                background: aliceblue;
                padding-inline: 2rem 1rem;
                display: inline-block;
            }

            .dataTables_filter {
                position: relative;
                display: flex;
                align-items: center;

                &::before {
                    content: "\1F50D";
                    position: absolute;
                    inset: 1ch auto auto 0;
                    width: 1.5rem;
                    height: 1.5rem;
                    font-size: 1.5rem;
                    color: #666;
                    margin-left: 0.5rem;
                }

                * {
                    float: inline-end;
                    width: -webkit-fill-available !important;
                }
            }
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            transition: all 0.3s ease-in-out;

            tbody {
                font-size: 1rem;
            }

            th,
            td {
                padding: 1rem 2rem;
                text-align: left;
                border-radius: 0 !important;
            }

            th::before,
            th::after {
                content: '' !important;
            }

            td#qr {
                place-items: center;
            }

            tr {
                border: 0 !important;

                &:hover,
                &:focus,
                &:focus-within {
                    background: hsl(165 23% 87%);
                }

                &:nth-child(1) {
                    &~tr {
                        border-top: 2px solid hsl(0 0% 75%) !important;
                    }
                }

                td {
                    &:nth-child(3) {
                        display: -webkit-box;
                        -webkit-line-clamp: var(--clamp);
                        -webkit-box-orient: vertical;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        border: 0 !important;
                        border-radius: 0 !important;
                        height: calc(var(--clamp)* 1rem* var(--lineHeight));
                        font-size: 1rem;
                        place-content: center;
                    }

                    &:last-child {
                        justify-items: center;

                        i {
                            font-size: 16px !important;
                        }

                        >* {
                            margin: 0 0 0.7rem 0 !important;
                            display: flex;
                            place-items: center;
                        }
                    }

                    &:nth-child(4) {
                        text-align: center;
                    }
                }
            }

            th {
                font-weight: bold;
                border-radius: 0 !important;
                width: 100vw;

                body.theme-light & {
                    background-color: hsl(from var(--bg-light) h 51% 85%);
                }
            }
        }

        .grid-wrapper {
            display: grid;
            gap: 3rem;
            transition: all 0.3s ease-in-out;
        }

        .business-card {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: transform 0.3s ease;
        }

        .business-header {
            padding: 1rem;
            border-left: solid 2px hsl(222deg 53% 43% / 69%);
        }

        .qr-code-container {
            border-radius: 7px;

            svg {
                border: 2.3px solid hsl(240deg 100% 1%);
                padding: 0.1rem;
                width: 100px;
                height: 100px;
            }
        }

        .green.darken-2 {
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            background-color: #388e3c;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;

            &:hover {
                background-color: #2e7d32;
            }
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
        }

        @media screen and (max-width: 1440px) {
            .table-view {
                display: none;
            }

            .grid-wrapper {
                display: grid;
            }

            .search-box {
                display: flex;
                align-items: center;
                gap: 1rem;
                border: 2px solid hsl(208deg 100% 89% / 90%);
                background: aliceblue;
                padding-inline: 1rem;
                margin-top: 0.5rem;

                .material-icons {
                    font-size: 1.5rem !important;
                }

                input {
                    padding: 0 1rem !important;
                }
            }

            .business-details>* {
                padding: 0.7rem 0.5rem;
                display: flex;
                align-items: center;
                gap: 0.37rem;
                border-bottom: 1px solid hsl(240deg 100% 1% / 62%);
                border-radius: 0 !important;

                &:last-child {
                    border-bottom: none !important;
                }
            }

            .qr-code-section {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 1rem;
                padding: 0.7rem 0.5rem;
                border: 1px solid hsl(240deg 100% 1% / 62%);
            }

            .qr-code-actions {
                display: flex;
                gap: 10px;
                align-items: center;
            }

            .act {
                gap: 1rem;
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
            }

            button.btn-flat.red-text,
            a.btn-flat.green-text {
                display: flex;
                float: inline-end;
                border: 1px solid;
                margin-block: 0.7rem;
                gap: 0.5rem;
                align-items: center;
            }

            a.btn-icon.green.darken-2 {
                width: max-content;
                gap: 0.5rem;
            }

            i {
                font-size: 16px !important;
            }
        }

        @media screen and (min-width: 1440px) {
            .grid-wrapper {
                display: none;
            }

            .table-view {
                display: block;
            }

            .red.darken-2 {
                background-color: #D32F2F !important;
                color: white;
            }
        }

        body {
            counter-reset: section;
        }

        h3::before {
            counter-increment: section;
            content: counter(section) ". ";
        }

        .material-icons {
            font-size: 20px;
            color: inherit;
            vertical-align: middle;

            p & {
                margin-right: 0.1rem;
            }
        }
    </style>
@endpush
@endsection