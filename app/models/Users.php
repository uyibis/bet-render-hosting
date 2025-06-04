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

class Users extends \Phalcon\Mvc\Model {

    public $db; 
    public $userid; 
    public $username; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function authAccount($userid, $userlogintoken)
    {
        $query = "SELECT * FROM users WHERE id=:userid AND logintoken=:logintoken";
        $result = $this->db->query($query, [
                        "userid" => $userid,
                        "logintoken" => $userlogintoken
                    ]
                  ); 
        return $result->numRows();
    }

    public function verifyUserQuickLoginPassCode($userid,$passcode,$todaydate)
    {
        $query = "SELECT * FROM users WHERE (id=:userid OR username=:userid OR email=:userid OR phone=:userid) AND passcode=:passcode AND passcodedate=:todaydate";
        $result = $this->db->query($query, ["userid" => $userid, "passcode" => $passcode, "todaydate" => $todaydate]); 
        return $result->numRows();
    }

    public function verifyUserPasswordResetCode($userid,$resetcode,$todaydate)
    {
        $query = "SELECT * FROM users WHERE (id=:userid OR username=:userid OR email=:userid) AND passwordremindercode=:resetcode AND passwordreminderdate=:todaydate";
        $result = $this->db->query($query, ["userid" => $userid, "resetcode" => $resetcode, "todaydate" => $todaydate]); 
        return $result->numRows();
    }

    public function checkUserExist($data)
    {
        $query = "SELECT * FROM users WHERE email=:email OR phone=:phone OR username=:username";
        $result = $this->db->query($query, [
                        "username" => $data['username'],
                        "email" => $data['email'],
                        "phone" => $data['phone']
                    ]
                  ); 
        return $result->numRows();
    }

    public function checkUserDataExist($userid)
    {
        $query = "SELECT * FROM users WHERE id=:userid OR username=:userid OR email=:userid OR phone=:userid";
        $result = $this->db->query($query, [
                        "userid" => $userid
                    ]
                  ); 
        return $result->numRows();
    }

    public function checkUserUsernameExist($userdata)
    {
        $query = "SELECT * FROM users WHERE username=:user";
        $result = $this->db->query($query, [
                        "user" => $userdata
                    ]
                  ); 
        return $result->numRows();
    }

    public function checkUserEmailExist($userdata)
    {
        $query = "SELECT * FROM users WHERE email=:user";
        $result = $this->db->query($query, [
                        "user" => $userdata
                    ]
                  ); 
        return $result->numRows();
    }

    public function checkUserPhoneExist($userdata)
    {
        $query = "SELECT * FROM users WHERE phone=:user";
        $result = $this->db->query($query, [
                        "user" => $userdata
                    ]
                  ); 
        return $result->numRows();
    }

    public function checkUserEmailVerified($userid)
    {
        $query = "SELECT * FROM users WHERE (username=:userid OR email=:userid OR phone=:userid) AND verified='yes'";
        $result = $this->db->query($query, [
                        "userid" => $userid
                    ]
                  ); 
        return $result->numRows();
    }

    public function checkUserPhoneVerified($userid)
    {
        $query = "SELECT * FROM users WHERE (username=:userid OR email=:userid OR phone=:userid) AND verified='yes'";
        $result = $this->db->query($query, [
                        "userid" => $userid
                    ]
                  ); 
        return $result->numRows();
    }
    
