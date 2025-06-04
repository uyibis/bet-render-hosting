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

class Games extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addEntry($data)
    {
        $query = "INSERT INTO games (gameid, type, hometeamid, hometeam, hometeamscore, awayteamid, awayteam, awayteamscore, gameleagueid, gameleague, gameleagueseason, venue, stage, gamedate, gametime, status, details, date) VALUES (:gameid, :type, :hometeamid, :hometeam, :hometeamscore, :awayteamid, :awayteam, :awayteamscore, :gameleagueid, :gameleague, :gameleagueseason, :venue, :stage, :gamedate, :gametime, :status, :details, :date)";
        $result = $this->db->query($query, [
                        "gameid" => $data['gameid'],
                        "type" => $data['type'],
                        "hometeamid" => $data['hometeamid'],
                        "hometeam" => $data['hometeam'],
                        "hometeamscore" => $data['hometeamscore'],
                        "awayteamid" => $data['awayteamid'],
                        "awayteam" => $data['awayteam'],
                        "awayteamscore" => $data['awayteamscore'],
                        "gameleagueid" => $data['categoryid'],
                        "gameleague" => $data['gameleague'],
                        "gameleagueseason" => $data['gameleagueseason'],
                        "venue" => $data['venue'],
                        "stage" => $data['stage'],
                        "gamedate" => $data['gamedate'],
                        "gametime" => $data['gametime'],
                        "status" => $data['status'],
                        "details" => $data['details'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeEntry($postid)
    {
        $query = "DELETE FROM games WHERE id=:id";
        $result = $this->db->query($query, ["id" => $postid]);
        return $result->numRows();
    }

    public function allEntries($todayDate)
    {
        $query = "SELECT * FROM games WHERE gamedate=:todayDate ORDER BY id DESC";
        $result = $this->db->query($query, ["todayDate" => $todayDate]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allPendingEntries($todayDate)
    {
        $query = "SELECT * FROM games WHERE status='pending' AND gamedate=:todayDate ORDER BY STR_TO_DATE(gamedate, '%d-%m-%Y') ASC, cast(gametime as TIME) ASC";
        $result = $this->db->query($query, ["todayDate" => $todayDate]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allPlayedEntries($todayDate)
    {
        $query = "SELECT * FROM games WHERE status='played' AND gamedate=:todayDate ORDER BY STR_TO_DATE(gamedate, '%d-%m-%Y') ASC, cast(gametime as TIME) ASC";
        $result = $this->db->query($query, ["todayDate" => $todayDate]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesByLeague($categoryid)
    {
        $query = "SELECT * FROM games WHERE gameleagueid=:categoryid AND status='pending' ORDER BY STR_TO_DATE(gamedate, '%d-%m-%Y') ASC, cast(gametime as TIME) ASC";
        $result = $this->db->query($query, ["categoryid" => $categoryid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allLiveEntries()
    {
        $query = "SELECT * FROM games WHERE status='on-going' ORDER BY STR_TO_DATE(gamedate, '%d-%m-%Y') ASC, cast(gametime as TIME) ASC";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesByType($type,$todayDate)
    {
        $query = "SELECT * FROM games WHERE type=:type AND gamedate=:todayDate ORDER BY STR_TO_DATE(gamedate, '%d-%m-%Y') ASC, cast(gametime as TIME) ASC";
        $result = $this->db->query($query, ["type" => $type, "todayDate" => $todayDate]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesByDate($mydate)
    {
        $query = "SELECT * FROM games WHERE gamedate=:mydate AND status='pending' ORDER BY STR_TO_DATE(gamedate, '%d-%m-%Y') ASC, cast(gametime as TIME) ASC";
        $result = $this->db->query($query, ["mydate" => $mydate]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesByLeagueSeason($categoryid,$subcategory)
    {
        $query = "SELECT * FROM games WHERE gameleagueid=:categoryid AND gameleagueseason=:subcategory AND status='pending' ORDER BY STR_TO_DATE(gamedate, '%d-%m-%Y') ASC, cast(gametime as TIME) ASC";
        $result = $this->db->query($query, ["categoryid" => $categoryid, "subcategory" => $subcategory]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function searchEntries($searchquery)
    {
        $query = "SELECT * FROM games WHERE hometeam LIKE :searchQuery OR awayteam LIKE :searchQuery OR gameleague LIKE :searchQuery OR gameleagueseason LIKE :searchQuery OR venue LIKE :searchQuery OR stage LIKE :searchQuery OR status LIKE :searchQuery OR gamedate LIKE :searchQuery OR gametime LIKE :searchQuery OR details LIKE :searchQuery ORDER BY STR_TO_DATE(gamedate, '%d-%m-%Y') ASC, cast(gametime as TIME) ASC";
        $result = $this->db->query($query, ["searchQuery" => '%' . $searchquery . '%']); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function EntriesByLatest($resultNum,$mydatelists)
    {
        $query = "SELECT * FROM games WHERE status='pending' AND gamedate IN ('" . implode("','", $mydatelists) . "') ORDER BY STR_TO_DATE(gamedate, '%d-%m-%Y') ASC, cast(gametime as TIME) ASC LIMIT $resultNum";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function singleEntry($gameid)
    {
        $query = "SELECT * FROM games WHERE id=:gameid";
        $result = $this->db->query($query, ["gameid" => $gameid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function singleEntryByGameID($gameid)
    {
        $query = "SELECT * FROM games WHERE gameid=:gameid";
        $result = $this->db->query($query, ["gameid" => $gameid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function updateEntry($gameid, $data, $tableLabel)
    {
        $query = "UPDATE games SET $tableLabel=:tableLabelValue WHERE id=:gameid";
        $result = $this->db->query($query, ["tableLabelValue" => $data,"gameid" => $gameid]); 
        return $result->numRows();
    }

    public function checkEntryExist($matchid)
    {
        $this->db = $this->getDi()->getShared('db');
        $query = "SELECT * FROM games WHERE gameid=:matchid";
        $result = $this->db->query($query, ["matchid" => $matchid]); 
        return $result->numRows();
    }

    public function sumEntries()
    {
        $query = "SELECT * FROM games";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }

    public function sumEntriesByPlayed()
    {
        $query = "SELECT * FROM games WHERE status='played'";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }

    public function sumEntriesByPending()
    {
        $query = "SELECT * FROM games WHERE status='pending'";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }

    public function sumEntriesByType($myquery)
    {
        $query = "SELECT * FROM games WHERE type=:myquery";
        $result = $this->db->query($query, ["myquery" => $myquery]); 
        return $result->numRows();
    }

    public function sumEntriesByLeague($myquery)
    {
        $query = "SELECT * FROM games WHERE gameleagueid=:myquery";
        $result = $this->db->query($query, ["myquery" => $myquery]); 
        return $result->numRows();
    }

    public function sumEntriesByLeagueSeason($myquery,$myquerytwo)
    {
        $query = "SELECT * FROM games WHERE gameleagueid=:myquery AND gameleagueseason=:myquerytwo";
        $result = $this->db->query($query, ["myquery" => $myquery,"myquerytwo" => $myquerytwo]); 
        return $result->numRows();
    }

    public function EntriesCOUNT($monthquery)
    {
        $query = "SELECT * FROM games WHERE gamedate like '%$monthquery' GROUP BY id";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }
}
?>