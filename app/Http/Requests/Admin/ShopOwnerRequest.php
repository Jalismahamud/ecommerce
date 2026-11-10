<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopOwnerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $shopOwner = $this->route('shop_owner');

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($shopOwner)],
            'password' => [$shopOwner ? 'nullable' : 'required', 'string', 'min:12', 'confirmed'],
            'shop_ids' => ['sometimes', 'array'],
            'shop_ids.*' => ['integer', 'distinct', Rule::exists('shops', 'id')],
        ];
    }
}
