<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\CoreRequest;
use App\Models\CustomField;
use App\Models\Company;

class StorePublicLead extends CoreRequest
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
        $company = Company::findOrFail($this->request->get('company_id'));
        $rules = array();
        $rules['name'] = 'required';
        $rules['email'] = 'nullable|email:rfc|unique:leads,client_email,null,id,company_id,' . $company->id.'|unique:users,email,null,id,company_id,' . $company->id;

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


        if(global_setting()->google_recaptcha_status == 'active' && global_setting()->ticket_form_google_captcha == 1 && (global_setting()->google_recaptcha_v2_status == 'active')){
            $rules['g-recaptcha-response'] = 'required';
        }

        return $rules;
    }

}
