<?php

namespace BenBjurstrom\Otpz\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $characters = config('otpz.characters') ?? 10;
        return [
            'code' => ['required', 'string', 'size:'.$characters],
            'sessionId' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $code = preg_replace('/[^0-9A-Z]/', '', strtoupper($this->code));
        $this->merge([
            'code' => $code,
        ]);
    }
}
