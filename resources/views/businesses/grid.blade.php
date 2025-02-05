<div class="grid-wrapper" id="usersTable">
    <div class="search-box">
        <i class="material-icons">search</i>
        <input type="text" id="businessSearch" placeholder="Search by business name..." onkeyup="searchBusiness()">
    </div>

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
                            class="btn-icon green darken-2" title="Review Page">
                            <i class="material-icons">rate_review</i>Review Page
                        </a>
                        <button class="green darken-2" id="downloadQrCodeBtn" title="Download QR">
                            <i class="material-icons">file_download</i>
                            Download QR
                        </button>
                    </div>
                </div>

                <div class="act">
                    <a href="{{ route('businesses.reviews', $location->id) }}" class="btn-flat green-text"
                        title="Reviews">
                        <i class="material-icons">reviews</i>Reviews
                    </a>

                    @if (Auth::id() == $location->user_id)
                        <form action="{{ route('businesses.delete', $location) }}" method="POST"
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
