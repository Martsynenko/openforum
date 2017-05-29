<?php

class NewTopic extends Model{

    public function getCategories(){
        $sql = 'SELECT * FROM `db_category`';
        return $this->db->query($sql);
    }

    public function getRanks($cat_id){
        $sql = "SELECT * FROM `db_rank` WHERE `cat_id` = $cat_id";
        return $this->db->query($sql);
    }

    public function insertTopic($cat_id, $rank_id, $subject, $text, $date, $user_id){
        $sql = "INSERT INTO `db_topics`(`cat_id`, `rank_id`, `subject`, `text`, `date`, `answers`, `views`, `user_id`)
                VALUES ('$cat_id', '$rank_id', '$subject', '$text', '$date', '0', '0', '$user_id')";
        return $this->db->query($sql);
    }

}