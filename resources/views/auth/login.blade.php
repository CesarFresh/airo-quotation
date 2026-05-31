@extends('layouts.app')

@section('title', 'Login | AIRO Quotation')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card app-card">
            <div class="card-body p-4 p-md-5">
                <div class="mb-4 text-center">
                    <span class="badge text-bg-primary brand-badge mb-3">
                        Protected Access
                    </span>

                    <h1 class="h3 fw-bold mb-2">
                        Login
                    </h1>

                    <p class="text-muted mb-0">
                        Sign in to get a JWT token before creating a quotation.
                    </p>
                </div>

                <div id="errorBox" class="alert alert-danger d-none" role="alert"></div>
                <div id="successBox" class="alert alert-success d-none" role="alert"></div>

                <form id="loginForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="demo@airo.com"
                            required
                            autocomplete="email"
                        >
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">
                            Password
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            value="password"
                            required
                            autocomplete="current-password"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="loginButton">
                        Login
                    </button>
                </form>

                <div class="mt-4 small text-muted">
                    <strong>Demo credentials:</strong><br>
                    Email: <code>demo@airo.com</code><br>
                    Password: <code>password</code>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const loginPage = document.getElementById('loginPage');
    const loginForm = document.getElementById('loginForm');
    const errorBox = document.getElementById('errorBox');
    const successBox = document.getElementById('successBox');
    const loginButton = document.getElementById('loginButton');

    async function redirectIfAlreadyAuthenticated() {
        const existingToken = sessionStorage.getItem('jwt_token');

        if (!existingToken) {
            loginPage.classList.remove('d-none');
            return;
        }

        try {
            const response = await fetch('/api/auth/me', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${existingToken}`,
                },
            });

            if (response.ok) {
                window.location.replace('/quotation');
                return;
            }

            sessionStorage.removeItem('jwt_token');
            loginPage.classList.remove('d-none');
        } catch (error) {
            sessionStorage.removeItem('jwt_token');
            loginPage.classList.remove('d-none');
        }
    }

    redirectIfAlreadyAuthenticated();

    window.addEventListener('pageshow', function () {
        redirectIfAlreadyAuthenticated();
    });

    function showError(message) {
        errorBox.textContent = message;
        errorBox.classList.remove('d-none');
        successBox.classList.add('d-none');
    }

    function showSuccess(message) {
        successBox.textContent = message;
        successBox.classList.remove('d-none');
        errorBox.classList.add('d-none');
    }

    loginForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        errorBox.classList.add('d-none');
        successBox.classList.add('d-none');

        loginButton.disabled = true;
        loginButton.textContent = 'Logging in...';

        const payload = {
            email: loginForm.email.value,
            password: loginForm.password.value,
        };

        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                showError(data.message || 'Invalid credentials.');
                return;
            }

            if (!data.access_token) {
                showError('Login succeeded, but no access token was returned.');
                return;
            }

            sessionStorage.setItem('jwt_token', data.access_token);

            window.location.replace('/quotation');
        } catch (error) {
            showError('Network error: ' + error.message);
        } finally {
            loginButton.disabled = false;
            loginButton.textContent = 'Login';
        }
    });
</script>
@endpush