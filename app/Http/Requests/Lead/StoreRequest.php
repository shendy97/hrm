<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\CoreRequest;
use App\Models\CustomField;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends CoreRequest
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
        $rules = array();

        $rules['client_name'] = 'required';
        $rules['client_email'] = 'nullable|email:rfc|unique:leads,client_email,null,id,company_id,' . company()->id.'|unique:users,email,null,id,company_id,' . company()->id;
        $rules['website'] = 'nullable|url';

        $checkCustomField = request()->custom_fields_data;

        if ($checkCustomField)
        {
            foreach ($checkCustomField as $key => $customFieldsData) {
                $fieldName = explode ('_', $key);
                $name = $fieldName[0];
                $id = $fieldName[1];

                $customField = CustomField::findOrFail($id);

                if ($customField->required == 'yes' && $customFieldsData == null)
                {
                    $rules[$name] = 'required';
                }
            }
        }

        return $rules;

    }

}
