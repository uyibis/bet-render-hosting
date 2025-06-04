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

class Comments extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addEntry($data)
    {
        $query = "INSERT INTO comments (postid, userid, username, photo, type, status, details, time, date) VALUES (:postid, :userid, :username, :photo, :type, :status, :details, :time, :date)";
        $result = $this->db->query($query, [
                        "postid" => $data['postid'],
                        "userid" => $data['userid'],
                        "username" => $data['username'],
                        "photo" => $data['photo'],
                        "type" => $data['type'],
                        "status" => $data['status'],
                        "details" => $data['details'],
                        "time" => $data['time'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function updateEntry($postid, $postdata, $tableLabel)
    {
        $query = "UPDATE comments SET $tableLabel=:tableLabelValue WHERE id=:postid";
        $result = $this->db->query($query, [
                      "tableLabelValue" => $postdata,
                      "postid" => $postid
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeEntry($postid)
    {
        $query = "DELETE FROM comments WHERE id=:id";
        $result = $this->db->query($query, ["id" => $postid]);
        return $result->numRows();
    }

    public function allEntries()
    {
        $query = "SELECT * FROM comments";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesByPost($postid)
    {
        $query = "SELECT * FROM comments WHERE postid=:postid AND status='approved'";
        $result = $this->db->query($query, ["postid" => $postid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesByAdmin($postid)
    {
        $query = "SELECT * FROM comments WHERE postid=:postid";
        $result = $this->db->query($query, ["postid" => $postid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function sumComments()
    {
        $query = "SELECT * FROM comments";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }

    public function sumCommentsByPosts($postid)
    {
        $query = "SELECT * FROM comments WHERE postid=:postid";
        $result = $this->db->query($query, ["postid" => $postid]); 
        return $result->numRows();
    }
}
?>