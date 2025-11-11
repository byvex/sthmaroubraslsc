<?php

use App\Services\GlobalRelay;

function globalRelay() {
    return GlobalRelay::instance();
}

function resJson($response = [], $code = 200)
{
    if (is_string($response)) {
        $response = ['message' => $response];
    }
    return response()->json($response, $code);
}
