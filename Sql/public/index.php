<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;



// Replace the placeholder with your Atlas connection string

// Create a new client and connect to the server

require __DIR__ . '/../vendor/autoload.php';
require __DIR__."./db.php";


$app = AppFactory::create();

session_start();

function CreateAccount(PDO $db,$username,$password)
{
    $user_uid = uniqid(uniqid());

    $q = $db->prepare("INSERT INTO `users`(`user_id`, `user_name`, `password_hash`) VALUES (:uuid,:name,:password_hash)");
    $q->bindParam(":uuid",$user_uid);
    $q->bindParam(":name",$username);
    $q->bindParam(":password_hash",$password);
    $q->execute();

    $lib_uid = uniqid(uniqid());

    $_SESSION["user_name"] = $username;
    $_SESSION["user_id"]   = $user_uid;
    $_SESSION["password_hash"] = $password;

}

$app->get('/Deauth', function (Request $request, Response $response, $args) {

  $_SESSION["user_id"] = null;
  $_SESSION["user_name"]= null;
  $_SESSION["password_hash"]= null;

  session_destroy();$response->getBody()->write("Deauth success");

  return $response;

  

});

$app->get('/Login/{username}/{user_password}', function (Request $request, Response $response, $args) {

    $db = ConnectDB();

    $username = $args["username"];
    $password = hash("sha256",$args["user_password"]);

    if($_SESSION)
    {
        if(($_SESSION["user_name"]==$username) && ($_SESSION["password_hash"]==$password))
        {
            $q = $db->prepare("SELECT * FROM users WHERE users.user_name LIKE :name AND users.password_hash LIKE :password");
            $q->bindParam(":name",$_SESSION["user_name"]);
            $q->bindParam(":password",$_SESSION["password_hash"]);
            $q->execute();

            $user_data = $q->fetchAll();

            if(count($user_data))
            {
                $response->getBody()->write("Deja connecter en tant que ".$_SESSION["user_name"]);

                return $response;
            }

        }
    }
   
    $q = $db->prepare("SELECT * FROM users WHERE users.user_name LIKE :name AND users.password_hash LIKE :password");
    $q->bindParam(":name",$username);
    $q->bindParam(":password",$password);
    $q->execute(); 

    $user_data = $q->fetchAll();

    if(count($user_data))
    {
        $response->getBody()->write("Bienvenu ".$user_data[0]["user_name"]);
        $_SESSION["user_name"] = $user_data[0]["user_name"];
        $_SESSION["user_id"]   = $user_data[0]["user_id"];
        $_SESSION["password_hash"] = $user_data[0]["password_hash"];
    }
    else  
      $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Utilisateur introuvable")));

    return $response;

});


$app->get('/CreateUser/{username}/{user_password}', function (Request $request, Response $response, $args) {
    $username = $args["username"];
    $password = hash("sha256",$args["user_password"]);
    $db = ConnectDB();
    $q = $db->prepare("SELECT user_name FROM users WHERE users.user_name LIKE :name");
    $q->bindParam(":name",$username);
    $q->execute();
 
    if(!count($q->fetchAll()))
    {
        $response->getBody()->write(json_encode(Array("status"=>"success","data"=>"Creation du compte !")));
        CreateAccount($db,$username,$password);
    }
    else  
      $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Name already used")));

    return $response;
});

$app->get('/GetAllBooks', function (Request $request, Response $response, $args) 
{
    $db = ConnectDB();
    $books = $db->query("SELECT * from books");

    $books = $books->fetchAll();

    $response->getBody()->write(json_encode($books));
    
    return $response;
});

