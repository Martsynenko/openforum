<?php

class modelProfile extends Model{

    public function deleteUserAvatar($user_id, $default_avatar){
        $sql = "UPDATE `db_users` SET `avatar` = '$default_avatar'
                WHERE `id` = '$user_id'";
        return $this->db->query($sql);
    }

    public function updateUserAvatar($user_id, $path){
        $sql = "UPDATE `db_users` SET `avatar` = '$path'
                WHERE `id` = '$user_id'";
        return $this->db->query($sql);
    }

    public function getPreviousAvatar($user_id){
        $sql = "SELECT `avatar` FROM `db_users`
                WHERE `id` = '$user_id'";
        $data = $this->db->query($sql);
        return $data[0]['avatar'];
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

    public function getAllCategory(){
        $sql = "SELECT * FROM `db_category`
                ORDER BY `category`";
        return $this->db->query($sql);
    }

    public function updateUserCity($user_id, $city){
        $sql = "UPDATE `db_users` SET `city` = '$city'
                WHERE `id` = '$user_id'";
        return $this->db->query($sql);
    }

    /* USER EMAIL */

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

    public function updateUserEmail($user_id, $email){
        $sql = "UPDATE `db_users` SET `email` = '$email'
                WHERE `id` = '$user_id'";
        return $this->db->query($sql);
    }

    /* USER PASSWORD */

    public function checkUserPassword($user_id, $password){
        $sql = "SELECT `id` FROM `db_users`
                WHERE `id` = '$user_id' AND `password` = '$password'";
        $data = $this->db->query($sql);
        if(!empty($data)){
            return true;
        } else {
            return false;
        }
    }

    public function checkValidPassword($password){
        if(preg_match('/^(?=.*\d)(?=.*[a-zа-я])(?=.*[A-ZА-Я])(?!.*\s).*$/', $password)){
            return true;
        } else {
            return false;
        }
    }

    public function updateUserPassword($user_id, $password){
        $sql = "UPDATE `db_users` SET `password` = '$password'
                WHERE `id` = '$user_id'";
        return $this->db->query($sql);
    }

    /* USER RANK */

    public function insertUserRank($category, $user_rank){
        $sql = "INSERT INTO `db_rank`(`rank`, `cat_id`)
                VALUES ('$user_rank', '$category')";
        return $this->db->query($sql);
    }

    public function getUserRank($user_rank){
        $sql = "SELECT `id` FROM `db_rank`
                WHERE `rank` = '$user_rank'";
        $data = $this->db->query($sql);
        return $data[0]['id'];
    }

    public function checkIssetRank($rank){
        $sql = "SELECT `id` FROM `db_rank`
                WHERE `rank` = '$rank'";
        $data = $this->db->query($sql);
        return $data;
    }

    public function updateUserRank($user_id, $cat_id, $rank_id){
        $sql = "UPDATE `db_users` SET `cat_id` = '$cat_id', `rank_id` = '$rank_id'
                WHERE `id` = '$user_id'";
        return $this->db->query($sql);
    }

    /* USER DELETE */

    public function checkValidUserData($user_id, $email, $password){
        $sql = "SELECT `id` FROM `db_users`
                WHERE `email` = '$email' AND `password` = '$password' AND `id` = '$user_id'";
        $data = $this->db->query($sql);
        if(!empty($data)){
            return true;
        } else {
            return false;
        }
    }

    public function deleteIDFromUsers($user_id){
        $sql = "SELECT `user_id`, `specialists` FROM `db_user_specialists`";
        $data = $this->db->query($sql);
        for($i=0;$i<count($data);$i++){
            $str = $data[$i]['specialists'];
            $array = explode(', ', $str);
            if(in_array($user_id, $array)){
                $key = array_search($user_id, $array);
                unset($array[$key]);
            }
            $str = implode(', ', $array);
            $id = $data[$i]['user_id'];
            $sql = "UPDATE `db_user_specialists` SET `specialists` = '$str'
                    WHERE `user_id` = '$id'";
            $this->db->query($sql);
        }
    }

    public function deleteUser($user_id){
        $sql = "DELETE FROM `db_users`
                WHERE `id` = '$user_id'";
        return $this->db->query($sql);
    }
}