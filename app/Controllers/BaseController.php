<?php

namespace App\Controllers;

use App\Support\LegacyRequest;
use App\Support\LegacyResponse;
use App\Support\LegacyValidationErrors;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

abstract class BaseController extends Controller
{
    protected LegacyRequest $request;
    protected LegacyResponse $response;
    protected LegacyValidationErrors $validator;

    public function __construct()
    {
        $this->request = new LegacyRequest(request());
        $this->response = new LegacyResponse();
        $this->validator = new LegacyValidationErrors();
    }

    protected function validate(array $rules): bool
    {
        $laravelRules = [];
        foreach ($rules as $field => $ruleSet) {
            $converted = [];
            foreach (explode('|', $ruleSet) as $rule) {
                if ($rule === 'permit_empty') { $converted[] = 'nullable'; continue; }
                if ($rule === 'valid_date') { $converted[] = 'date'; continue; }
                if ($rule === 'valid_email') { $converted[] = 'email'; continue; }
                if ($rule === 'is_natural') { array_push($converted, 'integer', 'min:0'); continue; }
                if ($rule === 'is_natural_no_zero') { array_push($converted, 'integer', 'min:1'); continue; }
                if (preg_match('/^min_length\[(\d+)]$/', $rule, $match)) { $converted[] = 'min:' . $match[1]; continue; }
                if (preg_match('/^max_length\[(\d+)]$/', $rule, $match)) { $converted[] = 'max:' . $match[1]; continue; }
                if (preg_match('/^in_list\[(.*)]$/', $rule, $match)) { $converted[] = 'in:' . $match[1]; continue; }
                $converted[] = $rule;
            }
            $laravelRules[$field] = $converted;
        }

        $validation = Validator::make($this->request->all(), $laravelRules);
        $this->validator->set($validation->errors()->toArray());

        return ! $validation->fails();
    }
}
