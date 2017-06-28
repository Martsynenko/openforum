<?php

class modelRank extends Model{

    public function getAllRank(){
        $sql = "SELECT `db_category`.`category`, `db_rank`.`id`, `db_rank`.`rank` FROM `db_rank` 
                JOIN `db_category`
                ON(`db_category`.`id` = `db_rank`.`cat_id`)
                ORDER BY `db_category`.`category`, `db_rank`.`rank`";
        return $this->db->query($sql);
    }

    /* ADMIN ADD */

    public function getAllCategory(){
        $sql = "SELECT * FROM `db_category`
                ORDER BY `category`";
        return $this->db->query($sql);
    }

    public function insertRank($title, $cat_id){
        $sql = "INSERT INTO `db_rank` (`rank`, `cat_id`)
                VALUES ('$title', '$cat_id')";
        return $this->db->query($sql);
    }

    public function checkIssetRank($rank){
        $sql = "SELECT `id` FROM `db_rank`
                WHERE `rank` = '$rank'";
        $data = $this->db->query($sql);
        if(empty($data)){
            return true;
        } else {
            return false;
        }
    }

    /* ADMIN EDIT */

    public function getCategoryByRankID($rank_id){
        $sql = "SELECT `cat_id` FROM `db_rank`
                WHERE `id` = '$rank_id'";
        $data = $this->db->query($sql);
        return $data[0]['cat_id'];
    }

    public function getRankByID($rank_id){
        $sql = "SELECT `rank` FROM `db_rank`
                WHERE `id` = '$rank_id'";
        $data = $this->db->query($sql);
        return $data[0]['rank'];
    }

    public function updateRank($rank_id, $title, $cat_id){
        $sql = "UPDATE `db_rank` SET `rank` = '$title', `cat_id` = '$cat_id'
                WHERE `id` = '$rank_id'";
        return $this->db->query($sql);
    }

    /* ADMIN DELETE */

    public function checkForeignKeyRank($rank_id){
        $sql = "SELECT `id` FROM `db_users`
                WHERE `rank_id` = '$rank_id'";
        $data = $this->db->query($sql);
        if(empty($data)){
            return true;
        } else {
            return false;
        }
    }

    public function deleteRank($rank_id){
        $sql = "DELETE FROM `db_rank`
                WHERE `id` = '$rank_id'";
        return $this->db->query($sql);
    }

}