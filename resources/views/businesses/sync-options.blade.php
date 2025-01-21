@extends('layouts.app')

@section('content')
<div class="container profile-wrapper">
    <div class="card">
        <div class="card-content">
            <h4>Select Businesses to Sync</h4>
            <p>Choose which businesses you want to sync with your account.</p>

            <form action="{{ route('business.sync') }}" method="POST" id="syncForm">
                @csrf
                <div class="table-view">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <label>
                                        <input type="checkbox" class="filled-in" id="selectAll"/>
                                        <span></span>
                                    </label>
                                </th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Address</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locations as $location)
                                <tr>
                                    <td>
                                        <label>
                                            <input type="checkbox" class="filled-in location-checkbox"
                                                   name="selected_locations[]" 
                                                   value="{{ $location['store_code'] }}"/>
                                            <span></span>
                                        </label>
                                    </td>
                                    <td>{{ $location['title'] }}</td>
                                    <td>{{ $location['category'] }}</td>
                                    <td>{{ $location['address'] }}</td>
                                    <td>{{ $location['phone'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="actions-wrapper" style="margin-top: 20px;">
                    <button type="submit" class="btn green darken-2" id="syncSelectedBtn" disabled>
                        <i class="material-icons left">sync</i>
                        Sync Selected Businesses
                    </button>
                    <a href="{{ route('businesses') }}" class="btn-flat">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const locationCheckboxes = document.querySelectorAll('.location-checkbox');
    const syncButton = document.getElementById('syncSelectedBtn');

    function updateSyncButton() {
        const checkedBoxes = document.querySelectorAll('.location-checkbox:checked');
        syncButton.disabled = checkedBoxes.length === 0;
    }

    selectAll.addEventListener('change', function() {
        locationCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSyncButton();
    });

    locationCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            selectAll.checked = [...locationCheckboxes].every(cb => cb.checked);
            updateSyncButton();
        });
    });
});
</script>
@endpush
@endsection