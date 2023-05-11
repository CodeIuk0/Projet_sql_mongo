<?php


class User 
{
    public string $UserName = "";
    public string $Password = "";

    function __construct(string $name,string $password)
    {
        $this->UserName = $name;
        $this->Password = hash("sha256",$password);
    }

    public function ToArray() : array
    {
        return ["user_name"=>$this->UserName,"password_hash"=>$this->Password];
    }
}

class Librarie 
{
    public int $Advancement = 0;
    public string $CommentId = "";
    public string $UserId = "";
    public string $BookId = "";

    function __construct(int $advancement,string $comment_id,string $user_id,string $book_id)
    {
        $this->Advancement = $advancement;
        $this->CommentId = $comment_id;
        $this->UserId = $user_id;
        $this->BookId = $book_id;
    }

    public function ToArray() : array
    {
        return ["advancement"=>$this->Advancement,"comment_id"=>$this->CommentId,"user_id"=>$this->UserId,"book_id"=>$this->BookId];
    }
}



class Comment 
{
    public string $Comment = "";
    public string $UserId = "";
    public MongoDB\BSON\ObjectID $BookId;
    public int $Note = 0;
    
    function __construct(string $book_id,string $user_id,int $note,string $comment)
    {
        $this->Comment = $comment;
        $this->Note    = $note;
        $this->UserId  = $user_id;
        $this->BookId  = BackEndManager::ObjectId($book_id);
    }

    public function ToArray() : array
    {
        return ["book_id"=>$this->BookId,"user_id"=> $this->UserId,"comment"=>$this->Comment,"note"=>$this->Note];
    }
}

class Book 
{
    public string $Title = "";
    public int $Pages = 0;
    public string $Summary = "";
    public string $Tags = "";
    public string $Editors = "";

    public string $UserIdCreatedBook = "";
    public int $WhenCreated = 0;


    function __construct(string $title,int $pages,string $summary,string $tags ,string $editors ,string $user_id_created_book, int $when_created)
    {
        $this->Title = $title;
        $this->Pages = $pages;
        $this->Summary = $summary;
        $this->Tags = $tags;
        $this->Editors = $editors;
    
        $this->UserIdCreatedBook = $user_id_created_book;
        $this->WhenCreated = $when_created;

    }

    public function ToArray() : array
    {
        return [

            "title"                => $this->Title,
            "pages"                => $this->Pages,
            "summary"              => $this->Summary,
            "tags"                 => $this->Tags,
            "editors"              => $this->Editors,
            "user_id_created_book" => $this->UserIdCreatedBook,
            "when_created"         => $this->WhenCreated
        ];
    }
}


?>