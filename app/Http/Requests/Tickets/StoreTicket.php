<?php

namespace App\Http\Requests\Tickets;

use App\Http\Requests\CoreRequest;
use App\Models\CustomField;

class StoreTicket extends CoreRequest
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
        $rules['subject'] = 'required';
        $rules['description'] = [
            'required',
            function ($attribute, $value, $fail) {
                $comment = trim_editor($value);;

                if ($comment == '') {
                    $fail(__('validation.required'));
                }
            }
        ];
        $rules['priority'] = 'required';
        $rules['user_id'] = 'required_if:requester_type,employee';
        $rules['client_id'] = 'required_if:requester_type,client';

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

    public function messages()
    {
        return [
            'user_id.required_if' => __('modules.tickets.requesterName') . ' ' . __('app.required'),
            'client_id.required_if' => __('modules.tickets.requesterName') . ' ' . __('app.required'),
        ];
    }

}
