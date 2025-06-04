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

class Pages extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addNewPage($data)
    {
        $query = "INSERT INTO pages (pagename, content, pagebanner, lastupdated, author, pagecode, date) VALUES (:pagename, :content, :pagebanner, :lastupdated, :author, :pagecode, :date)";
        $result = $this->db->query($query, [
                        "pagename" => $data['title'],
                        "content" => $data['content'],
                        "pagebanner" => $data['pagebanner'],
                        "lastupdated" => $data['lastupdated'],
                        "author" => $data['author'],
                        "pagecode" => $data['pagecode'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }
    
    public function savePageContent($pageid, $data, $tableLabel)
    {
        $query = "UPDATE pages SET $tableLabel=:tableLabelValue WHERE id=:id";
        $result = $this->db->query($query, [
                        "tableLabelValue" => $data,
                        "id" => $pageid
                    ]
                  ); 
        return $result->numRows();
    }

    public function removePage($pageid)
    {
        $query = "DELETE FROM pages WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $pageid
                    ]
                  );
        return $result->numRows();
    }

    public function getPageContent($pageid)
    {
        $query = "SELECT * FROM pages WHERE id=:pageid OR pagecode=:pageid";
        $result = $this->db->query($query, ["pageid" => $pageid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function getAllPages()
    {
        $query = "SELECT * FROM pages ORDER BY id DESC";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

}
?>