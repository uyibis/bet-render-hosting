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

class Faqs extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addNewEntry($data)
    {
        $query = "INSERT INTO faqs (authorid, author, question, answer, tags, categoryid, category, subcategoryid, subcategory, date) VALUES (:authorid, :author, :question, :answer, :tags, :categoryid, :category, :subcategoryid, :subcategory, :date)";
        $result = $this->db->query($query, [
                        "authorid" => $data['authorid'],
                        "author" => $data['author'],
                        "question" => $data['question'],
                        "answer" => $data['answer'],
                        "tags" => $data['tags'],
                        "categoryid" => $data['categoryid'],
                        "category" => $data['category'],
                        "subcategoryid" => $data['subcategoryid'],
                        "subcategory" => $data['subcategory'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function removefaq($faqid)
    {
        $query = "DELETE FROM faqs WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $faqid
                    ]
                  );
        return $result->numRows();
    }

    public function getFAQItems()
    {
        $query = "SELECT * FROM faqs";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function getFAQContentByCategory($categoryid)
    {
        $query = "SELECT * FROM faqs WHERE categoryid=:categoryid";
        $result = $this->db->query($query, ["categoryid" => $categoryid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

}
?>