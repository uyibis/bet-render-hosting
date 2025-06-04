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

class Teams extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addEntry($data)
    {
        $query = "INSERT INTO teams (team, type, photo, leagueid, league, details, date) VALUES (:team, :type, :photo, :leagueid, :league, :details, :date)";
        $result = $this->db->query($query, [
                        "team" => $data['team'],
                        "type" => $data['type'],
                        "photo" => $data['photo'],
                        "leagueid" => $data['leagueid'],
                        "league" => $data['league'],
                        "details" => $data['details'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function updateEntry($postid, $postdata, $tableLabel)
    {
        $query = "UPDATE teams SET $tableLabel=:tableLabelValue WHERE id=:postid";
        $result = $this->db->query($query, [
                      "tableLabelValue" => $postdata,
                      "postid" => $postid
                    ]
                  ); 
        return $result->numRows();
    }

    public function singleEntry($postid)
    {
        $query = "SELECT * FROM teams WHERE id=:postid OR team=:postid";
        $result = $this->db->query($query, ["postid" => $postid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function removeEntry($postid)
    {
        $query = "DELETE FROM teams WHERE id=:id";
        $result = $this->db->query($query, ["id" => $postid]);
        return $result->numRows();
    }

    public function allEntries()
    {
        $query = "SELECT * FROM teams";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesByType($postid)
    {
        $query = "SELECT * FROM teams WHERE type=:postid";
        $result = $this->db->query($query, ["postid" => $postid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesByLeague($postid)
    {
        $query = "SELECT * FROM teams WHERE leagueid=:postid";
        $result = $this->db->query($query, ["postid" => $postid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesSearch($postid)
    {
        $query = "SELECT * FROM teams WHERE leagueid=:postid OR league=:postid OR type=:postid";
        $result = $this->db->query($query, ["postid" => $postid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function checkEntryExist($data)
    {
        $this->db = $this->getDi()->getShared('db');
        $query = "SELECT * FROM teams WHERE team=:mydata";
        $result = $this->db->query($query, ["mydata" => $data]); 
        return $result->numRows();
    }

    public function getEntryID($entryname)
    {
        $query = "SELECT * FROM teams WHERE team=:entryname";
        $result = $this->db->query($query, ["entryname" => $entryname]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->id;
        return $data;
    }

    public function sumTeams()
    {
        $query = "SELECT * FROM teams";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }

    public function sumTeamsByLeague($categoryid)
    {
        $query = "SELECT * FROM teams WHERE leagueid=:categoryid";
        $result = $this->db->query($query, ["categoryid" => $categoryid]); 
        return $result->numRows();
    }
}
?>