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

class Reviews extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addReview($data)
    {
        $query = "INSERT INTO reviews (playerid, name, email, phone, photo, rating, status, details, date) VALUES (:playerid, :name, :email, :phone, :photo, :rating, :status, :details, :date)";
        $result = $this->db->query($query, [
                        "playerid" => $data['playerid'],
                        "name" => $data['name'],
                        "email" => $data['email'],
                        "phone" => $data['phone'],
                        "photo" => $data['photo'],
                        "rating" => $data['rating'],
                        "status" => $data['status'],
                        "details" => $data['details'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function updateRatingReview($postid, $postdata, $tableLabel)
    {
        $query = "UPDATE reviews SET $tableLabel=:tableLabelValue WHERE id=:postid";
        $result = $this->db->query($query, [
                      "tableLabelValue" => $postdata,
                      "postid" => $postid
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeReview($reviewid)
    {
        $query = "DELETE FROM reviews WHERE id=:id";
        $result = $this->db->query($query, ["id" => $reviewid]);
        return $result->numRows();
    }

    public function allReviews()
    {
        $query = "SELECT * FROM reviews";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allReviewsByPlayer($playerid)
    {
        $query = "SELECT * FROM reviews WHERE playerid=:playerid AND status='approved'";
        $result = $this->db->query($query, ["playerid" => $playerid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allReviewsByAdmin($playerid)
    {
        $query = "SELECT * FROM reviews WHERE playerid=:playerid";
        $result = $this->db->query($query, ["playerid" => $playerid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }
}
?>