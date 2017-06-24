<?php

class AuthUser extends Model{

    public function checkBanUser($user_id){
        $sql = "SELECT `id` FROM `db_users_block`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        if(!empty($data)){
            return true;
        } else {
            return false;
        }
    }

    public function checkIssetUser($email, $password){
        $sql = "SELECT `id` FROM `db_users` 
                WHERE `email` = '$email' AND `password` = '$password'";
        $data = $this->db->query($sql);
        if(!empty($data[0]['id'])){
            return true;
        } else {
            return false;
        }
    }

    public function checkIssetEmail($email){
        $sql = "SELECT `id` FROM `db_users`
                WHERE `email` = '$email'";
        $data = $this->db->query($sql);
        if(!empty($data[0]['id'])){
            return true;
        } else {
            return false;
        }
    }

    function checkValidPassword($password){
        if(preg_match('/^(?=.*\d)(?=.*[a-zа-я])(?=.*[A-ZА-Я])(?!.*\s).*$/', $password)){
            return true;
        } else {
            return false;
        }
    }

    function updatePassword($email, $password){
        $sql = "UPDATE `db_users` SET `password` = '$password' 
                WHERE `email` = '$email'";
        return $this->db->query($sql);
    }

    function getUserData($email, $password){
        $sql = "SELECT * FROM `db_users`
                WHERE `email` = '$email' AND `password` = '$password'";
        $data = $this->db->query($sql);
        return $data[0];
    }

    public function countTopics($user_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_topics`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        $count = $data[0]['count'];
        return $count;
    }

    public function countMessages($user_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_messages`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function countSpecialists($user_id){
        $sql = "SELECT `specialists` FROM `db_user_specialists`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        if(!empty($data)){
            $str = $data[0]['specialists'];
            $array = explode(', ', $str);
            $count = count($array);
            echo $count;
            return $count;
        } else return false;
    }

    public function getUserNameByEmail($email){
        $sql = "SELECT `firstname`, `lastname` FROM `db_users`
                WHERE `email` = '$email'";
        $data = $this->db->query($sql);
        return $data[0];
    }

    public function updateCountVisits($user_id){
        $sql = "SELECT `id` FROM `db_users_visits`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        if(empty($data)){
            $sql = "INSERT INTO `db_users_visits` (`user_id`, `visits`)
                    VALUES ('$user_id', '1')";
            return $this->db->query($sql);
        } else {
            $sql = "UPDATE `db_users_visits` SET `visits` = `visits` + 1
                    WHERE `user_id` = '$user_id'";
            return $this->db->query($sql);
        }
    }

    public function updateTimeVisit($user_id, $time){
        $sql = "UPDATE `db_users_visits` SET `time` = `time` + '$time'
                WHERE `user_id` = '$user_id'";
        return $this->db->query($sql);
    }

}