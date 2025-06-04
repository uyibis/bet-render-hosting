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

class Supporttickets extends \Phalcon\Mvc\Model {

    public $db; 
    public $ticketid; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addTicket($data)
    {
        $query = "INSERT INTO supporttickets (origin, fromid, from, updatedby, title, toid, to, type, details, status, updateddate, date) VALUES (:origin, :fromid, :from, :updatedby, :title, :toid, :to, :type, :details, :status, :updateddate, :date)";
        $result = $this->db->query($query, [
                        "origin" => $data['origin'],
                        "fromid" => $data['fromid'],
                        "from" => $data['from'],
                        "updatedby" => $data['updatedby'],
                        "title" => $data['title'],
                        "toid" => $data['toid'],
                        "to" => $data['to'],
                        "type" => $data['type'],
                        "details" => $data['details'],
                        "status" => $data['status'],
                        "updateddate" => $data['updateddate'],
                        "date" => $data['date']
                    ]
                  ); 
        $addedID = $result->lastInsertId();
        return $addedID;
    }

    public function removeTicket($ticketid)
    {
        $query = "DELETE FROM supporttickets WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $ticketid
                    ]
                  );
        return $result->numRows();
    }

    public function adminAllTickets()
    {
        $query = "SELECT * FROM supporttickets";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }
    
    public function adminTicketsCount()
    {
        $query = "SELECT * FROM supporttickets";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }
    
    public function merchantAllTickets($merchantid)
    {
        $query = "SELECT * FROM supporttickets WHERE fromid=:userid OR from=:userid OR toid=:userid OR to=:userid";
        $result = $this->db->query($query, ["userid" => $merchantid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }
    
    public function merchantTicketsCount($merchantid)
    {
        $query = "SELECT * FROM supporttickets WHERE fromid=:userid OR from=:userid OR toid=:userid OR to=:userid";
        $result = $this->db->query($query, ["userid" => $merchantid]); 
        return $result->numRows();
    }
    
    public function allTickets($userid)
    {
        $query = "SELECT * FROM supporttickets WHERE fromid=:userid OR from=:userid OR toid=:userid OR to=:userid";
        $result = $this->db->query($query, ["userid" => $userid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function singleTicket($ticketid)
    {
        $query = "SELECT * FROM supporttickets WHERE id=:ticketid";
        $result = $this->db->query($query, [
                      "ticketid" => $ticketid
                    ]
                  ); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function updateTicket($ticketid, $author, $status, $date)
    {
        $query = "UPDATE supporttickets SET updatedby=:updatedby, status=:status, updateddate=:updateddate WHERE id=:ticketid";
        $result = $this->db->query($query, [
                      "updatedby" => $author,
                      "status" => $status,
                      "updateddate" => $date,
                      "ticketid" => $ticketid
                    ]
                  ); 
        return $result->numRows();
    }

}
?>