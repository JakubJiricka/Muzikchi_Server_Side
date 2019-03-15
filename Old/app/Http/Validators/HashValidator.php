<?php namespace App\Http\Validators;

use Hash;
use Illuminate\Validation\Validator;

class HashValidator {

    public function validate($attribute, $value, $parameters) {
        return Hash::check($value, $parameters[0]);
    }
}