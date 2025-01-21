@extends('layouts.app')

@section('content')
<div class="card-content">
    <h4>Manage Businesses</h4>
    <p>View and manage your registered businesses.</p>
</div>
<div class="container profile-wrapper">
    <div class="businesses-section">
        <div class="controls-wrapper">
            <div class="search-box">
                <i class="material-icons">search</i>
                <input type="text" id="businessSearch" placeholder="Search by business name...">
            </div>

            <form id="syncForm" action="{{ route('business.sync.options') }}" method="GET" class="sync-form">
                @csrf
                <button type="submit" class="btn green darken-2" id="syncButton">
                    <i class="material-icons">sync</i>
                    Sync Business
                </button>
            </form>
        </div>

        <h2>
            <i class="material-icons">business</i> 
            My Businesses
        </h2>

        <div class="table-view">
            <table class="table">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('businesses', ['sort' => $currentDirection === 'asc' ? 'desc' : 'asc', 'search' => $searchTerm]) }}">
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
                                <a style="color: blue;" href="{{ $location->website }}" target="_blank" title="{{ $location->website ?: 'Not available' }}">
                                    {{ $location->website ? 'Visit Site' : 'Not available' }}
                                </a>
                            </td>
                            <td id="qr">
                                <div class="qr-code-popup" id="qrCodePopup">
                                    <div class="popup-content">
                                        <button type="button" class="close-btn" aria-label="Close" onclick="closeQrPopup()">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24">
                                                <path d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                            </svg>
                                        </button>
                                        <div id="qrCodeZoom"></div>
                                        <button class="download-btn" id="downloadQrCodeBtn" title="Download QR">
                                            <i class="material-icons">file_download</i>
                                            Download
                                        </button>
                                    </div>
                                </div>

                                <div class="qr-code-container" onclick="showQrPopup('{{ $location->title }}')">
                                    {!! $location->qr_code !!}
                                </div>
                            </td>
                            <td>
                                <div class="act-btn">
                                    <a href="{{ route('review_page', ['location' => $location->id]) }}"
                                       class="btn-icon green darken-2" 
                                       title="Review Page">
                                        <i class="material-icons">rate_review</i>
                                    </a>
                                    <a href="{{ route('businesses.reviews', $location->id) }}" 
                                       class="btn green darken-2">
                                        <i class="material-icons">reviews</i>
                                    </a>
                                </div>

                                @if (Auth::id() == $location->user_id)
                                    <form action="{{ route('businesses.delete', $location) }}" 
                                          method="POST" 
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

        <div class="grid-wrapper">
            @foreach($locations as $location)
                <div class="feature-item business-card">
                    <div class="business-header">
                        <h3>{{ $location->title }}</h3>
                        <h5>{{ $location->primary_category }}</h5>
                    </div>
                    <div class="business-body">
                        <div class="business-details">
                            <p>
                                <i class="material-icons">description</i>
                                <strong>Description:</strong>
                                {{ $location->description ?: 'No description available' }}
                            </p>
                            <p>
                                <i class="material-icons">location_on</i>
                                <strong>Address:</strong>
                                {{ $location->formatted_address ?: 'Unknown address' }}
                            </p>
                            <p>
                                <i class="material-icons">phone</i>
                                <strong>Mobile No.:</strong>
                                {{ $location->international_phone_number ?: 'Not available' }}
                            </p>
                            <p>
                                <i class="material-icons">language</i>
                                <strong>Website:</strong>
                                <a href="{{ $location->website }}" target="_blank">
                                    {{ $location->website ?: 'Not available' }}
                                </a>
                            </p>
                        </div>

                        <div class="qr-code-section">
                            <h4>
                                <i class="material-icons">qr_code_2</i>
                                Write a review
                            </h4>
                            <div class="qr-code-container">
                                {!! $location->qr_code !!}
                            </div>
                            <div class="qr-code-actions">
                                <a href="{{ route('review_page', ['location' => $location->id]) }}" 
                                   class="btn-icon green darken-2"
                                   title="Review Page">
                                    <i class="material-icons">rate_review</i>Review Page
                                </a>
                                <button class="green darken-2" 
                                        id="downloadQrCodeBtn" title="Download QR">
                                    <i class="material-icons">file_download</i>
                                    Download QR
                                </button>
                            </div>
                        </div>

                        <div class="act">
                            <a href="{{ route('businesses.reviews', $location->id) }}" 
                               class="btn-flat green-text" title="Reviews">
                                <i class="material-icons">reviews</i>Reviews
                            </a>

                            @if (Auth::id() == $location->user_id)
                                <form action="{{ route('businesses.delete', $location) }}" 
                                      method="POST"
                                      style="display:inline; margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-flat red-text" title="Delete">
                                        <i class="material-icons">delete</i>
                                        Delete Business
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        <div class="pagination-controls">
            @if($locations->hasPages())
                <div class="pagination-wrapper">
                    <button onclick="changePage({{ $locations->currentPage() - 1 }})" 
                            {{ !$locations->previousPageUrl() ? 'disabled' : '' }}
                            class="btn-flat">
                        <i class="material-icons">chevron_left</i>
                    </button>
                    
                    <span class="page-info">
                        Page {{ $locations->currentPage() }} of {{ $locations->lastPage() }}
                    </span>
                    
                    <button onclick="changePage({{ $locations->currentPage() + 1 }})" 
                            {{ !$locations->nextPageUrl() ? 'disabled' : '' }}
                            class="btn-flat">
                        <i class="material-icons">chevron_right</i>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('businessSearch');
        const urlParams = new URLSearchParams(window.location.search);

        if (searchInput) {
            const currentSearch = urlParams.get('search');
            if (currentSearch) {
                searchInput.value = currentSearch;
            }

            searchInput.addEventListener('input', debounce(handleSearch, 350));
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleSearch();
                }
            });
        }
    });

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
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

    function showQrPopup(title) {
        const qrCodeContainer = document.querySelector('.qr-code-container');
        const svgElement = qrCodeContainer.querySelector('svg');
        
        if (!svgElement) {
            alert('QR code not found.');
            return;
        }
        
        const qrCodePopup = document.getElementById('qrCodePopup');
        const qrCodeZoom = document.getElementById('qrCodeZoom');
        const downloadBtn = document.getElementById('downloadQrCodeBtn');

        qrCodeZoom.innerHTML = '';
        const clonedSvg = svgElement.cloneNode(true);
        qrCodeZoom.appendChild(clonedSvg);

        downloadBtn.onclick = () => downloadQrCode(title, clonedSvg);

        qrCodePopup.style.display = 'flex';
    }

    function closeQrPopup() {
        document.getElementById('qrCodePopup').style.display = 'none';
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

    .controls-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        gap: 1rem;
        width: 100%;
        background: lavender;
        padding: 1rem;
        border-radius: 8px;

        @media (max-width: 768px) {
            flex-direction: column;
            align-items: stretch;
        }
    }

    .search-box {
        flex: 1;
        display: flex;
        align-items: center;
        background: white;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);

        i {
            color: #666;
            margin-right: 0.5rem;
        }

        input {
            border: none !important;
            outline: none !important;
            padding: 0.5rem !important;
            width: 100%;
            font-size: 1rem;
            background: transparent !important;
            margin: 0 !important;
            height: auto !important;
        }
    }

    .sync-form {
        margin:0;
        
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
        float: inline-end;
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
        background: hsl(240 100% 108% / 1);
        width: 2rem !important;
        padding: 3px;
        box-shadow: 1px 1px 5px hsl(0deg 0% 0% / 10%), -1px -1px 5px hsl(0deg 0% 0% / 10%);

        svg {
            scale: 1.29;
        }
    }

    .download-btn {
        margin-top: 1rem;
        background: #43a047;
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
                    /* width: max-content;
                    white-space: nowrap; */
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
        a.btn-flat.green-text  {
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

        i{
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