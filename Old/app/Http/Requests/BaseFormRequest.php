<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class BaseFormRequest extends FormRequest
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
     * {@inheritdoc}
     */
    protected function formatErrors(Validator $validator)
    {
        return $this->formatValidationErrors($validator);
    }

    /**
     * Format the validation errors to be returned.
     *
     * @param  Validator  $validator
     * @return array
     */
    public static function formatValidationErrors(Validator $validator)
    {
        try {
            $formatted = self::getMessages($validator);
        } catch (\Exception $e) {
            $formatted = $validator->errors()->getMessages();
        }

        return ['status' => 'error', 'messages' => $formatted];
    }

    private static function getMessages(Validator $validator)
    {
        $messages = $validator->errors()->getMessages();
        $formatted = [];

        foreach ($validator->failed() as $field => $rules) {
            $formatted[$field] = [];

            $index = 0;
            foreach ($rules as $rule => $params) {

                //get validation rule translation key
                $message = $messages[$field][$index];

                //translate validation message, without replacing placeholders
                $translated = trans($message, [], 'en');

                //replace placeholders in rule translation
                $replaced = $validator->makeReplacements($translated, $field, $rule, $params);

                //explode translated and replace strings by space
                $first = explode(' ', rtrim($translated, '.'));
                $second = explode(' ', rtrim($replaced, '.'));

                //diff the two string and make an array of translation
                //placeholders and values for example, [':min' => 5]
                $params = array_combine(
                    array_diff($first, $second),
                    array_diff($second, $first)
                );

                //remove : from all param names
                $formattedParams = [];
                foreach ($params as $name => $value) {
                    $formattedParams[str_replace(':', '', $name)] = $value;
                }

                //format validation messages
                $formatted[$field][] = ['message' => $message, 'params' => $formattedParams];

                $index++;
            }
        }

        return $formatted;
    }
}
