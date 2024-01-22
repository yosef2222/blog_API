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
    $servername = getenv('DB_SERVERNAME') ?: "127.0.0.1";
    $username = getenv('DB_USER') ?: "root";
    $password = getenv('DB_PASS') ?: "";
    $database = getenv('DB_NAME') ?: "blog_DB";
    $conn = new mysqli($servername, $username, $password, $database);
    return $conn;
}

function updateAuthToken($userId)
{
    $conn = connectToDB();
    $token = bin2hex(random_bytes(32));

    $stmt = $conn->prepare("UPDATE tokens SET tokenValue = ? WHERE userId = ?");
    $stmt->bind_param("ss", $token, $userId);

    if ($stmt->execute()) {

        $stmt->close();
        $conn->close();
        return $token;
    } else {

        $stmt->close();
        $conn->close();
        return null;
    }
}

function createAuthToken($userId)
{
    $conn = connectToDB();
    $token = bin2hex(random_bytes(32));

    $stmt = $conn->prepare("INSERT INTO tokens (userId, tokenValue) VALUES (?, ?)");
    $stmt->bind_param("ss", $userId, $token);

    if ($stmt->execute()) {

        $stmt->close();
        $conn->close();
        return $token;
    } else {

        $stmt->close();
        $conn->close();
        return null;
    }
}

function getTokenFromHeaders()
{
    $headers = getallheaders();

    if (isset($headers['Authorization'])) {
        $authorizationHeader = $headers['Authorization'];

        if (strpos($authorizationHeader, 'Bearer ') === 0) {
            return substr($authorizationHeader, 7);
        }
    }

    return null;
}

function isAuthentecated($token)
{
    $userId = getUserId($token);

    if (is_null($userId)) {
        return false;
    } else {
        return true;
    }
}

function getUserId($token)
{
    $conn = connectToDB();
    $stmt = $conn->prepare("SELECT userId FROM tokens WHERE tokenValue = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $userId = isset($row['userId']) ? $row['userId'] : null;
    return $userId;
}

function admin($userId)
{

    $conn = connectToDB();
    $stmt = $conn->prepare("SELECT admin FROM users WHERE id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $admin = isset($row['admin']) ? $row['admin'] : null;

    return isset($admin) ? $admin : false;
}

function generateSlug($title)
{
    $cleanedTitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $title);

    $lowercaseTitle = strtolower($cleanedTitle);

    $slug = str_replace(' ', '-', $lowercaseTitle);

    $slug = substr($slug, 0, 32);

    return $slug;
}
