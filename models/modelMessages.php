<?php

class modelMessages extends Model{

    /* NEW MESSAGE*/

    public function checkIssetRank($rank){
        $sql = "SELECT `id` FROM `db_rank`
                WHERE `rank` = '$rank'";
        $data = $this->db->query($sql);
        return $data;
    }

    public function getCategories(){
        $sql = 'SELECT * FROM `db_category`';
        return $this->db->query($sql);
    }

    public function getRanks($cat_id){
        $sql = "SELECT * FROM `db_rank` WHERE `cat_id` = $cat_id";
        return $this->db->query($sql);
    }

    public function getUsersForMessage($cat_id, $rank_id, $user_id){
        $sql = "SELECT `id` FROM `db_users`
                WHERE `cat_id` = '$cat_id' AND `rank_id` = '$rank_id' AND `id` != '$user_id' AND `email` != ''";
        $data = $this->db->query($sql);
        if(empty($data)) return false;
        else {
            $array_id = [];
            for($i=0;$i<count($data);$i++){
                $array_id[] = $data[$i]['id'];
            }
            $str = implode(', ', $array_id);
            return $str;
        }
    }

    public function getUsersForMessageMy($cat_id, $rank_id, $user_id){
        $sql = "SELECT `specialists` FROM `db_user_specialists`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        $str = $data[0]['specialists'];
        $sql = "SELECT `id` FROM `db_users`
                WHERE `cat_id` = '$cat_id' AND `rank_id` = '$rank_id' AND `id` IN($str)";
        $data = $this->db->query($sql);
        if(empty($data)) return false;
        else {
            $array_id = [];
            for($i=0;$i<count($data);$i++){
                $array_id[] = $data[$i]['id'];
            }
            $str = implode(', ', $array_id);
            return $str;
        }
    }

    public function insert($id, $cat_id, $rank_id, $text, $date, $users){
        $sql = "INSERT INTO `db_moderation_messages`(`user_id`, `cat_id`, `rank_id`, `text`, `date`, `users`)
                VALUES ('$id', '$cat_id', '$rank_id', '$text', '$date', '$users')";
        return $this->db->query($sql);
    }

    public function getUserName($user_id){
        $sql = "SELECT `id`, `firstname`, `lastname` FROM `db_users`
                WHERE `id` = '$user_id'";
        return $this->db->query($sql);
    }

    public function checkIssetUser($user_id){
        $sql = "SELECT `id` FROM `db_user_specialists`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        if(empty($data)) return false;
        else return true;
    }


    /* OUTBOX MESSAGE */

