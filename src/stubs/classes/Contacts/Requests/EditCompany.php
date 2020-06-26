<?php

namespace App\Classes\Contacts\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditCompany extends FormRequest
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
            'rag_soc' => "required|unique:companies,rag_soc,{$this->company->id}",
            'email' => "required|email|unique:companies,email,{$this->company->id}",
            'piva' => "required"
        ];
    }

    public function attributes()
    {
        return [
            'rag_soc' => 'Ragione Sociale',
            'piva' => 'P.IVA (Partita Iva)'
        ];
    }

}
