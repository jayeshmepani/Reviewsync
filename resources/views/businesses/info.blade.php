@extends('layouts.app')

@section('content')
<div class="container profile-wrapper">
    <div class="businesses-section">
        <h2><i class="material-icons">business</i> My Businesses</h2>

        <div class="table-view">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th class="description">Description</th>
                        <th>Created</th>
                        <th>Address</th>
                        <th>Mobile No.</th>
                        <th>Website</th>
                        <th>QR Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locations as $location)
                        <tr>
                            <td>{{ $location->title }}</td>
                            <td title="{{ $location->description ?: 'No description available' }}">{{ $location->description ?: 'No description available' }}</td>
                            <td>{{ $location->created_at->format('F d, Y') }}</td>
                            <td>{{ $location->formatted_address ?: 'Unknown address' }}</td>
                            <td>{{ $location->international_phone_number ?: 'Not available' }}</td>
                            <td>
                                <a href="{{ $location->website }}" target="_blank">
                                    {{ $location->website ?: 'Not available' }}
                                </a>
                            </td>
                            <td>
                                <div class="qr-code-container">
                                    {!! $location->qr_code !!}
                                </div>
                            </td>
                            <td>
                                <a href="{{ $location->review_link }}" target="_blank" class="btn-icon green darken-2"
                                    title="Review Link">
                                    <i class="material-icons">rate_review</i>
                                </a>
                                <button class="btn-icon green darken-2" onclick="downloadQrCode('{{ $location->title }}')"
                                    title="Download QR">
                                    <i class="material-icons">file_download</i>
                                </button>
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
                        <h3>{{ $location->title}}</h3>
                    </div>
                    <div class="business-body">
                        <div class="business-details">
                            <p><i class="material-icons">description</i> <strong>Description:</strong>
                                {{ $location->description ?: 'No description available' }}</p>
                            <p><i class="material-icons">calendar_month</i> <strong>Created:</strong>
                            {{ optional($location->created_at)->format('F d, Y') ?: 'No date available' }}</p>
                            <p><i class="material-icons">location_on</i> <strong>Address:</strong>
                                {{ $location->formatted_address ?: 'Unknown address' }}</p>
                            <p><i class="material-icons">phone</i> <strong>Mobile No.:</strong>
                                {{ $location->international_phone_number ?: 'Not available' }}</p>
                            <p><i class="material-icons">language</i> <strong>Website:</strong>
                                <a href="{{ $location->website }}" target="_blank">
                                    {{ $location->website ?: 'Not available' }}
                                </a>
                            </p>
                        </div>

                        <div class="qr-code-section">
                            <h4><i class="material-icons">qr_code_2</i> Write a review</h4>
                            <div class="qr-code-container">
                                {!! $location->qr_code !!}
                            </div>
                            <div class="qr-code-actions">
                                <a href="{{ $location->review_link }}" class="green darken-2" target="_blank">Review
                                    Link</a>
                                <button class="green darken-2" onclick="downloadQrCode('{{ $location->title }}')">Download
                                    QR</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
<script>
    function downloadQrCode(title) {
        const qrCodeContainer = document.querySelector('.qr-code-container');
        const svgElement = qrCodeContainer.querySelector('svg');
        if (svgElement) {
            const serializer = new XMLSerializer();
            const svgString = serializer.serializeToString(svgElement);

            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();

            const svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(svgBlob);

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
        } else {
            alert('QR code not found.');
        }
    }
</script>
<style>
    .container.profile-wrapper {
        width: 90%;
    }

    .businesses-section {
        background: ghostwhite;
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease-in-out;

        .theme-dark & {
            background-color: #2d3748;
        }
    }

    .businesses-section h2 {
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem !important;
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
            border: 1px solid;
            padding: 8px;
            text-align: left;
        }

        tr {
            border: 0 !important;

            &:nth-child(1) {
                &~tr {
                    border-bottom: 1px solid hsl(0deg 0% 0%) !important;

                    td {
                        &:nth-child(2) {
                            border-inline: 0 !important;
                            border-block: 1px solid !important;
                            border-bottom: 0 !important;
                        }
                    }
                }
            }

            td {
                &:nth-child(2) {
                    display: -webkit-box;
                    -webkit-line-clamp: 8;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    border: 0 !important;
                    border-radius: 0 !important;
                    height: calc(8 * 1rem * 1.4);
                }

                &:last-child>* {
                    margin: 1rem !important;
                }

                &:nth-child(5) {
                    width: 100%;
                    text-align: center;
                }
            }
        }

        th {
            background-color: hsl(from var(--bg-dark) h 51% 15%);
            font-weight: bold;
            border-radius: 0 !important;

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

        .theme-dark & {
            border-left: solid 2px hsl(222deg 53% 57% / 69%);
        }
    }

    .qr-code-container {
        padding: 0.7rem;
        border-radius: 7px;

        svg {
            border: 2.3px solid hsl(240deg 100% 1%);
            padding: 0.1rem;

            .theme-dark & {
                border: 2.3px solid ghostwhite;
                padding: 0.15rem;
            }
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

            .theme-dark & {
                border-bottom: 1px solid #f8f8ff9e;
            }
        }

        .qr-code-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 0.7rem 0.5rem;
            border: 1px solid hsl(240deg 100% 1% / 62%);

            .theme-dark & {
                border: 1px solid #f8f8ff9e;
            }
        }

        .qr-code-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
    }

    @media screen and (min-width: 1440px) {
        .grid-wrapper {
            display: none;
        }

        .table-view {
            display: block;
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