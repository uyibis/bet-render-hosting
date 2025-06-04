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

class Categories extends \Phalcon\Mvc\Model {

    public $db; 
    public $productid; 
    public $product; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addCategory($data)
    {
        $query = "INSERT INTO categories (leagueid, type, category, image, details, author, safeurl, date) VALUES (:leagueid, :type, :category, :image, :details, :author, :safeurl, :date)";
        $result = $this->db->query($query, [
                        "leagueid" => $data['leagueid'],
                        "type" => $data['type'],
                        "category" => $data['category'],
                        "image" => $data['image'],
                        "details" => $data['details'],
                        "author" => $data['author'],
                        "safeurl" => $data['safeurl'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeCategory($categoryid)
    {
        $query = "DELETE FROM categories WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $categoryid
                    ]
                  );
        return $result->numRows();
    }

    public function allCategories()
    {
        $query = "SELECT * FROM categories";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function sportCategories($allsports)
    {
        $query = "SELECT * FROM categories WHERE type IN ('" . implode("','", $allsports) . "')";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function filterCategories($categorytype)
    {
        $query = "SELECT * FROM categories WHERE type=:categorytype";
        $result = $this->db->query($query, ["categorytype" => $categorytype]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function getCategoryIDbyLeague($category)
    {
        $query = "SELECT * FROM categories WHERE leagueid=:category";
        $result = $this->db->query($query, ["category" => $category]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->id;
        return $data;
    }

    public function checkEntryExist($league_id)
    {
        $this->db = $this->getDi()->getShared('db');
        $sqlquery = "SELECT * FROM categories WHERE leagueid=:league_id";
        $result = $this->db->query($sqlquery, ["league_id" => $league_id]); 
        return $result->numRows();
    }

    public function getCategoryID($category)
    {
        $query = "SELECT * FROM categories WHERE safeurl=:category";
        $result = $this->db->query($query, ["category" => $category]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->id;
        return $data;
    }

    public function getCategoryLeagueID($category)
    {
        $query = "SELECT * FROM categories WHERE id=:category";
        $result = $this->db->query($query, ["category" => $category]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->leagueid;
        return $data;
    }
    
    public function getCategoryName($categoryid)
    {
        $query = "SELECT category FROM categories WHERE id=:categoryid";
        $result = $this->db->query($query, ["categoryid" => $categoryid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->category;
        return $data;
    }

    public function updateCategory($categoryid, $data, $tableLabel)
    {
        $query = "UPDATE categories SET $tableLabel=:tableLabelValue WHERE id=:categoryid";
        $result = $this->db->query($query, [
                      "tableLabelValue" => $data,
                      "categoryid" => $categoryid
                    ]
                  ); 
        return $result->numRows();
    }

}
?>