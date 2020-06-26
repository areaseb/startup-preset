<?php

namespace App\Classes\Contacts\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompany extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'rag_soc' => "required|unique:companies,rag_soc",
            'email' => "required|email|unique:companies,email",
            'piva' => "required"
        ];
    }
}
