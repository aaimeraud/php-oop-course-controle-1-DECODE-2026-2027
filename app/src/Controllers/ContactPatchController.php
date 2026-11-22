<?php

namespace App\Controllers;

use Lib\Http\{Request, Response};
use Lib\Controllers\AbstractController;

class ContactPatchController extends AbstractController
{
    public function process(Request $request): Response
    {
        header('Content-Type: application/json; charset=utf-8');

        $filename = rawurldecode($request->getParams()['filename']);

        if (str_contains($filename, "..") || str_contains($filename, "/") || str_contains($filename, "\\")) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "Invalid filename"]));

            return $response;
        }

        $path = __DIR__ . "/../../var/contacts/$filename.json";

        if (file_exists($path) === false) {
            $response = new Response();
            $response->sendResponse(404, json_encode(["error" => "File not found"]));

            return $response;
        }

        if (isset($request->getHeaders()["Content-Type"]) === false) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "No headers provided"]));

            return $response;
        }

        $contentType = explode(";", $request->getHeaders()["Content-Type"]);

        if (trim($contentType[0]) !== "application/json") {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "Invalid Content-Type: only JSON content is allowed"]));

            return $response;
        }

        try {
            $json = json_decode($request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "Invalid JSON format: Check your request for typos"]));

            return $response;
        }

        if (is_array($json) === false) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "Invalid Content: Not an array"]));

            return $response;
        }

        $allowedBodyKeys = ["email", "subject", "message"];
        $invalid = array_diff(array_keys($json), $allowedBodyKeys);

        if ($invalid !== []) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "invalid JSON schema: '" . implode(", ", $invalid) . "' key is/are invalid."]));

            return $response;
        }

        if ($json === []) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "Nothing to update: send at least one of email, subject or message"]));

            return $response;
        }

        if (array_key_exists("email", $json) && filter_var($json["email"], FILTER_VALIDATE_EMAIL) === false) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "The email provided is invalid"]));

            return $response;
        }

        try {
            $contact = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

            $contact = array_merge($contact, $json);
            $contact["dateOfLastUpdate"] = time();

            if (file_put_contents($path, json_encode($contact, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), LOCK_EX) === false) {
                $response = new Response();
                $response->sendResponse(500, json_encode(["error" => "could not write file"]));

                return $response;
            }
        } catch (\JsonException $e) {
            $response = new Response();
            $response->sendResponse(500, json_encode(["error" => "Error reading or writing JSON file"]));

            return $response;
        }

        $response = new Response();
        $response->sendResponse(200, json_encode($contact));

        return $response;
    }
}