    public function getCountOutbox($user_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_messages`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getMessages($user_id){
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, COUNT(`message_id`) as answers, m.`id`, m.`date`, m.`text`, m.`users` FROM `db_messages` as m
                JOIN `db_category`
                ON(m.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(m.`rank_id` = `db_rank`.`id`)
                LEFT JOIN `db_messages_answers`
                ON(m.`id` = `db_messages_answers`.`message_id`)
                WHERE m.`user_id` = '$user_id'
                GROUP BY m.`id`
                ORDER BY m.`date` DESC";
        return $this->db->query($sql);
    }

    /* INBOX MESSAGE */

    public function getMessagesInbox($user_id){
        $sql = "SELECT `id`, `users` FROM `db_messages`";
        $data = $this->db->query($sql);
        $array_id = [];
        $count = count($data);
        for($i=0;$i<$count;$i++){
            if(empty($data[$i]['users'])) continue;
            else{
                $str = $data[$i]['users'];
                $array_value = explode(', ', $str);
                if(in_array($user_id, $array_value)){
                    $array_id[] = $data[$i]['id'];
                } else continue;
            }
        }
        if(empty($array_id)) return false;
        else {
            $str_id = implode(', ', $array_id);
            $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, m.`text`, m.`id`, m.`date` FROM `db_messages` as m
                    JOIN `db_category`
                    ON(m.`cat_id` = `db_category`.`id`)
                    JOIN `db_rank`
                    ON(m.`rank_id` = `db_rank`.`id`)
                    WHERE m.`id` IN($str_id)
                    ORDER BY m.`date` DESC";
            return $this->db->query($sql);
        }
    }

    /* VIEW MESSAGE */

    public function getUserPosition($user_id){
        $sql = "SELECT `email` FROM `db_users`
                WHERE `id` = '$user_id'";
        $data = $this->db->query($sql);
        if(empty($data)){
            $str = 'Пользователь';
            return $str;
        } else {
            $str = 'Специалист';
            return $str;
        }
    }

    public function getUserMessageOut($message_id){
        $sql = "SELECT COUNT(`message_id`) as answers, `db_rank`.`rank`, `db_users`.`id` as user_id, `avatar`, `firstname`, `lastname`, `db_messages`.`text`, `db_messages`.`users` FROM `db_messages`
                JOIN `db_users`
                ON(`db_messages`.user_id = `db_users`.id)
                JOIN `db_rank`
                ON(`db_messages`.`rank_id` = `db_rank`.`id`)
                LEFT JOIN `db_messages_answers`
                ON(`db_messages`.`id` = `db_messages_answers`.`message_id`)
                WHERE `db_messages`.`id` = '$message_id'";
        return $this->db->query($sql);
    }

    public function getUserMessageIn($message_id){
        $sql = "SELECT `db_rank`.`rank`, `db_users`.`id` as user_id, `avatar`, `city`, `firstname`, `lastname`, `db_messages`.`text`, `db_messages`.`date` FROM `db_messages`
                JOIN `db_users`
                ON(`db_messages`.user_id = `db_users`.id)
                JOIN `db_rank`
                ON(`db_messages`.`rank_id` = `db_rank`.`id`)
                WHERE `db_messages`.`id` = '$message_id'";
        return $this->db->query($sql);
    }

    public function getAnswers($message_id){
        $sql = "SELECT `db_users`.`id` as user_id, `avatar`, `firstname`, `lastname`, `city`, `email`, `date`, `text` FROM `db_messages_answers`
                JOIN `db_users`
                ON(`db_users`.`id` = `db_messages_answers`.`user_id`)
                WHERE `message_id` = '$message_id'
                ORDER BY `db_messages_answers`.`date`";
        return $this->db->query($sql);
    }

    public function deleteMessageOut($message_id){
        $sql = "DELETE FROM `db_messages`
                WHERE `id` = '$message_id'";
        return $this->db->query($sql);
    }

    public function deleteMessageIn($message_id, $user_id){
        $sql = "SELECT `users` FROM `db_messages`
                WHERE `id` = '$message_id'";
        $data = $this->db->query($sql);
        $str = $data[0]['users'];
        $array = explode(', ', $str);
        $data_key = array_keys($array, $user_id);
        $key = $data_key[0];
        unset($array[$key]);
        $str = implode(', ', $array);
        $sql = "UPDATE `db_messages` SET `users` = '$str'
                WHERE `id` = '$message_id'";
        return $this->db->query($sql);
    }

    public function setAnswer($id, $message_id, $date, $answer){
        $sql = "INSERT INTO `db_messages_answers`(`user_id`, `message_id`, `date`, `text`)
                VALUES ('$id', '$message_id', '$date', '$answer')";
        return $this->db->query($sql);
    }

    public function checkIssetAnswer($user_id){
        $sql = "SELECT `message_id` FROM `db_messages_answers`
                WHERE `user_id` = '$user_id'";
        return $this->db->query($sql);
    }

    public function getUserAnswer($user_id, $message_id){
        $sql = "SELECT `db_users`.`id` as user_id, `avatar`, `firstname`, `lastname`, `city`, `email`, `date`, `text` FROM `db_messages_answers`
                JOIN `db_users`
                ON(`db_users`.`id` = `db_messages_answers`.`user_id`)
                WHERE `message_id` = '$message_id' AND `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        if(!empty($data)){
            return $data[0];
        } else {
            return false;
        }
    }

    /* ADMIN INDEX */

    public function getAllMessages(){
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_users`.`firstname`, `db_users`.`lastname`, `db_messages`.* FROM `db_messages`
                JOIN `db_category`
                ON(`db_category`.`id` = `db_messages`.`cat_id`)
                JOIN `db_rank`
                ON(`db_rank`.`id` = `db_messages`.`rank_id`)
                JOIN `db_users`
                ON(`db_users`.`id` = `db_messages`.`user_id`)
                ORDER BY `date` DESC";
        return $this->db->query($sql);
    }

    /* ADMIN EDIT */

    public function updateMessage($message_id, $text){
        $sql = "UPDATE `db_messages` SET `text` = '$text'
                WHERE `id` = '$message_id'";
        return $this->db->query($sql);
    }

    public function getMessageInfo($message_id){
        $sql = "SELECT `text` FROM `db_messages`
                WHERE `id` = '$message_id'";
        $data = $this->db->query($sql);
        return $data;
    }
}