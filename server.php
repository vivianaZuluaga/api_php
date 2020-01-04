<?php

    include "config.php";
    include "utils.php";

    $dbConn =  connect($db);
    //Definimos los recursos disponibles
    $allowedResourceTypes = [
        'posts',
    ];

    //Validamos el recurso
    $resourceType = $_GET['resource_type'];
    if(!in_array($resourceType, $allowedResourceTypes)){
        header("HTTP/1.1 400 Bad Request");
        die;
    }

    $resourceId = isset($_GET['resource_id']) ? $_GET['resource_id'] : '';
    //Generamos las respuestas
    header('Content-Type: application/json');
    switch(strtoupper($_SERVER['REQUEST_METHOD'])){
        case 'GET':
            if(empty($resourceId)){
                $sql = $dbConn->prepare("SELECT * FROM posts");
                $sql->execute();
                $sql->setFetchMode(PDO::FETCH_ASSOC);
                header("HTTP/1.1 200 OK");
                echo json_encode( $sql->fetchAll()  );
            }else{
                $sql = $dbConn->prepare("SELECT * FROM posts where id=:id");
                $sql->bindValue(':id', $resourceId);
                $sql->execute();
                header("HTTP/1.1 200 OK");
                echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
            }

            break;
        case 'POST':
            $input = $_POST;
            $sql = "INSERT INTO posts
                  (title, status, content, user_id)
                  VALUES
                  (:title, :status, :content, :user_id)";
            $statement = $dbConn->prepare($sql);
            bindAllValues($statement, $input);
            $statement->execute();
            $postId = $dbConn->lastInsertId();
            if($postId)
            {
              $input['id'] = $postId;
              header("HTTP/1.1 200 OK");
              echo json_encode($input);
            }

            break;
        case 'PUT':
            $input = $_GET;
            $postId = $input['id'];
            $fields = getParams($input);

            $sql = "
                  UPDATE posts
                  SET $fields
                  WHERE id='$postId'
                   ";

            $statement = $dbConn->prepare($sql);
            bindAllValues($statement, $input);

            $statement->execute();
            header("HTTP/1.1 200 OK");

            break;
        case 'DELETE':
            $id = $_GET['id'];
            $statement = $dbConn->prepare("DELETE FROM posts where id=:id");
            $statement->bindValue(':id', $id);
            $statement->execute();
            header("HTTP/1.1 200 OK");

            break;
    }
header("HTTP/1.1 400 Bad Request");
?>
