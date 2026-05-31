@extends('layouts.app')

@section('title', 'Create Quotation | AIRO Quotation')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="badge text-bg-success brand-badge mb-2">
                    JWT Protected
                </span>

                <h1 class="h3 fw-bold mb-0">
                    Travel Insurance Quotation
                </h1>
            </div>

            <button type="button" class="btn btn-outline-danger" id="logoutButton">
                Logout
            </button>
        </div>

        <div id="authWarning" class="alert alert-warning d-none" role="alert">
            You are not authenticated. Redirecting to login...
        </div>

        <div id="errorBox" class="alert alert-danger d-none" role="alert"></div>
        <div id="successBox" class="alert alert-success d-none" role="alert"></div>

        <div class="card app-card mb-4">
            <div class="card-body p-4">
                <form id="quotationForm">
                    <div class="mb-3">
                        <label for="age" class="form-label">
                            Passenger ages
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="age"
                            name="age"
                            placeholder="Example: 28,35"
                            required
                        >

                        <div class="form-text">
                            Enter one or more ages separated by commas. Supported range: 18 to 70.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="currency_id" class="form-label">
                            Currency
                        </label>

                        <select class="form-select" id="currency_id" name="currency_id" required>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="start_date" class="form-label">
                                Start date
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                id="start_date"
                                name="start_date"
                                required
                            >
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="end_date" class="form-label">
                                End date
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                id="end_date"
                                name="end_date"
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="quotationButton">
                        Get Quotation
                    </button>
                </form>
            </div>
        </div>

        <div id="resultCard" class="card app-card d-none">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">
                    Quotation Result
                </h2>

                <div class="row">
                    <div class="col-12 col-md-4 mb-3 mb-md-0">
                        <div class="text-muted small">Quotation ID</div>
                        <div class="fs-5 fw-semibold" id="resultQuotationId">-</div>
                    </div>

                    <div class="col-12 col-md-4 mb-3 mb-md-0">
                        <div class="text-muted small">Total</div>
                        <div class="fs-5 fw-semibold" id="resultTotal">-</div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="text-muted small">Currency</div>
                        <div class="fs-5 fw-semibold" id="resultCurrency">-</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const token = sessionStorage.getItem('jwt_token');

    const quotationForm = document.getElementById('quotationForm');
    const quotationButton = document.getElementById('quotationButton');
    const logoutButton = document.getElementById('logoutButton');

    const authWarning = document.getElementById('authWarning');
    const errorBox = document.getElementById('errorBox');
    const successBox = document.getElementById('successBox');

    const resultCard = document.getElementById('resultCard');
    const resultQuotationId = document.getElementById('resultQuotationId');
    const resultTotal = document.getElementById('resultTotal');
    const resultCurrency = document.getElementById('resultCurrency');

    if (!token) {
        authWarning.classList.remove('d-none');

        setTimeout(() => {
            window.location.href = '/login';
        }, 1000);
    }

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

    function clearMessages() {
        errorBox.classList.add('d-none');
        successBox.classList.add('d-none');
    }

    function formatValidationErrors(errors) {
        if (!errors) {
            return '';
        }

        return Object.values(errors)
            .flat()
            .join(' ');
    }

    quotationForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        clearMessages();
        resultCard.classList.add('d-none');

        quotationButton.disabled = true;
        quotationButton.textContent = 'Calculating...';

        const payload = {
            age: quotationForm.age.value,
            currency_id: quotationForm.currency_id.value,
            start_date: quotationForm.start_date.value,
            end_date: quotationForm.end_date.value,
        };

        try {
            const response = await fetch('/api/quotation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${sessionStorage.getItem('jwt_token')}`,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (response.status === 401) {
                sessionStorage.removeItem('jwt_token');
                showError('Your session expired or the token is invalid. Redirecting to login...');

                setTimeout(() => {
                    window.location.href = '/login';
                }, 1200);

                return;
            }

            if (!response.ok) {
                const validationErrors = formatValidationErrors(data.errors);
                showError((data.message || 'Quotation request failed.') + ' ' + validationErrors);
                return;
            }

            resultQuotationId.textContent = data.quotation_id;
            resultTotal.textContent = data.total;
            resultCurrency.textContent = data.currency_id;

            resultCard.classList.remove('d-none');
            showSuccess('Quotation created successfully.');
        } catch (error) {
            showError('Network error: ' + error.message);
        } finally {
            quotationButton.disabled = false;
            quotationButton.textContent = 'Get Quotation';
        }
    });

    logoutButton.addEventListener('click', async function () {
        const currentToken = sessionStorage.getItem('jwt_token');

        logoutButton.disabled = true;
        logoutButton.textContent = 'Logging out...';

        try {
            if (currentToken) {
                await fetch('/api/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${currentToken}`,
                    },
                });
            }
        } catch (error) {
            console.warn('Logout request failed:', error);
        } finally {
            sessionStorage.removeItem('jwt_token');
            window.location.href = '/login';
        }
    });
</script>
@endpush