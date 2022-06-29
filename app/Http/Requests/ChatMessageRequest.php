<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChatMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->hasUser();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'message' => [
                'required'
            ],
            'user_id' => [
                'sometimes',
                Rule::exists('users', 'id'),
            ],
            'chat_title' => [
                'sometimes',
                'string'
            ],
            'chat_id' => [
                'sometimes',
                'exists:App\Models\Chat,id'
            ]
        ];
    }
}
