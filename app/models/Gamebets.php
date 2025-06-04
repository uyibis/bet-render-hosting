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

class Gamebets extends \Phalcon\Mvc\Model {

    public $db; 
    public $productid; 
    public $product; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addEntry($data)
    {
        $query = "INSERT INTO gamebets (gameid, hometeambetterid, hometeambetter, hometeambetterbet, hometeambetterscore, awayteambetterid, awayteambetter, awayteambetterbet, awayteambetterscore, betamount, finalbetamount, status, date) VALUES (:gameid, :hometeambetterid, :hometeambetter, :hometeambetterbet, :hometeambetterscore, :awayteambetterid, :awayteambetter, :awayteambetterbet, :awayteambetterscore, :betamount, :finalbetamount, :status, :date)";
        $result = $this->db->query($query, [
                        "gameid" => $data['gameid'],
                        "hometeambetterid" => $data['hometeambetterid'],
                        "hometeambetter" => $data['hometeambetter'],
                        "hometeambetterbet" => $data['hometeambetterbet'],
                        "hometeambetterscore" => $data['hometeambetterscore'],
                        "awayteambetterid" => $data['awayteambetterid'],
                        "awayteambetter" => $data['awayteambetter'],
                        "awayteambetterbet" => $data['awayteambetterbet'],
                        "awayteambetterscore" => $data['awayteambetterscore'],
                        "betamount" => $data['betamount'],
                        "finalbetamount" => $data['betamount'],
                        "status" => $data['status'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeEntry($productid)
    {
        $query = "DELETE FROM gamebets WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $productid
                    ]
                  );
        return $result->numRows();
    }

    public function allEntries()
    {
        $query = "SELECT * FROM gamebets ORDER BY id DESC";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allFeaturedEntries($limit)
    {
        $query = "SELECT * FROM gamebets WHERE status='open' ORDER BY id ASC LIMIT $limit";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesByGame($gameid)
    {
        $query = "SELECT * FROM gamebets WHERE gameid=:gameid ORDER BY id DESC";
        $result = $this->db->query($query, ["gameid" => $gameid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allUserEntries($userid,$status)
    {
        $query = "SELECT * FROM gamebets WHERE (hometeambetterid=:userid OR awayteambetterid=:userid) AND status=:status ORDER BY id DESC";
        $result = $this->db->query($query, ["userid" => $userid, "status" => $status]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allUserOpenEntries($userid)
    {
        $query = "SELECT * FROM gamebets WHERE (hometeambetterid=:userid OR awayteambetterid=:userid) AND (status='open' OR status='closed') ORDER BY id DESC";
        $result = $this->db->query($query, ["userid" => $userid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allUserCompletedEntries($userid)
    {
        $query = "SELECT * FROM gamebets WHERE (hometeambetterid=:userid OR awayteambetterid=:userid) AND status='completed' ORDER BY id DESC";
        $result = $this->db->query($query, ["userid" => $userid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allEntriesByUser($userid)
    {
        $query = "SELECT * FROM gamebets WHERE hometeambetterid=:userid OR awayteambetterid=:userid ORDER BY id DESC";
        $result = $this->db->query($query, ["userid" => $userid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function filterEntries($allgames)
    {
        $query = "SELECT * FROM gamebets WHERE gameid IN ('" . implode("','", $allgames) . "') ORDER BY id DESC";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function updateEntry($betid, $data, $tableLabel)
    {
        $query = "UPDATE gamebets SET $tableLabel=:tableLabelValue WHERE id=:betid";
        $result = $this->db->query($query, ["tableLabelValue" => $data,"betid" => $betid]); 
        return $result->numRows();
    }

    public function singleEntry($betid)
    {
        $query = "SELECT * FROM gamebets WHERE id=:betid";
        $result = $this->db->query($query, ["betid" => $betid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function allBetsCount()
    {
        $query = "SELECT * FROM gamebets";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }

    public function sumBetsByUser($userid)
    {
        $query = "SELECT * FROM gamebets WHERE hometeambetterid=:userid OR awayteambetterid=:userid";
        $result = $this->db->query($query, ["userid" => $userid]); 
        return $result->numRows();
    }

    public function sumBetWinsByUser($userid)
    {
        $query = "SELECT * FROM gamebets WHERE (hometeambetterid=:userid AND hometeambetterbetstatus='win') OR (awayteambetterid=:userid  AND awayteambetterbetstatus='win')";
        $result = $this->db->query($query, ["userid" => $userid]); 
        return $result->numRows();
    }

    public function sumBetLossByUser($userid)
    {
        $query = "SELECT * FROM gamebets WHERE (hometeambetterid=:userid AND hometeambetterbetstatus='loss') OR (awayteambetterid=:userid  AND awayteambetterbetstatus='loss')";
        $result = $this->db->query($query, ["userid" => $userid]); 
        return $result->numRows();
    }

    public function sumBetDrawsByUser($userid)
    {
        $query = "SELECT * FROM gamebets WHERE (hometeambetterid=:userid AND hometeambetterbetstatus='draw') OR (awayteambetterid=:userid  AND awayteambetterbetstatus='draw')";
        $result = $this->db->query($query, ["userid" => $userid]); 
        return $result->numRows();
    }

    public function sumBetPendingByUser($userid)
    {
        $query = "SELECT * FROM gamebets WHERE (hometeambetterid=:userid AND hometeambetterbetstatus='pending') OR (awayteambetterid=:userid  AND awayteambetterbetstatus='pending')";
        $result = $this->db->query($query, ["userid" => $userid]); 
        return $result->numRows();
    }

    public function sumBetPendingAmountByUser($userid)
    {
        $query = "SELECT COALESCE(SUM(finalbetamount), 0) AS totalsum FROM gamebets WHERE (hometeambetterid=:userid AND hometeambetterbetstatus='pending') OR (awayteambetterid=:userid  AND awayteambetterbetstatus='pending')";
        $result = $this->db->query($query, ["userid" => $userid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function betsCOUNT($monthquery)
    {
        $query = "SELECT * FROM gamebets WHERE date like '%$monthquery' GROUP BY id";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }

    public function betsCOUNTByUser($monthquery, $userid)
    {
        $query = "SELECT * FROM gamebets WHERE (hometeambetterid=:userid OR awayteambetterid=:userid) AND date like '%$monthquery' GROUP BY id";
        $result = $this->db->query($query, ["userid" => $userid]); 
        return $result->numRows();
    }
    
    public function betsStatisticsCOUNTByUser($querystatus,$customerid)
    {
        $query = "SELECT * FROM gamebets WHERE (hometeambetterid=:userid OR awayteambetterid=:userid) AND status=:querystatus";
        $result = $this->db->query($query, ["querystatus" => $querystatus, "userid" => $customerid]); 
        return $result->numRows();
    }
    
    public function betsStatisticsCOUNT($querystatus)
    {
        $query = "SELECT * FROM gamebets WHERE status=:querystatus";
        $result = $this->db->query($query, ["querystatus" => $querystatus]); 
        return $result->numRows();
    }
}
?>