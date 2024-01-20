<?php

function setHTTPStatus($statusCode)
{
    $statusCodes = [

        200 => 'OK',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        409 => 'Conflict',
        404 => 'Not Found',
        500 => 'Internal Server Error',
    ];

    if (array_key_exists($statusCode, $statusCodes)) {
        header($_SERVER['SERVER_PROTOCOL'] . ' ' . $statusCode . ' ' . $statusCodes[$statusCode]);
    } else {
        echo 'HTTP status code: ' . $statusCode;
    }
}

function connectToDB()
{
    $servername = "127.0.0.1";
    $username = "root";
    $password = "";
    $database = "blog_DB";
    $conn = new mysqli($servername, $username, $password, $database);
    return $conn;
}
