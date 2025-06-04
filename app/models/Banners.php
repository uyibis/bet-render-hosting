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

class Banners extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addNewBanner($data)
    {
        $query = "UPDATE banners SET image=:image, link=:link, details=:details, author=:author, date=:date WHERE id=:position";
        $result = $this->db->query($query, [
                        "position" => $data['position'],
                        "image" => $data['image'],
                        "link" => $data['link'],
                        "details" => $data['details'],
                        "author" => $data['author'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeBanner($bannerid)
    {
        $query = "DELETE FROM banners WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $bannerid
                    ]
                  );
        return $result->numRows();
    }

    public function getBannersItems()
    {
        $query = "SELECT * FROM banners";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }
    
    public function getBannerContentByPost($bannerid)
    {
        $query = "SELECT * FROM gallery WHERE id=:bannerid";
        $result = $this->db->query($query, ["bannerid" => $bannerid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

}
?>