    public function addUser($data)
    {
        $query = "INSERT INTO users (username, firstname, lastname, email, phone, password, address, city, state, country, postalzipcode, photo, referrercode, referralcode, verified, verificationcode, type, date) VALUES (:username, :firstname, :lastname, :email, :phone, :password, :address, :city, :state, :country, :postalzipcode, :photo, :referrercode, :referralcode, 'no', :verificationcode, :type, :date)";
        $result = $this->db->query($query, [
                        "username" => $data['username'],
                        "firstname" => $data['firstname'],
                        "lastname" => $data['lastname'],
                        "email" => $data['email'],
                        "phone" => $data['phone'],
                        "password" => $data['passwordhash'],
                        "address" => $data['address'],
                        "city" => $data['city'],
                        "state" => $data['state'],
                        "country" => $data['country'],
                        "postalzipcode" => $data['postalzipcode'],
                        "photo" => $data['photo'],
                        "referrercode" => $data['referrercode'],
                        "referralcode" => $data['referralcode'],
                        "verificationcode" => $data['verificationcode'],
                        "type" => $data['type'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeUser($userid)
    {
        $query = "DELETE FROM users WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $userid
                    ]
                  );
        return $result->numRows();
    }

    public function allUsers()
    {
        $query = "SELECT * FROM users";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allTopUsers()
    {
        $query = "SELECT * FROM users ORDER BY ranking DESC LIMIT 20";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function allUsersNearby($arealatitude,$arealongitude)
    {
        $query = "SELECT * FROM users";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function searchUser($searchquery)
    {
        $query = "SELECT * FROM users WHERE id=:sq OR username=:sq OR email=:sq OR phone=:sq OR address=:sq OR city=:sq OR state=:sq OR country=:sq";
        $result = $this->db->query($query, [
                        "sq" => $searchquery
                    ]
                  ); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function singleUser($userid)
    {
        $query = "SELECT * FROM users WHERE id=:userid OR username=:userid OR email=:userid OR phone=:userid";
        $result = $this->db->query($query, [
                      "userid" => $userid
                    ]
                  ); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function singleUserByEmail($userid)
    {
        $query = "SELECT * FROM users WHERE email=:userid";
        $result = $this->db->query($query, ["userid" => $userid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function updateUserLoginToken($userid, $logintoken, $newdate)
    {
        $query = "UPDATE users SET lastlogindate=:lastlogindate, logintoken=:logintoken WHERE email=:userid";
        $result = $this->db->query($query, [
                      "lastlogindate" => $newdate,
                      "logintoken" => $logintoken,
                      "userid" => $userid
                    ]
                  ); 
        return $result->numRows();
    }

    public function resetUserPassword($useremail, $newpasswordhash)
    {
        $query = "UPDATE users SET password=:password,passwordremindercode='',passwordreminderdate='' WHERE email=:useremail";
        $result = $this->db->query($query, [
                      "password" => $newpasswordhash,
                      "useremail" => $useremail
                    ]
                  ); 
        return $result->numRows();
    }

    public function updateUserPassCode($useremail, $passcode, $newdate)
    {
        $query = "UPDATE users SET passcode=:passcode, passcodedate=:passcodedate WHERE email=:useremail";
        $result = $this->db->query($query, [
                      "passcode" => $passcode,
                      "passcodedate" => $newdate,
                      "useremail" => $useremail
                    ]
                  ); 
        return $result->numRows();
    }

    public function resetUserLoginCode($useremail)
    {
        $query = "UPDATE users SET passcode='null', passcodedate='' WHERE email=:useremail";
        $result = $this->db->query($query, [
                      "useremail" => $useremail
                    ]
                  ); 
        return $result->numRows();
    }

    public function updateUserPasswordResetCode($useremail, $passwordresetcode, $newdate)
    {
        $query = "UPDATE users SET passwordremindercode=:passwordremindercode, passwordreminderdate=:passwordreminderdate WHERE email=:useremail";
        $result = $this->db->query($query, [
                      "passwordremindercode" => $passwordresetcode,
                      "passwordreminderdate" => $newdate,
                      "useremail" => $useremail
                    ]
                  ); 
        return $result->numRows();
    }

    public function verifyUserAccount($userid, $verificationcode)
    {
        $query = "UPDATE users SET verificationcode='', verified='yes' WHERE (id=:userid OR username=:userid OR email=:userid) AND verificationcode=:verificationcode";
        $result = $this->db->query($query, [
                      "verificationcode" => $verificationcode,
                      "userid" => $userid
                    ]
                  ); 
        return $result->numRows();
    }

    public function updateUserAccount($userid, $userdata, $tableLabel)
    {
        $query = "UPDATE users SET $tableLabel=:tableLabelValue WHERE id=:userid";
        $result = $this->db->query($query, [
                      "tableLabelValue" => $userdata,
                      "userid" => $userid
                    ]
                  ); 
        return $result->numRows();
    }

    public function sumReferredUsers($referralcode)
    {
        $query = "SELECT * FROM users WHERE referrercode=:referralcode";
        $result = $this->db->query($query, ["referralcode" => $referralcode]); 
        return $result->numRows();
    }

    public function referredUsers($referralcode)
    {
        $query = "SELECT * FROM users WHERE referrercode=:referralcode";
        $result = $this->db->query($query, ["referralcode" => $referralcode]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function findUserReferrer($userid)
    {
        $query = "SELECT referrercode FROM users WHERE id=:userid OR username=:userid OR email=:userid";
        $result = $this->db->query($query, ["userid" => $userid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->referrercode;
        return $data;
    }

    public function findUserReferralCode($userid)
    {
        $query = "SELECT referralcode FROM users WHERE id=:userid OR username=:userid OR email=:userid";
        $result = $this->db->query($query, ["userid" => $userid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->referralcode;
        return $data;
    }

    public function sumUsers()
    {
        $query = "SELECT * FROM users";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }

    public function usersSignupCOUNT($monthquery)
    {
        $query = "SELECT * FROM users WHERE date like '%$monthquery' GROUP BY id";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }

    public function usersResultStatisticsCOUNT($querystatus)
    {
        $query = "SELECT * FROM users WHERE riskassessmentstatus=:querystatus";
        $result = $this->db->query($query, ["querystatus" => $querystatus]); 
        return $result->numRows();
    }

    public function usersActionStatisticsCOUNT($querystatus)
    {
        $query = "SELECT * FROM users WHERE riskassessmentlevel=:querystatus";
        $result = $this->db->query($query, ["querystatus" => $querystatus]); 
        return $result->numRows();
    }

    public function usersCOUNTByStatus($querystatus)
    {
        $query = "SELECT * FROM users WHERE riskassessmentstatus=:querystatus";
        $result = $this->db->query($query, ["querystatus" => $querystatus]); 
        return $result->numRows();
    }

    public function getUserID($userId)
    {
        $query = "SELECT id FROM users WHERE username=:userId";
        $result = $this->db->query($query, ["userId" => $userId]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->id;
        return $data;
    }

    public function getUserName($userId)
    {
        $query = "SELECT username FROM users WHERE id=:userId";
        $result = $this->db->query($query, ["userId" => $userId]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->username;
        return $data;
    }

    public function getUserEmail($userId)
    {
        $query = "SELECT email FROM users WHERE id=:userId";
        $result = $this->db->query($query, ["userId" => $userId]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->email;
        return $data;
    }

    public function getUserPhone($userId)
    {
        $query = "SELECT phone FROM users WHERE id=:userId";
        $result = $this->db->query($query, ["userId" => $userId]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->phone;
        return $data;
    }

    public function getUserPhoto($userId)
    {
        $query = "SELECT photo FROM users WHERE id=:userId";
        $result = $this->db->query($query, ["userId" => $userId]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        $data=$row->photo;
        return $data;
    }

}
?>