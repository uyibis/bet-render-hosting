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

class Notifications extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addEntry($data)
    {
        $query = "INSERT INTO notifications (`title`, `details`, `fromid`, `from`, `type`, `status`, `userid`, `username`, `actionid`, `actionsubid`, `time`, `date`) VALUES (:title, :details, :fromid, :from, :type, :status, :userid, :username, :actionid, :actionsubid, :time, :date)";
        $result = $this->db->query($query, [
                        "title" => $data['title'],
                        "details" => $data['details'],
                        "fromid" => $data['fromid'],
                        "from" => $data['from'],
                        "type" => $data['type'],
                        "status" => $data['status'],
                        "userid" => $data['userid'],
                        "username" => $data['username'],
                        "actionid" => $data['actionid'],
                        "actionsubid" => $data['actionsubid'],
                        "time" => $data['time'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeEntry($entryid)
    {
        $query = "DELETE FROM notifications WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $entryid
                    ]
                  );
        return $result->numRows();
    }

    public function allEntries()
    {
        $query = "SELECT * FROM notifications ORDER BY id DESC";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesByUser($userid)
    {
        $query = "SELECT * FROM notifications WHERE userid=:userid ORDER BY id DESC";
        $result = $this->db->query($query, ["userid" => $userid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesType($type)
    {
        $query = "SELECT * FROM notifications WHERE type=:type ORDER BY id DESC";
        $result = $this->db->query($query, ["type" => $type]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesTypeByUser($userid,$type)
    {
        $query = "SELECT * FROM notifications WHERE userid=:userid AND type=:type ORDER BY id DESC";
        $result = $this->db->query($query, ["userid" => $userid, "type" => $type]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function singleEntry($userid,$entryid)
    {
        $query = "SELECT * FROM notifications WHERE userid=:userid AND id=:entryid";
        $result = $this->db->query($query, ["userid" => $userid, "entryid" => $entryid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function updateEntry($entryid, $data, $tableLabel)
    {
        $query = "UPDATE notifications SET $tableLabel=:tableLabelValue WHERE id=:entryid";
        $result = $this->db->query($query, [
                      "tableLabelValue" => $data,
                      "entryid" => $entryid
                    ]
                  ); 
        return $result->numRows();
    }

    public function updateEntryReadByUser($userid)
    {
        $query = "UPDATE notifications SET status='read' WHERE userid=:userid";
        $result = $this->db->query($query, ["userid" => $userid]); 
        return $result->numRows();
    }

    public function sumNotifications()
    {
        $query = "SELECT * FROM notifications WHERE status='unread'";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }

    public function sumNotificationsByUser($userid)
    {
        $query = "SELECT * FROM notifications WHERE userid=:userid AND status='unread'";
        $result = $this->db->query($query, ["userid" => $userid]); 
        return $result->numRows();
    }

    public function notificationsCOUNT($monthquery)
    {
        $query = "SELECT * FROM notifications WHERE date like '%$monthquery'";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }
}
?>