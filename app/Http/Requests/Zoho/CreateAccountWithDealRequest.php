<?php

namespace App\Http\Requests\Zoho;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAccountWithDealRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'Account_Name' => 'required|string|min:3|max:255',
      'Website' => 'url:http,https',
      'Phone' => 'required|string|regex:/^\+380(\d){9}$/i|min:13|max:13',
      'Deal_Name' => 'required|string|min:3|max:255',
      'Closing_Date' => [
        'required',
        Rule::date()->format('Y-m-d'),
      ],
      'Stage' => 'required|string|min:3|max:255',
    ];
  }
}
