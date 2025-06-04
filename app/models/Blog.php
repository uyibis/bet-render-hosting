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

class Blog extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addNewBlog($data)
    {
        $query = "INSERT INTO blog (authorid, author, authorphoto, authordetails, safeurl, title, photo, type, media, mediatype, medialink, categoryid, category, subcategoryid, subcategory, views, comments, details, date) VALUES (:authorid, :author, :authorphoto, :authordetails, :safeurl, :title, :photo, :type, :media, :mediatype, :medialink, :categoryid, :category, :subcategoryid, :subcategory, :views, :comments, :details, :date)";
        $result = $this->db->query($query, [
                        "authorid" => $data['authorid'],
                        "author" => $data['author'],
                        "authorphoto" => $data['authorphoto'],
                        "authordetails" => $data['authordetails'],
                        "safeurl" => $data['safeurl'],
                        "title" => $data['title'],
                        "photo" => $data['photo'],
                        "type" => $data['type'],
                        "media" => $data['media'],
                        "mediatype" => $data['mediatype'],
                        "medialink" => $data['medialink'],
                        "categoryid" => $data['categoryid'],
                        "category" => $data['category'],
                        "subcategoryid" => $data['subcategoryid'],
                        "subcategory" => $data['subcategory'],
                        "views" => $data['views'],
                        "comments" => $data['comments'],
                        "details" => $data['details'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }
    
    public function updateBlogPostCommentCount($blogpostid)
    {
        $query = "UPDATE blog SET comments=comments+1 WHERE id=:id OR safeurl=:id";
        $result = $this->db->query($query, ["id" => $blogpostid]); 
        return $result->numRows();
    }

    public function reduceBlogPostCommentCount($blogpostid)
    {
        $query = "UPDATE blog SET comments=comments-1 WHERE id=:id OR safeurl=:id";
        $result = $this->db->query($query, ["id" => $blogpostid]); 
        return $result->numRows();
    }

    public function updateBlogPostViewCount($blogpostid)
    {
        $query = "UPDATE blog SET views=views+1 WHERE id=:id OR safeurl=:id";
        $result = $this->db->query($query, ["id" => $blogpostid]); 
        return $result->numRows();
    }

    public function removeBlog($blogid)
    {
        $query = "DELETE FROM blog WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $blogid
                    ]
                  );
        return $result->numRows();
    }

    public function getAllBlogPosts()
    {
        $query = "SELECT * FROM blog ORDER BY id DESC LIMIT 20";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function getBlogPosts($resultNum)
    {
        $query = "SELECT * FROM blog ORDER BY id DESC LIMIT $resultNum";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function searchBlogPosts($searchquery,$resultNum)
    {
        $query = "SELECT * FROM blog WHERE id LIKE :searchQuery OR title LIKE :searchQuery OR details LIKE :searchQuery OR category LIKE :searchQuery OR subcategory LIKE :searchQuery OR author LIKE :searchQuery OR type LIKE :searchQuery LIMIT $resultNum";
        $result = $this->db->query($query, ["searchQuery" => '%' . $searchquery . '%']); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function blogPostsByCategory($categoryid,$resultNum)
    {
        $query = "SELECT * FROM blog WHERE categoryid=:categoryid ORDER BY id DESC LIMIT $resultNum";
        $result = $this->db->query($query, ["categoryid" => $categoryid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function blogPostsBySubcategory($categoryid,$subcategoryid,$resultNum)
    {
        $query = "SELECT * FROM blog WHERE categoryid=:categoryid AND subcategoryid=:subcategoryid ORDER BY id DESC LIMIT $resultNum";
        $result = $this->db->query($query, ["categoryid" => $categoryid, "subcategoryid" => $subcategoryid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function getBlogFeaturedPosts($resultNum)
    {
        $query = "SELECT * FROM blog ORDER BY id DESC LIMIT $resultNum";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function getRandomBlogPosts($resultNum)
    {
        $query = "SELECT * FROM blog ORDER BY rand() LIMIT $resultNum";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }
    
    public function getBlogContent($blogid)
    {
        $query = "SELECT * FROM blog WHERE id=:blogid OR safeurl=:blogid";
        $result = $this->db->query($query, ["blogid" => $blogid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function updateBlog($postid, $data, $tableLabel)
    {
        $query = "UPDATE blog SET $tableLabel=:tableLabelValue WHERE id=:postid";
        $result = $this->db->query($query, [
                      "tableLabelValue" => $data,
                      "postid" => $postid
                    ]
                  ); 
        return $result->numRows();
    }

}
?>