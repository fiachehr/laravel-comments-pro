<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Fiachehr\Comments\Models\Comment;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
            'body' => 'required|string|min:1|max:5000',
            'parent_id' => 'nullable|integer|exists:comments,id',
            'recaptcha_token' => 'nullable|string',
            'guest_name' => 'nullable|string|max:120',
            'guest_email' => 'nullable|email|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Guests handling
            if (!Auth::check()) {
                if (!config('comments.guests.allowed')) {
                    $validator->errors()->add('guest', 'Guests are not allowed to comment.');
                    return;
                }
                if (config('comments.guests.require_email') && !$this->filled('guest_email')) {
                    $validator->errors()->add('guest_email', 'Email is required for guest comments.');
                }
            }

            // Depth validation
            if ($this->parent_id) {
                $parent = Comment::find($this->parent_id);
                if ($parent && $parent->depth + 1 > config('comments.max_depth', 5)) {
                    $validator->errors()->add('depth', 'Max depth exceeded.');
                }
            }

            // reCAPTCHA validation
            if (config('comments.recaptcha.enabled')) {
                $token = $this->input('recaptcha_token');
                $ok = app('comments.recaptcha')->verify($token, $this->ip());
                if (!$ok) {
                    $validator->errors()->add('recaptcha', 'Failed reCAPTCHA verification.');
                }
            }
        });
    }
}
