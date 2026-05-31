<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class QuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Permitir todas las solicitudes, o implementar lógica de autorización si es necesario
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'age'         => ['required', 'string', 'regex:/^\d+(,\d+)*$/'],
            'currency_id' => ['required', 'in:EUR,GBP,USD'],
            'start_date'  => ['required', 'date_format:Y-m-d'],
            'end_date'    => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('age')) {
                return;
            }

            if (!preg_match('/^\d+(\s*,\s*\d+)*$/', $this->input('age'))) {
                return;
            }

            foreach (explode(',', $this->input('age')) as $age) {
                $ageInt = (int) trim($age);
                if ($ageInt < 18 || $ageInt > 70) {
                    $validator->errors()->add(
                        'age',
                        "Each passenger age must be between 18 and 70. Invalid age: {$ageInt}."
                    );
                }
            };
        });
    }

    public function messages(): array
    {
        return [
            'age.regex'               => 'The age field must be a comma-separated list of numbers.Example: 28,35.',
            'currency_id.in'          => 'The currency_id must be one of:EUR,GBP,USD.',
            'end_date.after_or_equal' => 'The end_date must be equal to or after the start_date.',
        ];
    }

    // Override: retornar JSON en lugar de redirect (es una API)
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
