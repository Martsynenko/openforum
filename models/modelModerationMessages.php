<?php

class modelModerationMessages extends Model{

    /* ADMIN INDEX */

    public function getAllMessages(){
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_users`.`firstname`, `db_users`.`lastname`, `db_moderation_messages`.* FROM `db_moderation_messages`
                JOIN `db_category`
                ON(`db_category`.`id` = `db_moderation_messages`.`cat_id`)
                JOIN `db_rank`
                ON(`db_rank`.`id` = `db_moderation_messages`.`rank_id`)
                JOIN `db_users`
                ON(`db_users`.`id` = `db_moderation_messages`.`user_id`)
                ORDER BY `date` DESC";
        return $this->db->query($sql);
    }

    public function getAllUsersName(){
        $sql = "SELECT `id`, `firstname`, `lastname` FROM `db_users`
                WHERE `email` != ''";
        return $this->db->query($sql);
    }

    /* ADMIN PUBLIC */

    public function getUserID($message_id){
        $sql = "SELECT `user_id` FROM `db_moderation_messages`
                WHERE `id` = '$message_id'";
        $data = $this->db->query($sql);
        return $data[0]['user_id'];
    }

    public function getUserInfo($user_id){
        $sql = "SELECT `email`, `firstname`, `lastname` FROM `db_users`
                WHERE `id` = '$user_id'";
        $data = $this->db->query($sql);
        return $data;
    }

    public function getMessageInfo($message_id){
        $sql = "SELECT * FROM `db_moderation_messages`
                WHERE `id` = '$message_id'";
        $data = $this->db->query($sql);
        return $data[0];
    }

    public function insertMessage($user_id, $cat_id, $rank_id, $text, $date, $users){
        $sql = "INSERT INTO `db_messages`(`user_id`, `cat_id`, `rank_id`, `text`, `date`, `users`)
                VALUES('$user_id', '$cat_id', '$rank_id', '$text', '$date', '$users')";
        return $this->db->query($sql);
    }

    public function deleteMessageModeration($message_id){
        $sql = "DELETE FROM `db_moderation_messages`
                WHERE `id` = '$message_id'";
        return $this->db->query($sql);
    }

    /* ADMIN EDIT */

    public function updateMessage($message_id, $text){
        $sql = "UPDATE `db_moderation_messages` SET `text` = '$text'
                WHERE `id` = '$message_id'";
        return $this->db->query($sql);
    }

    public function getMessageText($message_id){
        $sql = "SELECT `text` FROM `db_moderation_messages`
                WHERE `id` = '$message_id'";
        $data = $this->db->query($sql);
        return $data[0]['text'];
    }


}