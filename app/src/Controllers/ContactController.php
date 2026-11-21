<?php

namespace App\Controllers;


use Lib\Http\{Request, Response};
use Lib\Controllers\AbstractController;

class ContactController extends AbstractController
{
    public function process(Request $request): Response
    {

        try {
            $json = json_decode($request->getBody(), True, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {

            $response = new Response();
            $response->statusCode = 400;
            $response->content = "Invalid JSON format: Check your request for typos";
            return $response;
        }

        $response = new Response();
        $allowedBodyKeys = ["email", "subject", "message"];


        if (array_diff($allowedBodyKeys, array_keys($json)) == [] && array_diff(array_keys($json), $allowedBodyKeys) == []) {
            $response->statusCode = 201;
            $response->content = "created";
        } else {

            $missing = array_diff($allowedBodyKeys, array_keys($json));
            $invalid = array_diff(array_keys($json), $allowedBodyKeys);
            $response->statusCode = 400;

            if ($missing != []) {
                $response->content = "invalid JSON schema: <strong><code>" . implode($missing) . "</code></strong> is missing.";
            }
            if ($invalid != []) {
                $response->content = "invalid JSON schema: <strong><code>" . implode($invalid) . "</code></strong> is invalid.";
            }
            if ($invalid != [] && $missing != []) {
                $response->content = "invalid JSON schema: <strong><code>" . implode($invalid) . "</code></strong> is invalid and <strong><code>" . implode($missing) . "</code></strong> is missing.";
            }
        }
        ;

        return $response;
    }
}
