<?php
include_once 'helpers/helpers.php';
function route($method, $urlList, $requestData)
{
    checkAuth();
}

function checkAuth()
{
    $authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : "";
    $conn = connectToDB();
    $userID = $conn->query("SELECT id FROM tokens WHERE tokenVal='$authHeader'");
    if (isset($userID)) {
        return true;
    } else {
        return false;
    }
}
