<?php

class modelUsers extends Model{

    /* ADMIN INDEX */

    public function getAllUsers(){
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_users`.* FROM `db_users`
                JOIN `db_category`
                ON(`db_category`.`id` = `db_users`.`cat_id`)
                JOIN `db_rank`
                ON(`db_rank`.`id` = `db_users`.`rank_id`)
                WHERE `email` != ''";
        return $this->db->query($sql);
    }

    public function getUserInfo($user_id){
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_users`.* FROM `db_users`
                JOIN `db_category`
                ON(`db_category`.`id` = `db_users`.`cat_id`)
                JOIN `db_rank`
                ON(`db_rank`.`id` = `db_users`.`rank_id`)
                WHERE `db_users`.`id` = '$user_id'";
        $data = $this->db->query($sql);
        return $data[0];
    }

    public function getCountUserTopics($user_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_topics`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountUserMessages($user_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_messages`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountAnswersTopics($user_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_topics_answers`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountAnswersMessages($user_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_messages_answers`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountVisists($user_id){
        $sql = "SELECT `visits` FROM `db_users_visits`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        if(empty($data)){
            return 0;
        } else {
            return $data[0]['visits'];
        }
    }

    public function getTimeVisits($user_id){
        $sql = "SELECT `time` FROM `db_users_visits`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        if(empty($data)){
            return 0;
        } else {
            return $data[0]['time'];
        }
    }

    /* ADMIN BLOCK */

    public function insertBlockUser($user_id){
        $sql = "INSERT INTO `db_users_block` (`user_id`)
                VALUES ('$user_id')";
        return $this->db->query($sql);
    }

    public function getUsersBlock(){
        $sql = "SELECT `user_id` FROM `db_users_block`";
        return $this->db->query($sql);
    }

    /* ADMIN UNBLOCK */

    public function deleteUserBlock($user_id){
        $sql = "DELETE FROM `db_users_block`
                WHERE `user_id` = '$user_id'";
        return $this->db->query($sql);
    }

    /* ADMIN DELETE */

    public function deleteUser($user_id){
        $sql = "DELETE FROM `db_users`
                WHERE `id` = '$user_id'";
        return $this->db->query($sql);
    }
}