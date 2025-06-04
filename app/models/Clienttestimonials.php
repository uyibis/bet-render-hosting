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

class Clienttestimonials extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addNewEntry($data)
    {
        $query = "INSERT INTO clienttestimonials (type, author, authorphoto, authorjob, authorlocation, details, media, date) VALUES (:type, :author, :authorphoto, :authorjob, :authorlocation, :details, :media, :date)";
        $result = $this->db->query($query, [
                        "type" => $data['type'],
                        "author" => $data['author'],
                        "authorphoto" => $data['authorphoto'],
                        "authorjob" => $data['authorjob'],
                        "authorlocation" => $data['authorlocation'],
                        "details" => $data['details'],
                        "media" => $data['media'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function updateEntry($postid, $postdata, $tableLabel)
    {
        $query = "UPDATE clienttestimonials SET $tableLabel=:tableLabelValue WHERE id=:postid";
        $result = $this->db->query($query, [
                      "tableLabelValue" => $postdata,
                      "postid" => $postid
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeEntry($postid)
    {
        $query = "DELETE FROM clienttestimonials WHERE id=:id";
        $result = $this->db->query($query, ["id" => $postid]);
        return $result->numRows();
    }

    public function allEntries()
    {
        $query = "SELECT * FROM clienttestimonials";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allApprovedEntries()
    {
        $query = "SELECT * FROM clienttestimonials WHERE status='approved'";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }
}
?>