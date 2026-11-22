<?php

namespace App\Controllers;

use Lib\Http\{Request, Response};
use Lib\Controllers\AbstractController;

class ContactSelectController extends AbstractController
{
    public function process(Request $request): Response
    {
        header('Content-Type: application/json; charset=utf-8');

        $requestParams = $request->getParams();

        $filename = rawurldecode($requestParams['filename']);

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

        try {
            $content = json_decode(file_get_contents($path), null, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $response = new Response();
            $response->sendResponse(500, json_encode(["error" => "Error reading JSON file"]));

            return $response;
        }

        $response = new Response();
        $response->sendResponse(200, json_encode($content));

        return $response;
    }
}