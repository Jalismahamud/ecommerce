<?php

namespace App\Http\Requests\ShopOwner;

use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $shopProduct = $this->route('shopProduct');
        $shopId = $shopProduct?->shop_id ?? $this->input('shop_id');
        $shop = $shopId ? Shop::find($shopId) : null;

        return $shopProduct
            ? $this->user()?->can('update', $shopProduct) ?? false
            : ($shop && ($this->user()?->can('create', [ShopProduct::class, $shop]) ?? false));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->route('shopProduct')) {
            return [
                'shop_id' => ['sometimes', 'integer', Rule::exists('shops', 'id')],
                'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
                'compare_at_price' => ['nullable', 'numeric', 'gte:price', 'max:99999999.99'],
                'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
                'description' => ['nullable', 'string', 'max:10000'],
                'is_active' => ['sometimes', 'boolean'],
                'is_featured' => ['sometimes', 'boolean'],
                'image' => ['nullable', 'image', 'max:5120'],
            ];
        }

        return [
            'shop_id' => ['required', 'integer', Rule::exists('shops', 'id')],
            'product_id' => [
                'required_without:name',
                'integer',
                Rule::exists('products', 'id'),
                Rule::unique('shop_products', 'product_id')->where(fn ($query) => $query->where('shop_id', $this->input('shop_id'))),
            ],
            'name' => ['required_without:product_id', 'string', 'max:180'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')],
            'brand' => ['nullable', 'string', 'max:150'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'product_description' => ['nullable', 'string', 'max:10000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'compare_at_price' => ['nullable', 'numeric', 'gte:price', 'max:99999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'description' => ['nullable', 'string', 'max:10000'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
