<?php
include_once 'helpers/helpers.php';
function route($method, $urlList, $requestData)
{
    switch ($method) {
        case 'POST':
            $conn = connectToDB();
            if ($conn->connect_error) {
                echo $conn->connect_error;
                setHTTPStatus(500);
                return;
            }
            $email = $requestData->body->email;
            $email = strtolower(trim($email));
            $rawPassword = $requestData->body->password;
            $password = sha1($rawPassword);

            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND password = ?");
            $stmt->bind_param("ss", $email, $password);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $userId = isset($row['id']) ? $row['id'] : null;
            if (is_null($userId)) {
                setHTTPStatus(401);
                echo 'Incorrect credentials.';
            } else {
                generateAuthToken($userId);
            }

            $stmt->close();
            $conn->close();
            break;

        default:
            setHTTPStatus(400);
            break;
    }
}

function generateAuthToken($userId) {
    
}