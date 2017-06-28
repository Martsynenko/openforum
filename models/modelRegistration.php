<?php

class modelRegistration extends Model{

    public function getCategories(){
        $sql = 'SELECT * FROM `db_category`
                ORDER BY `category`';
        return $this->db->query($sql);
    }

    public function checkValidName($name){
        if(preg_match('/^[a-zA-Zа-яА-Я]+$/u', $name)){
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

    public function insert($firstname, $lastname, $city, $email, $password, $category, $rank, $birthday, $avatar, $date_reg){
        $sql = "INSERT INTO `db_users`(`firstname`, `lastname`, `city`, `email`, `password`, `cat_id`, `rank_id`, `birthday`, `avatar`, `date_reg`)
                VALUES ('$firstname', '$lastname', '$city', '$email', '$password', '$category', '$rank', '$birthday', '$avatar', '$date_reg')";
        echo $sql;
        return $this->db->query($sql);
    }

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
}