<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'archivo' => 'required|file|mimes:xlsx,xls',
            'remision' => 'required|string|max:20',
            'sede' => 'required|string|max:100',
            'fecha' => 'required|date',
            'manual_items' => 'nullable|array',
            'manual_items.*.codigo_item' => 'required|string|exists:items,codigo_item',
            'manual_items.*.cantidad' => 'required|integer|min:1',
        ];
    }
}
