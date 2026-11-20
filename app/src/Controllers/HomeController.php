<?php

namespace App\Controllers;

use Lib\Http\{Request, Response};
use Lib\Controllers\AbstractController;

class HomeController extends AbstractController
{
    public function process(Request $request): Response
    {
        $response = new Response();
        $response->statusCode = 200;
        $response->content = 'success';

        return $response;
    }
}