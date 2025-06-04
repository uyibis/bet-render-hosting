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

class Supportticketreplies extends \Phalcon\Mvc\Model {

    public $db; 
    public $ticketid; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addTicketReply($data)
    {
        $query = "INSERT INTO supportticketreplies (ticketid, authorid, author, details, status, date) VALUES (:ticketid, :authorid, :author, :details, :status, :date)";
        $result = $this->db->query($query, [
                        "ticketid" => $data['ticketid'],
                        "authorid" => $data['authorid'],
                        "author" => $data['author'],
                        "details" => $data['details'],
                        "status" => $data['status'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeTicket($ticketid)
    {
        $query = "DELETE FROM supportticketreplies WHERE ticketid=:ticketid";
        $result = $this->db->query($query, [
                      "ticketid" => $ticketid
                    ]
                  );
        return $result->numRows();
    }

    public function allTicketReplies($ticketid)
    {
        $query = "SELECT * FROM supportticketreplies WHERE ticketid=:ticketid";
        $result = $this->db->query($query, ["ticketid" => $ticketid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

}
?>