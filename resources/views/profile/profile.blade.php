@extends('layouts.app')

@section('content')
<div class="container profile-wrapper">
    <!-- Profile Header -->
    <div class="section-wrapper profile-header">
        <div class="profile-avatar">
            @if(isset($user->profile_picture))
                <img src="{{  asset($user->profile_picture) }}" alt="Profile Picture" class="profile-pic" width="50"
                    height="50">
            @endif
        </div>
        <div class="profile-info">
            <h1>
                {{ $user->name }}'s Profile
            </h1>
            <div class="user-details">
                <div class="detail-item">
                    <span class="detail-label"><i class="material-icons">email</i> Email</span>
                    <span class="detail-value">{{ $user->email }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="material-icons">calendar_today</i> Account Created</span>
                    <span class="detail-value">{{ $user->created_at->format('F d, Y') }}</span>
                </div>
                @if($user->google_id)
                    <div class="detail-item">
                        <span class="detail-label"><i class="material-icons">link</i> Connected Account</span>
                        <span class="detail-value">Google</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Settings Section -->
    <div class="section-wrapper settings">
        <h2><span class="material-icons">settings</span> Settings</h2>

        <!-- Rename User -->
        <form method="POST" action="{{ route('profile.rename') }}" class="settings-form">
            @csrf
            <div class="input-field col s12">
                <input type="text" name="name" id="name" value="{{ $user->name }}" required placeholder="Rename">
            </div>
            <div class="center-align">
                <button type="submit" class="btn blue">
                    <span class="material-icons">edit</span> Update Name
                </button>
            </div>
        </form>

        <!-- Change Password -->
        <form method="POST" action="{{ route('profile.update_password') }}" class="settings-form">
            @csrf
            <div class="input-field col s12 password-field">
                <input class="password" type="password" name="current_password" id="current_password" required
                    placeholder="Current Password">
                <button type="button" class="password-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
            <div class="input-field col s12 password-field">
                <input class="password" type="password" name="new_password" id="new_password" required
                    placeholder="New Password">
                <button type="button" class="password-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
            <div class="input-field col s12 password-field">
                <input class="password" type="password" name="new_password_confirmation" id="new_password_confirmation"
                    required placeholder="Confirm New Password">
                <button type="button" class="password-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
            <div class="center-align">
                <button type="submit" class="btn blue">
                    <span class="material-icons">lock</span> Change Password
                </button>
            </div>
        </form>


        <!-- Update Email -->
        <form method="POST" action="{{ route('profile.update_email') }}" class="settings-form">
            @csrf
            <div class="input-field col s12">
                <input type="email" name="email" id="email" value="{{ $user->email }}" required
                    placeholder="Update Email">
            </div>
            <div class="center-align">
                <button type="submit" class="btn blue">
                    <span class="material-icons">email</span> Update Email
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    // Add event listeners to all password toggle buttons
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function () {
            const passwordField = this.closest('.password-field');
            const passwordInput = passwordField.querySelector('.password');
            const toggleButton = this.querySelector('svg');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                `;
            } else {
                passwordInput.type = 'password';
                toggleButton.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                `;
            }
        });
    });
</script>
@endsection

@push('styles')
    <style>
        .password-field {
            position: relative;
        }

        .input-field.col.s12.password-field {
            display: flex;
            flex-direction: row-reverse;
            align-items: center;
        }

        .password-toggle {
            position: absolute;
            padding: 0.9rem;
            transform: translateY(-0.5ch);
            background: none;
            border: none;
            cursor: pointer;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .password-toggle:hover {
            color: #6b46c1;
        }

        button:focus {
            outline: none;
            background: none;
        }

        .center-align button {
            border-radius: 9px !important;
        }

        .profile-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            gap: 2rem;
            background: ghostwhite;
            padding: 1rem;
        }

        .profile-header {
            .profile-avatar {
                position: unset !important;

                >* {
                    width: 73px !important;
                    object-fit: cover;
                    border-radius: .5rem !important;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
            }
        }

        .profile-info h1 {
            margin-bottom: 1rem;
        }

        .user-details {
            display: flex;
            flex-wrap: wrap;
            gap: 2.3rem;
            padding-top: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.8rem;
            color: rgba(0, 0, 0, 0.6);
            margin-bottom: 0.25rem;
        }

        .material-icons {
            vertical-align: middle;
            margin-right: 0.5rem;
            bottom: 0.1ch;
            position: relative;

            p & {
                margin-right: 0.1rem;
            }
        }

        .profile-info h1 .material-icons {
            font-size: 2rem;
        }

        .detail-item .material-icons {
            font-size: 1rem;
            margin-right: 0.3rem;
        }

        h2 {
            margin-bottom: 0.5rem !important;
        }

        form.settings-form {
            margin: 0;
        }

        .settings-form {
            display: grid;
            justify-items: center;
            padding: 1rem;
            border: 1.5px solid;
            border-radius: 0 !important;
            background: ghostwhite;

            &:first-of-type {
                border: 1.5px solid !important;
                border-bottom: 0 !important;
                border-radius: 1rem 1rem 0 0 !important;
            }

            &:nth-of-type(3) {
                border-bottom: 0 !important;
            }

            &:last-of-type {
                border: 1.5px solid !important;
                border-top: 0 !important;
                border-radius: 0 0 1rem 1rem !important;
            }

        }

        .input-field {
            width: -webkit-fill-available !important;
            width: -moz-available !important;
            position: relative;
            right: 0.5rem !important;
            margin-bottom: 1rem !important;
            padding: 0;
        }

        input {
            padding: 0 1rem !important;
            width: -webkit-fill-available !important;
            width: -moz-available !important;
            margin: 0 0 8px 16px !important;
        }

        .theme-light {

            input,
            textarea {
                border-radius: 7.5px !important;
            }

            &:is(.theme-light) {

                input,
                textarea {
                    border: 1px solid hsl(210 7% 73%) !important;
                    background-color: hsl(210 11% 91%) !important;
                }

                .input-field.col.s12:has(input)::before {
                    background: hsl(210 7% 73%);
                    color: black;
                }
            }

            .input-field.col.s12:has(input)::before {
                content: '';
                display: none;
                width: max-content;
                height: max-content;
                position: absolute;
                inset: -1.5ch 24px;
                border-radius: 7px;
                padding: 0 0.5rem;
                background: hsl(222 17% 35%);
                color: white;
                opacity: 0;
                transform: translateY(5px);
                transition: opacity 0.37s ease-in-out, transform 0.37s ease-in-out !important;
            }

            .input-field.col.s12 {
                &:has(#name)::before {
                    content: 'Full Name';
                }

                &:has(#email)::before {
                    content: 'Email';
                }

                &:has(#current_password)::before {
                    content: 'Current Password';
                }

                &:has(#new_password)::before {
                    content: 'New Password';
                }

                &:has(#new_password_confirmation)::before {
                    content: 'Confirm Password';
                }

                &:has(#phone)::before {
                    content: 'Phone No.';
                }

                &:has(input:not(:placeholder-shown))::before,
                &:has(input:focus-within)::before {
                    display: block;
                    opacity: 1;
                    transform: translateY(0);
                }

                &:has(input:placeholder-shown)::before {
                    opacity: 0;
                }
            }
        }
    </style>
@endpush
