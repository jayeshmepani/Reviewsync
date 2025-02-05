@extends('layouts.app')

@section('content')
<div class="container profile-wrapper">
    <div class="card">
        <div class="card-content">
            <h4>Select Business to Sync</h4>
            
            @if($isTrialUser)
                <div class="alert alert-info mb-4">
                    <p>With trial subscription, you can sync up to {{ $trialLimit }} business(es).</p>
                    @if(isset($remainingSlots))
                        <p>You can select {{ $remainingSlots }} more business(es).</p>
                    @endif
                </div>
            @endif

            <p>Choose which business(es) you want to sync with your account.</p>

            @if(count($locations) > 0)
                <form action="{{ route('business.sync') }}" method="POST" id="syncForm">
                    @csrf
                    <div class="table-view">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Select</th>
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
                                                <input type="{{ $isTrialUser ? ($trialLimit === 1 ? 'radio' : 'checkbox') : 'checkbox' }}" 
                                                       name="selected_locations[]" 
                                                       value="{{ $location['store_code'] }}"
                                                       class="location-select"
                                                       data-max-select="{{ $isTrialUser ? $remainingSlots : '-1' }}"
                                                       {{ $isTrialUser ? '' : 'class="filled-in"' }}/>
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
                            Sync Selected Business(es)
                        </button>
                        <a href="{{ route('businesses') }}" class="btn-flat">Cancel</a>
                    </div>
                </form>
            @else
                <div class="alert alert-warning">
                    <p>No businesses available to sync.</p>
                </div>
                <div class="actions-wrapper" style="margin-top: 20px;">
                    <a href="{{ route('businesses') }}" class="btn-flat">Back</a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectInputs = document.querySelectorAll('.location-select');
    const syncButton = document.getElementById('syncSelectedBtn');
    const maxSelect = parseInt(selectInputs[0]?.dataset.maxSelect || '-1');

    function updateSyncButton() {
        const checkedCount = document.querySelectorAll('.location-select:checked').length;
        const isValid = checkedCount > 0 && (maxSelect === -1 || checkedCount <= maxSelect);
        syncButton.disabled = !isValid;
    }

    selectInputs.forEach(input => {
        input.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('.location-select:checked').length;
            
            // If max selection reached, disable unchecked checkboxes
            if (maxSelect !== -1) {
                if (checkedCount >= maxSelect) {
                    selectInputs.forEach(inp => {
                        if (!inp.checked) inp.disabled = true;
                    });
                } else {
                    selectInputs.forEach(inp => inp.disabled = false);
                }
            }
            
            updateSyncButton();
        });
    });
});
</script>
@endpush
@endsection