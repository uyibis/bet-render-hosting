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

class Bannersliders extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addNewSlideBanner($data)
    {
        $query = "UPDATE bannersliders SET media=:media, type=:type, slidebuttonlink=:link, details=:details, author=:author, date=:date WHERE id=:position";
        $result = $this->db->query($query, [
                        "position" => $data['position'],
                        "media" => $data['image'],
                        "type" => $data['type'],
                        "link" => $data['link'],
                        "details" => $data['details'],
                        "author" => $data['author'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeSlideBanner($bannerid)
    {
        $query = "DELETE FROM bannersliders WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $bannerid
                    ]
                  );
        return $result->numRows();
    }

    public function getSlideBannersItems()
    {
        $query = "SELECT * FROM bannersliders";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

}
?>