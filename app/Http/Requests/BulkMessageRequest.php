<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'messages' => ['required', 'array', 'min:1', 'max:100'],
            'messages.*.title' => ['required', 'string', 'max:255'],
            'messages.*.content' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'messages.required' => 'Messages array is required',
            'messages.array' => 'Messages must be an array',
            'messages.min' => 'At least one message is required',
            'messages.max' => 'Maximum 100 messages can be processed at once',
            'messages.*.title.required' => 'Title is required for all messages',
            'messages.*.content.required' => 'Content is required for all messages',
        ];
    }
}
