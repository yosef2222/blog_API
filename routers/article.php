<?php
include_once 'helpers/helpers.php';
function route($method, $urlList, $requestData)
{
    switch ($method) {
        case 'POST':
            $token = getTokenFromHeaders();
            $authed = isAuthentecated($token);
            if (!$authed) {
                setHTTPStatus(403);
                echo "not authentecated";
                return;
            }
            $userId = getUserId($token);
            if (!admin($userId)) {
                setHTTPStatus(403);
                echo "User is not admin";
                return;
            }
            $title = $requestData->body->title;
            $text = $requestData->body->text;
            $slug = generateSlug($title);
            $publishNow = $requestData->body->publishNow;

            $conn = connectToDB();
            if ($publishNow) {
                $todayDate = date("Y-m-d");
                $stmt = $conn->prepare("INSERT INTO article (title, text, slug, datePublished, userId) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $title, $text, $slug, $todayDate, $userId);
            } else {
                $stmt = $conn->prepare("INSERT INTO article (title, text, slug, userId) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sss", $title, $text, $slug, $userId);
            }
            if ($stmt->execute()) {
                setHTTPStatus(200);
                echo "Article was created successfully";
            }
            break;

        case 'GET':
            $slug = isset($_GET['slug']) ? $_GET['slug'] : null;
            $conn = connectToDB();

            if (isset($slug)) {
                $stmt = $conn->prepare("SELECT * FROM article WHERE slug = ?");
                $stmt->bind_param("s", $slug);
                $stmt->execute();

                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                $title = isset($row['title']) ? $row['title'] : null;
                $text = isset($row['text']) ? $row['text'] : null;
                $datePublished = isset($row['datePublished']) ? $row['datePublished'] : null;
                $dateCreated = isset($row['dateCreated']) ? $row['dateCreated'] : null;

                $data = array(
                    'title' => $title,
                    'text' => $text,
                    'slug' => $slug,
                    'datePublished' => $datePublished,
                    'dateCreated' => $dateCreated
                );

                $jsonData = json_encode($data);
                header('Content-Type: application/json');
                echo $jsonData;
            } else {
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $pageSize = isset($_GET['size']) ? intval($_GET['size']) : 5;

                $offset = ($page - 1) * $pageSize;

                $stmt = $conn->prepare("SELECT * FROM article WHERE datePublished IS NOT NULL LIMIT ? OFFSET ?");
                $stmt->bind_param("ii", $pageSize, $offset);
                $stmt->execute();

                $result = $stmt->get_result();
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                $articles = array();
                foreach ($rows as $row) {
                    $article = array(
                        'title' => isset($row['title']) ? $row['title'] : null,
                        'text' => isset($row['text']) ? $row['text'] : null,
                        'slug' => isset($row['slug']) ? $row['slug'] : null,
                        'datePublished' => isset($row['datePublished']) ? $row['datePublished'] : null,
                        'dateCreated' => isset($row['dateCreated']) ? $row['dateCreated'] : null,
                    );
                    $articles[] = $article;
                }

                $data = array(
                    'articles' => $articles,
                    'page' => $page,
                    'pageSize' => $pageSize,
                );

                $jsonData = json_encode($data);

                header('Content-Type: application/json');

                echo $jsonData;
            }
            break;
        case 'PUT':
            $token = getTokenFromHeaders();
            $authed = isAuthentecated($token);
            if (!$authed) {
                setHTTPStatus(403);
                echo "not authentecated";
                return;
            }
            $userId = getUserId($token);
            if (!admin($userId)) {
                setHTTPStatus(403);
                echo "User is not admin";
                return;
            }
            $conn = connectToDB();
            $slug = isset($_GET['slug']) ? $_GET['slug'] : null;

            if ($slug !== null) {

                $title = isset($requestData->body->title) ? $requestData->body->title : null;
                $text = isset($requestData->body->text) ? $requestData->body->text : null;
                $query = "UPDATE article SET ";
                $paramTypes = "";
                $bindParams = array();

                if ($title !== null) {
                    $query .= "title = ?, ";
                    $paramTypes .= "s";
                    $bindParams[] = &$title;
                }

                if ($text !== null) {
                    $query .= "text = ?, ";
                    $paramTypes .= "s";
                    $bindParams[] = &$text;
                }

                $query = rtrim($query, ', ');
                $query .= " WHERE slug = ?";

                $paramTypes .= "s";
                $bindParams[] = &$slug;

                $stmt = $conn->prepare($query);
                if ($stmt) {
                    $stmt->bind_param($paramTypes, ...$bindParams);
                    $stmt->execute();
                    $stmt->close();
                    $conn->close();
                    echo "updated successfully";

                    return;
                } else {
                    echo "Error preparing statement: " . $conn->error;
                }
            } else {
                setHTTPStatus(400);
                echo "Error: Missing or invalid slug parameter.";
            }
            $conn->close();
            break;

        case 'DELETE':
            $token = getTokenFromHeaders();
            $authed = isAuthentecated($token);
            if (!$authed) {
                setHTTPStatus(403);
                echo "not authentecated";
                return;
            }
            $userId = getUserId($token);
            if (!admin($userId)) {
                setHTTPStatus(403);
                echo "User is not admin";
                return;
            }
            $conn = connectToDB();
            $slug = isset($_GET['slug']) ? $_GET['slug'] : null;

            if ($slug !== null) {
                $stmt = $conn->prepare("DELETE FROM article WHERE slug = ?");
                $stmt->bind_param("s", $slug);
                $stmt->execute();
                $stmt->close();
                echo "deleted successfully";
            } else {
                setHTTPStatus(400);
                echo "Error: Missing or invalid slug parameter.";
            }

            $conn->close();
            break;
        default:
            setHTTPStatus(400);
            break;
    }
}
