<?php

class modelSection extends Model{

    public function getAllCategory(){
        $sql = "SELECT * FROM `db_category`
                ORDER BY `category`";
        return $this->db->query($sql);
    }

    /* ADMIN ADD */

    public function insertCategory($title){
        $sql = "INSERT INTO `db_category` (`category`)
                VALUES ('$title')";
        return $this->db->query($sql);
    }

    public function checkIssetCategory($title){
        $sql = "SELECT `id` FROM `db_category`
                WHERE `category` = '$title'";
        $data = $this->db->query($sql);
        if(!empty($data)){
            return true;
        } else {
            return false;
        }
    }

    /* ADMIN EDIT */

    public function getCategoryByID($cat_id){
        $sql = "SELECT `category` FROM `db_category`
                WHERE `id` = '$cat_id'";
        $data = $this->db->query($sql);
        return $data[0]['category'];
    }

    public function updateCategory($cat_id, $title){
        $sql = "UPDATE `db_category` SET `category` = '$title'
                WHERE `id` = '$cat_id'";
        return $this->db->query($sql);
    }

    /* ADMIN DELETE */

    public function checkForeignKeyCategory($cat_id){
        $sql = "SELECT `id` FROM `db_users`
                WHERE `cat_id` = '$cat_id'";
        $data = $this->db->query($sql);
        if(empty($data)){
            return true;
        } else {
            return false;
        }
    }

    public function deleteCategory($cat_id){
        $sql = "DELETE FROM `db_category`
                WHERE `id` = '$cat_id'";
        return $this->db->query($sql);
    }
}