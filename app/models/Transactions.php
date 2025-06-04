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

class Transactions extends \Phalcon\Mvc\Model {

    public $db; 
    public $transactionid; 

    public function initialize()
    {
        $this->db = $this->getDi()->getShared('db');
    }
    
    public function addTransaction($data)
    {
        $query = "INSERT INTO transactions (orderid, type, details, amount, paymentstatus, paymentmethod, customerid, customername, customerreferrer, date) VALUES (:orderid, :type, :details, :amount, :paymentstatus, :paymentmethod, :customerid, :customername, :customerreferrer, :date)";
        $result = $this->db->query($query, [
                        "orderid" => $data['orderid'],
                        "type" => $data['type'],
                        "details" => $data['note'],
                        "amount" => $data['amount'],
                        "paymentstatus" => $data['paymentstatus'],
                        "paymentmethod" => $data['paymentmethod'],
                        "customerid" => $data['userid'],
                        "customername" => $data['username'],
                        "customerreferrer" => $data['userreferrer'],
                        "date" => $data['date']
                    ]
                  ); 
        return $result->numRows();
    }

    public function updateTransaction($transactionid, $data, $tableLabel)
    {
        $query = "UPDATE transactions SET $tableLabel=:tableLabelValue WHERE orderid=:transactionid";
        $result = $this->db->query($query, [
                      "tableLabelValue" => $data,
                      "transactionid" => $transactionid
                    ]
                  ); 
        return $result->numRows();
    }

    public function removeTransaction($transactionid)
    {
        $query = "DELETE FROM transactions WHERE orderid=:id";
        $result = $this->db->query($query, [
                      "id" => $transactionid
                    ]
                  );
        return $result->numRows();
    }

    public function allTransactions()
    {
        $query = "SELECT * FROM transactions ORDER BY id DESC";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function searchTransactions($searchquery)
    {
        $query = "SELECT * FROM transactions WHERE orderid=:sq OR id=:sq OR details=:sq OR customerid=:sq OR paymentstatus=:sq OR paymentmethod=:sq ORDER BY id DESC";
        $result = $this->db->query($query, [
                        "sq" => $searchquery
                    ]
                  ); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function transactionsByCustomer($customerid)
    {
        $query = "SELECT * FROM transactions WHERE customerid=:sq ORDER BY id DESC";
        $result = $this->db->query($query, [
                        "sq" => $customerid
                    ]
                  ); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function transactionsByReferrer($referrerid)
    {
        $query = "SELECT * FROM transactions WHERE customerreferrer=:referrerid ORDER BY id DESC";
        $result = $this->db->query($query, [
                        "referrerid" => $referrerid
                    ]
                  ); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $rows = $result->fetchAll();
        return $rows;
    }

    public function sumUserTransactions($customerid)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) AS totalsum FROM transactions WHERE customerid=:sq";
        $result = $this->db->query($query, ["sq" => $customerid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function sumUserWithdrawalTransactions($customerid)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) AS totalsum FROM transactions WHERE customerid=:sq AND type='withdrawal'";
        $result = $this->db->query($query, ["sq" => $customerid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function sumUserDepositTransactions($customerid)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) AS totalsum FROM transactions WHERE customerid=:sq AND type='deposit'";
        $result = $this->db->query($query, ["sq" => $customerid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function sumUserWinTransactions($customerid)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) AS totalsum FROM transactions WHERE customerid=:sq AND type='win'";
        $result = $this->db->query($query, ["sq" => $customerid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function sumUserLossTransactions($customerid)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) AS totalsum FROM transactions WHERE customerid=:sq AND type='loss'";
        $result = $this->db->query($query, ["sq" => $customerid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function singleTransaction($transactionid)
    {
        $query = "SELECT * FROM transactions WHERE orderid=:transactionid OR gatewayreferenceid=:transactionid";
        $result = $this->db->query($query, [
                      "transactionid" => $transactionid
                    ]
                  ); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }

    public function singleCustomerTransaction($transactionid,$userid)
    {
        $query = "SELECT * FROM transactions WHERE (orderid=:transactionid OR gatewayreferenceid=:transactionid) AND customerid=:userid";
        $result = $this->db->query($query, ["transactionid" => $transactionid,"userid" => $userid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row;
    }
    
    public function sumOrders()
    {
        $query = "SELECT * FROM transactions";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }

    public function sumSales()
    {
        $query = "SELECT COALESCE(SUM(amount), 0) AS totalsum FROM transactions";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row->totalsum;
    }
    
    public function sumWithdrawals()
    {
        $query = "SELECT * FROM transactions WHERE type='withdrawal'";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }
    
    public function sumDeposits()
    {
        $query = "SELECT * FROM transactions WHERE type='deposit'";
        $result = $this->db->query($query, []); 
        return $result->numRows();
    }

    public function transactionsCOUNTByCustomer($monthquery,$customerid)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) AS totalsum FROM transactions WHERE customerid=:customerid AND date like '%$monthquery'";
        $result = $this->db->query($query, ["customerid" => $customerid]); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row->totalsum;
    }
    
    public function transactionsPaymentStatisticsCOUNTByCustomer($querystatus,$customerid)
    {
        $query = "SELECT * FROM transactions WHERE customerid=:customerid AND paymentstatus=:querystatus";
        $result = $this->db->query($query, ["querystatus" => $querystatus, "customerid" => $customerid]); 
        return $result->numRows();
    }

    public function transactionsCOUNT($monthquery)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) AS totalsum FROM transactions WHERE date like '%$monthquery'";
        $result = $this->db->query($query, []); 
        $result->setFetchMode(Phalcon\Db\Enum::FETCH_OBJ);
        $row = $result->fetch();
        return $row->totalsum;
    }

    public function transactionsPaymentStatisticsCount($querystatus)
    {
        $query = "SELECT * FROM transactions WHERE paymentstatus=:querystatus";
        $result = $this->db->query($query, ["querystatus" => $querystatus]); 
        return $result->numRows();
    }

}
?>