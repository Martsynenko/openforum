<?php

class modelModerationTopics extends Model{

    /* ADMIN INDEX */

    public function getAllTopics(){
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_users`.`firstname`, `db_users`.`lastname`, `db_moderation_topics`.* 
                FROM `db_moderation_topics`
                JOIN `db_category`
                ON(`db_category`.`id` = `db_moderation_topics`.`cat_id`)
                JOIN `db_rank`
                ON(`db_rank`.`id` = `db_moderation_topics`.`rank_id`)
                JOIN `db_users`
                ON(`db_users`.`id` = `db_moderation_topics`.`user_id`)
                ORDER BY `date` DESC";
        return $this->db->query($sql);
    }

    /* ADMIN PUBLIC */

    public function getUserID($topic_id){
        $sql = "SELECT `user_id` FROM `db_moderation_topics`
                WHERE `id` = '$topic_id'";
        $data = $this->db->query($sql);
        return $data[0]['user_id'];
    }

    public function getUserInfo($user_id){
        $sql = "SELECT `email`, `firstname`, `lastname` FROM `db_users`
                WHERE `id` = '$user_id'";
        $data = $this->db->query($sql);
        return $data;
    }

    public function getTopicInfo($topic_id){
        $sql = "SELECT * FROM `db_moderation_topics`
                WHERE `id` = '$topic_id'";
        $data = $this->db->query($sql);
        return $data[0];
    }

    public function insertTopic($cat_id, $rank_id, $title, $text, $date, $user_id){
        $sql = "INSERT INTO `db_topics`(`cat_id`, `rank_id`, `subject`, `text`, `date`, `user_id`)
                VALUES('$cat_id', '$rank_id', '$title', '$text', '$date', '$user_id')";
        return $this->db->query($sql);
    }

    public function deleteTopicModeration($topic_id){
        $sql = "DELETE FROM `db_moderation_topics`
                WHERE `id` = '$topic_id'";
        return $this->db->query($sql);
    }

    public function getUsersForMail($cat_id){
        $sql = "SELECT `firstname`, `email` FROM `db_subscribe`
                WHERE `cat_id` = '$cat_id'";
        return $this->db->query($sql);
    }

    public function getCategoryByID($cat_id){
        $sql = "SELECT `category` FROM `db_category`
                WHERE `id` = '$cat_id'";
        $data = $this->db->query($sql);
        return $data[0]['category'];
    }

    public function getTopicID($user_id, $title, $text){
        $sql = "SELECT `id` FROM `db_topics`
                WHERE `user_id` = '$user_id' AND `subject` = '$title' AND `text` = '$text'";
        $data = $this->db->query($sql);
        return $data[0]['id'];
    }

    /* ADMIN EDIT */

    public function updateTopic($topic_id, $title, $text){
        $sql = "UPDATE `db_moderation_topics` SET `title` = '$title', `text` = '$text'
                WHERE `id` = '$topic_id'";
        return $this->db->query($sql);
    }


}