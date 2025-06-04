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

class Groupchats extends \Phalcon\Mvc\Model {

    public $db; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addChatEntry($targetid,$userid,$data)
    {
        $query = "INSERT INTO groupchats SET `chatroom_id`=:chatroom_id, `user_id`=:user_id, `username`=:username, `message`=:message, `is_admin`=:is_admin, `time`=:time, `date`=:date";
        $result = $this->db->query($query, [
                        "chatroom_id" => $targetid,
                        "user_id" => $userid,
                        "username" => $data['username'],
                        "message" => $data['message'],
                        "is_admin" => $data['is_admin'],
                        "time" => $data['time'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeChatEntry($chatid)
    {
        $query = "DELETE FROM groupchats WHERE id=:id";
        $result = $this->db->query($query, [
                      "id" => $chatid
                    ]
                  );
        return $result->numRows();
    }

    public function allChats($targetid,$limitnum,$lastmessageid=0)
    {
        $query = "SELECT * FROM groupchats WHERE `chatroom_id`=:chatroom_id AND id>:lastmessageid ORDER BY id DESC LIMIT $limitnum";
        $result = $this->db->query($query, ["chatroom_id" => $targetid,"lastmessageid" => $lastmessageid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function getAllOnlineUsers($targetid,$data)
    {
        //Delete user online time watch
        $query = "DELETE FROM chatroom_users WHERE `user_id`=:userid AND `chatroom_id`=:chatroom_id";
        $result = $this->db->query($query, ["userid" => $data['userid'],"chatroom_id" => $targetid]);
        //Insert user online time watch
        $query = "INSERT INTO chatroom_users SET `username`=:username,`datetime`=:datetime,`user_id`=:userid,`chatroom_id`=:chatroom_id";
        $result = $this->db->query($query, ["userid" => $data['userid'],"username" => $data['username'],"chatroom_id" => $targetid,"datetime" => $data['howlong']]); 
        //Fetch all users online in the room
        $query = "SELECT * FROM chatroom_users WHERE `chatroom_id`=:chatroom_id AND `datetime` >= :howlong GROUP BY `user_id`";
        $result = $this->db->query($query, ["howlong" => $data['howlong'],"chatroom_id" => $targetid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }
    
}
?>