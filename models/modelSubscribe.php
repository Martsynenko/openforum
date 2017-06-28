<?php

class modelSubscribe extends Model{

    /* SUBSCRIBE */

    public function getCategories(){
        $sql = 'SELECT * FROM `db_category`
                ORDER BY `category`';
        return $this->db->query($sql);
    }

    public function insertSubscribe($name, $email, $cat_id){
        $sql = "INSERT INTO `db_subscribe`(`firstname`, `email`, `cat_id`)
                VALUES ('$name', '$email', '$cat_id')";
        return $this->db->query($sql);
    }

    /* SUBSCRIBE DELETE */

    public function deleteUserSubscribeByEmail($email){
        $sql = "DELETE FROM `db_subscribe`
                WHERE md5(`email`) = '$email'";
        return $this->db->query($sql);
    }

    /* ADMIN SUBSCRIBE */

    public function getAllUsersSubscribe(){
        $sql = "SELECT `db_category`.`category`, `db_subscribe`.* FROM `db_subscribe`
                JOIN `db_category`
                ON (`db_category`.`id` = `db_subscribe`.`cat_id`)";
        return $this->db->query($sql);
    }

    /* ADMIN DELETESUBSCRIBE */

    public function deleteUserSubscribe($user_id){
        $sql = "DELETE FROM `db_subscribe`
                WHERE `id` = '$user_id'";
        return $this->db->query($sql);
    }

    /* ADMIN SEND */

    public function getCategoryByID($cat_id){
        $sql = "SELECT `category` FROM `db_category`
                WHERE `id` = '$cat_id'";
        $data = $this->db->query($sql);
        return $data[0]['category'];
    }

    public function getUsersForMail($cat_id){
        $sql = "SELECT `firstname`, `email` FROM `db_subscribe`
                WHERE `cat_id` = '$cat_id'";
        return $this->db->query($sql);
    }

}