$app->get('/Comment/{book_id}/{comment}/{note}', function (Request $request, Response $response, $args) 
{
    $db = ConnectDB();

    if(!$_SESSION)
    {
        $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Please Login")));
        return $response;

    } else if($_SESSION)
    {
        if(!$_SESSION["user_name"] || !$_SESSION["password_hash"])
        {
            $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Please Login")));
            return  $response;
        }
        

    $q = $db->prepare("SELECT * FROM books  WHERE books.book_id LIKE :book_id");
    
    $q->bindParam(":book_id",$args["book_id"]);
    
    $q->execute();

    if(!count($q->fetchAll()))
    {
        $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Invalid book id")));
        return $response;
    }
    
    }
     
    $comment_id = uniqid(uniqid()); 

    $q = $db->prepare("SELECT * FROM librairie WHERE user_id LIKE :user_id AND book_id LIKE :book_id");
    $q->bindParam(":user_id",$_SESSION["user_id"]); 
    $q->bindParam(":book_id",$args["book_id"]); 
    $q->execute();
    $q = $q->fetchAll();

    if(!count($q))
    {
        $current_user_data = $db->prepare("INSERT INTO `comment`(`comment_id`, `comment`, `note`) VALUES (:id,:comment,:note)");
    
        $current_user_data->bindParam(":id",$comment_id); 
        $current_user_data->bindParam(":comment",$args["comment"]); 
        $current_user_data->bindParam(":note",$args["note"]); 

        $current_user_data->execute();


        $command_id = uniqid(uniqid());
        $advancement = 0;
        

        $current_user_data = $db->prepare("INSERT INTO `librairie`(`command_id`, `advancement`, `comment_id`, `user_id`, `book_id`) VALUES (:command_id,:advancement,:comment_id,:user_id,:book_id)");
        $current_user_data->bindParam(":command_id",$command_id); 
        $current_user_data->bindParam(":advancement",$advancement); 
        $current_user_data->bindParam(":comment_id",$comment_id); 
        $current_user_data->bindParam(":user_id",$_SESSION["user_id"]);
        $current_user_data->bindParam(":book_id",$args["book_id"]);

        $current_user_data->execute();

        $response->getBody()->write(json_encode(Array("status"=>"sucess","data"=>"success add comment")));

    } else
    {
        $current_user_data = $db->prepare("UPDATE comment SET`comment`=:newcomment,`note`=:newnote WHERE comment.comment_id LIKE :comment_id");
        $current_user_data->bindParam(":comment_id",$q[0]["comment_id"]); 
        $current_user_data->bindParam(":newcomment",$args["comment"]); 
        $current_user_data->bindParam(":newnote",$args["note"]); 

        $current_user_data->execute();

        $response->getBody()->write(json_encode(Array("status"=>"sucess","data"=>"success modified comment")));
   
    }

    
    


    return $response;
});

$app->get('/GetBook/{book_id}', function (Request $request, Response $response, $args) 
{
    $id = $args["book_id"];
    
    $db = ConnectDB();
    $books = $db->query("SELECT * from books");

    $books = $books->fetchAll();

    
    $ret_response = json_encode(Array("status"=>"failed","data" => "Book not found !"));

    foreach ($books as $key => $value) {
        
        if($value["book_id"]==$id)
        {
            $ret_response =  json_encode(Array("status"=>"sucess","data"=>json_encode($value)));
            break;
        }

    }

    $response->getBody()->write($ret_response);
    
    return $response;

});

$app->get('/AddBook/{book_id}/{title}/{pages}/{summary_b64}/{tags}/{editors}', function (Request $request, Response $response, $args) 
{
    $db = ConnectDB();

    if(!$_SESSION)
    {
        $response->getBody()->write("Please login");
        return $response;

    } else if($_SESSION)
    {
        if(!$_SESSION["user_name"] || !$_SESSION["password_hash"])
        { 
            $response->getBody()->write(json_encode(Array("failed"=>"sucess","data"=>"Please login")));
            return  $response;
        }

        $q = $db->prepare("SELECT * FROM users WHERE users.user_name LIKE :name AND users.password_hash LIKE :password");
       
        $q->bindParam(":name",$_SESSION["user_name"]);
        $q->bindParam(":password",$_SESSION["password_hash"]);
        $q->execute();

        if(!count($q->fetchAll()))
        {
            $response->getBody()->write(json_encode(Array("failed"=>"sucess","data"=>"Invalid login")));
            return $response;
        }
    }

        $b = $db->prepare("SELECT * FROM books WHERE books.book_id LIKE :book__id");
       
        $b->bindParam(":book__id",$args["book_id"]);
        $b->execute();

        if(count($b->fetchAll()))
        {
            $response->getBody()->write(json_encode(Array("failed"=>"sucess","data"=>"Book already exist")));
            return $response;
        }
    

    $book_id  = $args["book_id"];
    $title  = $args["title"];
    $pages  = $args["pages"];
    $summary_b64  = $args["summary_b64"];
    $tags  = $args["tags"];
    $editors  = $args["editors"];
    $user = $_SESSION["user_name"];
    $ctime = time();

    $db->query("INSERT INTO `books`(`book_id`, `title`, `pages`, `summary`, `tags`, `editors`, `user_who_added`, `user_when_add`) VALUES ('$book_id','$title','$pages','$summary_b64','$tags','$editors','$user','$ctime')");
   
    $response->getBody()->write(json_encode(Array("failed"=>"sucess","data"=>"Book added success !")));
    
    return $response;

   
});


$app->run();