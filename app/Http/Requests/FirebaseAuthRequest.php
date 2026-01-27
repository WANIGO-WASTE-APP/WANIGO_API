<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for Firebase Authentication.
 * 
 * This request validates that:
 * - id_token is present and is a string (Requirement 1.1)
 * 
 * The id_token is a Firebase ID token (JWT) issued by Firebase Authentication
 * that will be verified server-side by the FirebaseAuthService.
 */
class FirebaseAuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Firebase authentication is public - no authorization needed
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_token' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id_token.required' => 'Firebase ID token is required',
            'id_token.string' => 'Firebase ID token must be a string',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'id_token' => 'Firebase ID token',
        ];
    }
}
