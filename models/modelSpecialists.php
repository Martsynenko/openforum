<?php

class modelSpecialists extends Model{

    public function getCategories(){
        $sql = 'SELECT * FROM `db_category`
                ORDER BY `category`';
        return $this->db->query($sql);
    }

    public function getSpecialists($page){
        $first_topic = $page * 20 - 20;
        $count_topic = 20;
        $sql = "SELECT `db_users`.`id`, `firstname`, `lastname`, `city`, `avatar`, `db_rank`.`rank` FROM `db_users`
                JOIN `db_rank`
                ON(`db_rank`.`id` = `db_users`.`rank_id`)
                WHERE `db_users`.`email` != ''
                ORDER BY `db_users`.`date_reg`
                LIMIT $first_topic, $count_topic";
        return $this->db->query($sql);
    }

    public function getSpecialistsSort($page, $cat, $rank){
        $first_topic = $page * 20 - 20;
        $count_topic = 20;
        $sql = "SELECT `db_users`.`id`, `firstname`, `lastname`, `city`, `avatar`, `db_rank`.`rank`  FROM `db_users`
                JOIN `db_rank`
                ON(`db_rank`.`id` = `db_users`.`rank_id`)
                WHERE `db_users`.`cat_id` = '$cat' AND `db_users`.`rank_id` = '$rank' AND `email` != ''
                ORDER BY `db_users`.`date_reg`
                LIMIT $first_topic, $count_topic";
        return $this->db->query($sql);
    }

    public function countTopics(){
        $sql = "SELECT COUNT(`id`) as count FROM `db_users`
                WHERE `email` != ''";
        $data = $this->db->query($sql);
        $count = $data[0]['count'];
        return $count;
    }

    public function countTopicsSort($cat, $rank){
        $sql = "SELECT COUNT(`id`) as count FROM `db_users`
                WHERE `cat_id` = '$cat' AND `rank_id` = '$rank' AND `email` != ''";
        $data = $this->db->query($sql);
        $count = $data[0]['count'];
        return $count;
    }

    public function checkIssetRank($rank){
        $sql = "SELECT `id` FROM `db_rank`
                WHERE `rank` = '$rank'";
        $data = $this->db->query($sql);
        return $data;
    }

    /* SPECIALIST VIEW */

    public function getSpecialistInfo($specialist_id){
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, year(`birthday`) as year, `city`, `firstname`, `lastname`, `avatar` FROM `db_users` 
                JOIN `db_category`
                ON(`db_users`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_users`.`rank_id` = `db_rank`.`id`)
                WHERE `db_users`.`id` = '$specialist_id'";
        $data = $this->db->query($sql);
        return $data[0];
    }

    public function getCountUserTopics($user_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_topics`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountUserAnswers($user_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_topics_answers`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getUserRank($user_id){
        $sql = "SELECT `rank_id` FROM `db_users`
                WHERE `id` = '$user_id'";
        $data = $this->db->query($sql);
        return $data[0]['rank_id'];
    }

    public function getPopularTopics($user_id){
        $sql = "SELECT `id` FROM `db_topics` WHERE
                `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        if(!empty($data)){
            $array = [];
            for($i=0;$i<count($data);$i++){
                $array[] = $data[$i]['id'];
            }
            $str = implode(', ', $array);
            $sql = "SELECT COUNT(`db_topics_views`.`id`) as count, `db_topics`.`subject`, `db_topics_views`.`topic_id` FROM `db_topics_views`
                JOIN `db_topics`
                ON(`db_topics`.`id` = `db_topics_views`.`topic_id`)
                GROUP BY `topic_id`
                HAVING `topic_id` IN($str)
                ORDER BY count DESC
                LIMIT 3";
            $data = $this->db->query($sql);
            if(empty($data[0]['count'])){
                return false;
            } else {
                return $data;
            }
        } else {
            return 0;
        }
    }

    public function getLastTopics($user_id){
        $sql = "SELECT `id`, `subject` FROM `db_topics`
                WHERE `user_id` = '$user_id' 
                ORDER BY `date` DESC
                LIMIT 3";
        $data = $this->db->query($sql);
        return $data;
    }

    public function getSimilarUsers($rank_id, $user_id){
        $sql = "SELECT `db_users`.`id`, `db_rank`.`rank`, `city`, `firstname`, `lastname`, `avatar` FROM `db_users` 
                JOIN `db_rank`
                ON(`db_users`.`rank_id` = `db_rank`.`id`)
                WHERE `db_users`.`rank_id` = '$rank_id' AND `email` != '' AND `db_users`.`id` != '$user_id'
                ORDER BY `date_reg`
                LIMIT 8";
        return $this->db->query($sql);
    }

    /* SPECIALIST ADD */

    public function add($user_id, $spec_id){
        $sql = "SELECT `id` FROM `db_user_specialists`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        if(empty($data)){
            $sql = "INSERT INTO `db_user_specialists`(`user_id`, `specialists`)
                    VALUES ('$user_id', '$spec_id')";
            return $this->db->query($sql);
        } else {
            $sql = "SELECT `specialists` FROM `db_user_specialists`
                    WHERE `user_id` = '$user_id'";
            $data = $this->db->query($sql);
            $str = $data[0]['specialists'];
            if(empty($str)){
                $str = "$spec_id";
            } else {
                $str .= ", $spec_id";
            }
            $sql = "UPDATE `db_user_specialists` SET `specialists` = '$str'
                    WHERE `user_id` = '$user_id'";
            return $this->db->query($sql);
        }
    }

    public function checkIssetSpecialist($user_id, $specialist_id){
        $sql = "SELECT `specialists` FROM `db_user_specialists`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        if(!empty($data)){
            $str = $data[0]['specialists'];
            $array = explode(', ', $str);
            if(in_array($specialist_id, $array)){
                return true;
            } else return false;
        }
    }


    /* USER INDEX (SPECIALIST) */

    public function getListSpecialist($user_id){
        $sql = "SELECT `specialists` FROM `db_user_specialists`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        $str = $data[0]['specialists'];
        if(!empty($str)){
            $sql = "SELECT `db_rank`.`rank`, COUNT(`user_id`) as sub, `db_users`.`id` as user_id, `firstname`, `lastname`, `avatar` FROM `db_users` 
                    JOIN `db_rank`
                    ON(`db_users`.`rank_id` = `db_rank`.`id`)
                    LEFT JOIN `db_topics`
                    ON(`db_topics`.`user_id` = `db_users`.`id`)
                    WHERE `db_users`.`id` IN($str)
                    GROUP BY `db_users`.`id`";
            return $this->db->query($sql);
        } else {
            return false;
        }
    }

    public function getListCountAnswers(){
        $sql = "SELECT `user_id`, COUNT(`id`) as answers FROM `db_topics_answers`
                GROUP BY `user_id`";
        $data = $this->db->query($sql);
        return $data;
    }

    public function deleteSpecialist($user_id, $specialist_id){
        $sql = "SELECT `specialists` FROM `db_user_specialists`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        $str = $data[0]['specialists'];
        $array = explode(', ', $str);
        $data_key = array_keys($array, $specialist_id);
        $key = $data_key[0];
        unset($array[$key]);
        $str = implode(', ', $array);
        $sql = "UPDATE `db_user_specialists` SET `specialists` = '$str'
                WHERE `user_id` = '$user_id'";
        return $this->db->query($sql);
    }
}