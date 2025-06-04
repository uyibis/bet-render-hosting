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

class Admin extends \Phalcon\Mvc\Model {

    public $db; 
    public $userid; 
    public $username; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function authAccount($userid, $userlogintoken)
    {
        $query = "SELECT * FROM admin WHERE id=:userid AND logintoken=:logintoken";
        $result = $this->db->query($query, [
                        "userid" => $userid,
                        "logintoken" => $userlogintoken
                    ]
                  ); 
        return $result->numRows();
    }

    public function checkAdminExist($data)
    {
        $query = "SELECT * FROM admin WHERE email=:email OR phone=:phone OR username=:username";
        $result = $this->db->query($query, [
                        "username" => $data['username'],
                        "email" => $data['email'],
                        "phone" => $data['phone']
                    ]
                  ); 
        return $result->numRows();
    }

    public function checkAdminDataExist($userid)
    {
        $query = "SELECT * FROM admin WHERE id=:userid OR username=:userid OR email=:userid OR phone=:userid";
        $result = $this->db->query($query, [
                        "userid" => $userid
                    ]
                  ); 
        return $result->numRows();
    }
    
    public function addAdmin($data)
    {
        $query = "INSERT INTO admin (username, firstname, lastname, email, phone, photo, password, role, date) VALUES (:username, :firstname, :lastname, :email, :phone, :photo, :password, :role, :date)";
        $result = $this->db->query($query, [
                        "username" => $data['username'],
                        "firstname" => $data['firstname'],
                        "lastname" => $data['lastname'],
                        "email" => $data['email'],
                        "phone" => $data['phone'],
                        "photo" => $data['photo'],
                        "password" => $data['passwordhash'],
                        "role" => $data['role'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeAdmin($userid)
    {
        $query = "DELETE FROM admin WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $userid
                    ]
                  );
        return $result->numRows();
    }

    public function allAdmin()
    {
        $query = "SELECT * FROM admin";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function searchAdmin($searchquery)
    {
        $query = "SELECT * FROM admin WHERE id=:sq OR username=:sq OR email=:sq OR phone=:sq OR address=:sq OR city=:sq OR state=:sq OR country=:sq";
        $result = $this->db->query($query, [
                        "sq" => $searchquery
                    ]
                  ); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function singleAdmin($userid)
    {
        $query = "SELECT * FROM admin WHERE id=:userid OR username=:userid OR email=:userid";
        $result = $this->db->query($query, [
                      "userid" => $userid
                    ]
                  ); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function updateAdminLoginToken($userid, $logintoken, $newdate)
    {
        $query = "UPDATE admin SET lastlogindate=:lastlogindate, logintoken=:logintoken WHERE email=:userid";
        $result = $this->db->query($query, [
                      "lastlogindate" => $newdate,
                      "logintoken" => $logintoken,
                      "userid" => $userid
                    ]
                  ); 
        return $result->numRows();
    }

    public function resetAdminPassword($useremail, $newpasswordhash)
    {
        $query = "UPDATE admin SET password=:password WHERE email=:useremail";
        $result = $this->db->query($query, [
                      "password" => $newpasswordhash,
                      "useremail" => $useremail
                    ]
                  ); 
        return $result->numRows();
    }

    public function updateAdminPasswordResetCode($useremail, $passwordresetcode, $newdate)
    {
        $query = "UPDATE admin SET passwordremindercode=:passwordremindercode, passwordreminderdate=:passwordreminderdate WHERE email=:useremail";
        $result = $this->db->query($query, [
                      "passwordremindercode" => $passwordresetcode,
                      "passwordreminderdate" => $newdate,
                      "useremail" => $useremail
                    ]
                  ); 
        return $result->numRows();
    }

    public function verifyAdminAccount($userid, $verificationcode)
    {
        $query = "UPDATE admin SET verificationcode='', verified='yes' WHERE (id=:userid OR username=:userid OR email=:userid) AND verificationcode=:verificationcode";
        $result = $this->db->query($query, [
                      "verificationcode" => $verificationcode,
                      "userid" => $userid
                    ]
                  ); 
        return $result->numRows();
    }

    public function updateAdminAccount($userid, $userdata, $tableLabel)
    {
        $query = "UPDATE admin SET $tableLabel=:tableLabelValue WHERE id=:userid";
        $result = $this->db->query($query, [
                      "tableLabelValue" => $userdata,
                      "userid" => $userid
                    ]
                  ); 
        return $result->numRows();
    }

    public function getUserID($userId)
    {
        $query = "SELECT id FROM admin WHERE username=:userId";
        $result = $this->db->query($query, ["userId" => $userId]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->id;
        return $data;
    }

    public function getUserName($userId)
    {
        $query = "SELECT username FROM admin WHERE id=:userId";
        $result = $this->db->query($query, ["userId" => $userId]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->username;
        return $data;
    }

    public function getUserEmail($userId)
    {
        $query = "SELECT email FROM admin WHERE id=:userId";
        $result = $this->db->query($query, ["userId" => $userId]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->email;
        return $data;
    }

    public function getUserPhone($userId)
    {
        $query = "SELECT phone FROM admin WHERE id=:userId";
        $result = $this->db->query($query, ["userId" => $userId]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->phone;
        return $data;
    }

    public function getUserPhoto($userId)
    {
        $query = "SELECT photo FROM admin WHERE id=:userId";
        $result = $this->db->query($query, ["userId" => $userId]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->photo;
        return $data;
    }

    public function sumAdmins()
    {
        $query = "SELECT * FROM admin";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }

}
?>