<?php
include_once 'helpers/helpers.php';
function route($method, $urlList, $requestData)
{
    $token = getTokenFromHeaders();
    $authed = isAuthentecated($token);
    
}