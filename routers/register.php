<?php
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
            $fullName = $requestData->body->fullName;
            $email = $requestData->body->email;
            $rawPassword = $requestData->body->password;
            $password = sha1($rawPassword);

            $emailsResult = $conn->query("SELECT id FROM users WHERE email='$email'");
            if (is_null($emailsResult)) {
                $addUser = $conn->query("INSERT INTO users (fullName, email, password) VALUES ('$fullName', '$email', '$password')");
            }else{
                echo "Email already has an account";
                setHTTPStatus(409);
            }

            break;

        default:
            setHTTPStatus(400);
            break;
    }
}
