<?php  
//Manual Model and not auto generated Model :)
use Phalcon\DI;
use Phalcon\Mvc\Model;  
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\Query;
/** 
*Phalcon\Db::FETCH_OBJ //Phalcon 3
*Phalcon\Db\Enum::FETCH_OBJ //Phalcon 4
**/

class Gallery extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addNewGallery($data)
    {
        $query = "INSERT INTO gallery (authorid, author, postid, posttype, caption, type, media, tags, categoryid, category, subcategoryid, subcategory, details, date) VALUES (:authorid, :author, :postid, :posttype, :caption, :type, :media, :tags, :categoryid, :category, :subcategoryid, :subcategory, :details, :date)";
        $result = $this->db->query($query, [
                        "authorid" => $data['authorid'],
                        "author" => $data['author'],
                        "postid" => $data['postid'],
                        "posttype" => $data['posttype'],
                        "caption" => $data['caption'],
                        "type" => $data['type'],
                        "media" => $data['media'],
                        "tags" => $data['tags'],
                        "categoryid" => $data['categoryid'],
                        "category" => $data['category'],
                        "subcategoryid" => $data['subcategoryid'],
                        "subcategory" => $data['subcategory'],
                        "details" => $data['details'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeGallery($galleryid)
    {
        $query = "DELETE FROM gallery WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $galleryid
                    ]
                  );
        return $result->numRows();
    }

    public function getGalleryItems()
    {
        $query = "SELECT * FROM gallery";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }
    
    public function getGalleryContentByPost($postid,$posttype)
    {
        $query = "SELECT * FROM gallery WHERE postid=:postid AND posttype=:posttype";
        $result = $this->db->query($query, ["postid" => $postid, "posttype" => $posttype]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function getGalleryContentByCategory($categoryid)
    {
        $query = "SELECT * FROM gallery WHERE categoryid=:categoryid";
        $result = $this->db->query($query, ["categoryid" => $categoryid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

}
?>