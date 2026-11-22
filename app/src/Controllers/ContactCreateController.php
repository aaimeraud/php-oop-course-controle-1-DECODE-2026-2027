<?php

namespace App\Controllers;


use App\Services\ContactStorage;
use Lib\Http\{Request, Response};
use Lib\Controllers\AbstractController;

class ContactCreateController extends AbstractController
{

    public function process(Request $request): Response
    {
        if (isset($request->getHeaders()["Content-Type"]) === false) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "No headers provided"]));
            return $response;
        }

        header('Content-Type: application/json; charset=utf-8');

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

        $response = new Response();
        $allowedBodyKeys = ["email", "subject", "message"];

        if (array_diff($allowedBodyKeys, array_keys($json)) == [] && array_diff(array_keys($json), $allowedBodyKeys) == []) {
            if (filter_var($json["email"], FILTER_VALIDATE_EMAIL) === false) {
                $response->sendResponse(400, json_encode(["error" => "The email provided is invalid"]));
                return $response;
            }

            try {
                $timestamp = time();

                $name = date("Y-m-d_H-i-s") . "_" . $json["email"];

                $data = array_merge($json, ["dateOfCreation" => $timestamp, 'dateOfLastUpdate' => $timestamp]);

                $storage = new ContactStorage();

                if ($storage->save($name, $data) === false) {
                    $response->sendResponse(500, json_encode(["error" => "could not write file"]));
                    return $response;
                }

                $response->sendResponse(201, json_encode(["file" => $name . ".json"]));
                return $response;
            } catch (\JsonException $e) {
                $response->sendResponse(400, json_encode(["error" => "Invalid JSON"]));
                return $response;
            } catch (\Throwable $e) {
                $response->sendResponse(500, json_encode(["error" => "Could not write JSON file"]));
                return $response;
            }
        } else {
            $missing = array_diff($allowedBodyKeys, array_keys($json));
            $invalid = array_diff(array_keys($json), $allowedBodyKeys);
            $response->statusCode = 400;

            if ($missing != [] && $invalid != []) {
                $response->content = json_encode(["error" => "invalid JSON schema: '" . implode(", ", $invalid) . "' is/are invalid and '" . implode(", ", $missing) . "' is/are missing."]);
            } elseif ($missing != []) {
                $response->content = json_encode(["error" => "invalid JSON schema: '" . implode(", ", $missing) . "' key is/are missing."]);
            } else {
                $response->content = json_encode(["error" => "invalid JSON schema: '" . implode(", ", $invalid) . "' key is/are invalid."]);
            }

        }


        return $response;
    }
}
