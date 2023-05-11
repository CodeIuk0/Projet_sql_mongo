<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use MongoDB\Client;
use MongoDB\Database;

// Replace the placeholder with your Atlas connection string

// Create a new client and connect to the server

require __DIR__ . '/../vendor/autoload.php';
require __DIR__."./db.php";
require __DIR__."./type.php";




BackEndManager::ConnectDataBase("localhost:27017","local");

$app = AppFactory::create();

session_start();

$app->get('/Deauth', function (Request $request, Response $response, $args) {

    
  $_SESSION["user_id"] = null;
  $_SESSION["user_name"]= null;
  $_SESSION["password_hash"]= null;

  if(isset($_SESSION["user_id"]) || isset($_SESSION["user_name"]) || isset($_SESSION["password_hash"]))
    $response->getBody()->write(json_encode(Array("status"=>"success","data"=>"Deauth success")));
  else 
  $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Non connecter")));
  
  session_destroy();

  return $response;

});



$app->get('/Login/{username}/{user_password}', function (Request $request, Response $response, $args) {

    $username = $args["username"] or  null;
    $password = $args["user_password"] or  null;

    if($username == null || $password == null)
    {
        $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"UserName / Password Invalid !")));
        return $response;
    }

    $Client = new User($username,$password);

    if(isset($_SESSION["user_name"]) && isset($_SESSION["password_hash"]))
        {
            if(($_SESSION["user_name"]==$Client->UserName) && ($_SESSION["password_hash"]==$Client->Password))
            {
                $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Deja connecter en tant que ".$_SESSION["user_name"])));
                return $response;
            }
        }
    

    $User = BackEndManager::GetTable("users")->findOne(["user_name"=>$Client->UserName,"password_hash"=>$Client->Password]);     

    if(@$User["user_name"] == $Client->UserName && @$User["password_hash"] == $Client->Password)
    {
        $_SESSION["user_name"] = $User["user_name"];
        $_SESSION["user_id"]   = $User["_id"];
        $_SESSION["password_hash"] = $User["password_hash"];
        $response->getBody()->write(json_encode(Array("status"=>"success","data"=>"Bienvenu ".$Client->UserName)));

    } else  
        $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Utilisateur introuvable !")));

    return $response;

}); 


$app->get('/CreateUser/{username}/{user_password}', function (Request $request, Response $response, $args) {
  
    $username = $args["username"]      or  null;
    $password = $args["user_password"] or  null;

    if($username == null || $password == null)
    {
        $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"UserName / Password Invalid !")));
        return $response;
    }

    $NewUser =  new User($username,$password);
     
    $InseredData = BackEndManager::GetTable("users")->updateOne(
        ["user_name"=>$username],
        ['$setOnInsert'=>$NewUser->ToArray()],['upsert' => true]);

    if($InseredData->getUpsertedCount() == 1)
    {
        $response->getBody()->write(json_encode(Array("status"=>"success","data"=>"Creation du compte !")));
        $_SESSION["user_name"] = $NewUser->UserName;
        $_SESSION["user_id"]   = $InseredData->getUpsertedId();
        $_SESSION["password_hash"] = $NewUser->Password;

    } else
        $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Compte deja existant !")));
    

    return $response;
});


$app->get('/AddBook/{title}/{pages}/{summary_b64}/{tags}/{editors}', function (Request $request, Response $response, $args) 
{
    if(empty($_SESSION) || empty($_SESSION["user_name"]) || empty($_SESSION["password_hash"]))
    {
        $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Please login")));
        return $response;
    }

    $title          = $args["title"]        or  null;
    $pages          = $args["pages"]        or  null;
    $summary_b64    = $args["summary_b64"]  or  null;
    $tags           = $args["tags"]         or  null;
    $editors        = $args["editors"]      or  null;

    if($title == null || $pages == null || $summary_b64 == null || $tags == null || $editors == null)
    {
        $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Invalid Book Data")));
        return $response;
    }
     
    $NewBook = new Book($title,$pages,$summary_b64,$tags,$editors,$_SESSION["user_id"],time());

    $InseredData = BackEndManager::GetTable("books")->updateOne(
        ["title" => $title,"editors"=>$editors,"tags"=>$tags],
        ['$setOnInsert'=>$NewBook->ToArray()],['upsert' => true]);

    if($InseredData->getUpsertedCount() == 1)
       $response->getBody()->write(json_encode(Array("status"=>"success","data"=>"Book added !")));
    else
       $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Book already added !")));
    
    return $response;
});

$app->get('/GetAllBooks', function (Request $request, Response $response, $args) 
{
    $response->getBody()->write(json_encode(BackEndManager::GetTable("books")->find()->toArray()));
    $book_data = BackEndManager::Join("books","comment","_id","book_id",null)->toArray();

    foreach ( $book_data as $key => $book) {
        $mean = 0;  

      foreach ( $book["comment"] as $key2 => $comment) {

            if(!empty($comment["note"]))
              $mean +=$comment["note"];
        }

        if($mean > 0 && count($book["comment"]) > 0)
              $mean = $mean / count($book["comment"]);

        $book["mean"] = $mean; 

    }

   $response->getBody()->write(json_encode(Array("status"=>"success","data"=> $book_data)));

    return $response;
});


$app->get('/GetBook/{id}', function (Request $request, Response $response, $args) 
{
    $id = $args["id"] or null;

    if($id == null)
     {
        $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Invalid book id")));
        return $response;
     }

    $book_id = BackEndManager::ObjectId($id);

    if($book_id==null)
    {
        $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Invalid book id")));
    
        return $response;
    }

    $book_data = BackEndManager::Join("books","comment","_id","book_id",$book_id)->toArray()[0];

    $mean = 0;

    foreach ( $book_data["comment"] as $key => $value) {

        $mean +=$value["note"];

    }

    $mean  = $mean / count($book_data["comment"]);

    $book_data["mean"] = $mean;

    $response->getBody()->write(json_encode(Array("status"=>"success","data"=> $book_data)));

    return $response;


});




$app->get('/Comment/{book_id}/{comment}/{note}', function (Request $request, Response $response, $args) 
{
    if(empty($_SESSION) || empty($_SESSION["user_name"]) || empty($_SESSION["password_hash"]))
    {
        $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Please login")));
        return $response;
    }

    $book_id_   = $args["book_id"]  or  null;
    $user_id_    = $_SESSION["user_id"]  or  null;
    $note_       = $args["note"]         or  null;
    $comment_    = $args["comment"]      or  null;

    if($book_id_ == null  || $user_id_ == null|| $note_ == null || $comment_ == null)
    {
        $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Invalid Comment Data")));
        return $response;
    }

    $CommentData = new Comment($book_id_,$user_id_,$note_,$comment_);

    $InseredData = BackEndManager::GetTable("comment")->updateOne(
        ["user_id"=>$CommentData->UserId,"book_id"=>$CommentData->BookId],
        ['$setOnInsert'=>$CommentData->ToArray()],['upsert' => true]);

    if($InseredData->getUpsertedCount() == 1)
    {
    $response->getBody()->write(json_encode(Array("status"=>"success","data"=>"Comment added to book")));
    } else
    $response->getBody()->write(json_encode(Array("status"=>"failed","data"=>"Failed insert coomment to book")));

    return $response;
});


$app->run();