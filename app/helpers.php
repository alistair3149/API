<?php

if (!function_exists('validate_array')) {
    /**
     * Validates an array with given rules
     *
     * @param array                    $data    The Data to validate
     * @param array                    $rules   The Rules to validate against
     * @param \Illuminate\Http\Request $request The Request, needed to throw a ValidationException
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return void
     */
    function validate_array(array $data, array $rules, \Illuminate\Http\Request $request)
    {
         $validator = resolve(\Illuminate\Contracts\Validation\Factory::class)->make($data, $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }
}

if (!function_exists('make_name_readable')) {
    /**
     * @param String $methodName name of view function
     *
     * @return String
     * @throws \App\Exceptions\WrongMethodNameException
     */
    function make_name_readable(String $methodName) : String
    {
        if (ends_with($methodName, 'View') && !starts_with($methodName, 'show')) {
            throw new \App\Exceptions\WrongMethodNameException($methodName.' is not a valid name for a View-Function!');
        } else {
            $readableName = preg_replace(
                '/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]|[0-9]{1,}/',
                ' $0',
                $methodName
            );

            $methodName = ucfirst(strtolower($readableName));
        }

        return $methodName;
    }
}
