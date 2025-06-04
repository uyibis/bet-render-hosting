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

class Subcategories extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addSubcategory($data)
    {
        $query = "INSERT INTO subcategories (type, leagueid, categoryid, subcategory, image, details, author, safeurl, date) VALUES (:type, :leagueid, :category, :subcategory, :image, :details, :author, :safeurl, :date)";
        $result = $this->db->query($query, [
                        "type" => $data['type'],
                        "leagueid" => $data['leagueid'],
                        "category" => $data['category'],
                        "subcategory" => $data['subcategory'],
                        "image" => $data['image'],
                        "details" => $data['details'],
                        "author" => $data['author'],
                        "safeurl" => $data['safeurl'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeSubcategory($subcategoryid)
    {
        $query = "DELETE FROM subcategories WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $subcategoryid
                    ]
                  );
        return $result->numRows();
    }

    public function allSubcategories()
    {
        $query = "SELECT * FROM subcategories";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function filterSubcategories($categoryid)
    {
        $query = "SELECT * FROM subcategories WHERE categoryid=:categoryid";
        $result = $this->db->query($query, ["categoryid" => $categoryid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function checkEntryExist($categoryid)
    {
        $this->db = $this->getDi()->getShared('db');
        $query = "SELECT * FROM subcategories WHERE categoryid=:categoryid ORDER BY id DESC";
        $result = $this->db->query($query, ["categoryid" => $categoryid]); 
        return $result->numRows();
    }

    public function getLatestSubCategoryID($leagueid)
    {
        $query = "SELECT * FROM subcategories WHERE leagueid=:leagueid";
        $result = $this->db->query($query, ["leagueid" => $leagueid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        if($row->subcategory){
            $data=$row->subcategory;
        } else {
            $data="";
        }
        return $data;
    }

    public function getSubcategoryID($subcategory)
    {
        $query = "SELECT * FROM subcategories WHERE safeurl=:subcategory";
        $result = $this->db->query($query, ["subcategory" => $subcategory]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->id;
        return $data;
    }

    public function getSubcategoryName($subcategoryid)
    {
        $query = "SELECT subcategory FROM subcategories WHERE id=:subcategoryid";
        $result = $this->db->query($query, ["subcategoryid" => $subcategoryid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        if($row->subcategory){
            $data=$row->subcategory;
        } else {
            $data="";
        }
        return $data;
    }

    public function updateSubCategory($subcategoryid, $data, $tableLabel)
    {
        $query = "UPDATE subcategories SET $tableLabel=:tableLabelValue WHERE id=:subcategoryid";
        $result = $this->db->query($query, [
                      "tableLabelValue" => $data,
                      "subcategoryid" => $subcategoryid
                    ]
                  ); 
        return $result->numRows();
    }

}
?>