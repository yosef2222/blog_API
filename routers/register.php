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
            $fullName = strtolower(trim($fullName));
            $email = $requestData->body->email;
            $email = strtolower(trim($email));
            $rawPassword = $requestData->body->password;
            $password = sha1($rawPassword);

            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $emailsResult = isset($row['id']) ? $row['id'] : null;


            if (is_null($emailsResult)) {
                $stmt = $conn->prepare("INSERT INTO users (fullName, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $fullName, $email, $password);

                if ($stmt->execute()) {
                    echo "User added successfully.";
                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();

                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $userId = isset($row['id']) ? $row['id'] : null;
                    if (is_null($userId)) {
                    } else {
                        echo json_encode(createAuthToken($userId));
                    }
                } else {
                    echo "Error: " . $stmt->error;
                    setHTTPStatus(500);
                }
                $stmt->close();
                $conn->close();
            } else {
                echo "Email already has an account";
                setHTTPStatus(409);
            }

            break;

        default:
            echo "Bad request";
            setHTTPStatus(400);
            break;
    }
}
