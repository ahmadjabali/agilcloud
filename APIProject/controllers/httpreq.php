<?php
function httpreq($req_code)
{
    if ($req_code == "200") {
    } elseif ($req_code == "400") {
        $responseData = [
            'message' => 'Error 400'
        ];
    } elseif ($req_code == "401") {
        $responseData = [
            'message' => 'Unauthorized'
        ];
    } elseif ($req_code == "403") {
        $responseData = [
            'message' => 'Error 403'
        ];
    } else {
    }
    http_response_code($req_code);
    // $REQUEST_response_code = json_encode($responseData);
    return @$responseData;
}
