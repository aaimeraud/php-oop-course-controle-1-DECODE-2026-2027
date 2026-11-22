<?php

namespace App\Controllers;


use Lib\Http\{Request, Response};
use Lib\Controllers\AbstractController;

class ContactListController extends AbstractController
{
    public function process(Request $request): Response
    {
        header('Content-Type: application/json; charset=utf-8');

        $path = __DIR__ . "/../../var/contacts/";

        $contactsDir = array_diff(scandir($path), array(".", "..", ".DS_Store"));

        $json = [];
        foreach ($contactsDir as $file) {
            if (str_ends_with(strtolower($file), ".json")) {
                try {
                    $content = json_decode(file_get_contents("$path$file"), null, 512, JSON_THROW_ON_ERROR);
                    $json[] = $content;
                } catch (\JsonException $e) {
                    error_log("Broken JSON file: \"$file\"");
                }
            }
        }

        $response = new Response();
        $response->sendResponse(200, json_encode($json));
        return $response;
    }
}