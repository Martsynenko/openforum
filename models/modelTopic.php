<?php

class userTopic extends Model{

    public function getCategories(){
        $sql = 'SELECT * FROM `db_category`';
        return $this->db->query($sql);
    }

    public function getRanks($cat_id){
        $sql = "SELECT * FROM `db_rank` WHERE `cat_id` = $cat_id";
        return $this->db->query($sql);
    }

    public function insertTopic($cat_id, $rank_id, $subject, $text, $date, $user_id){
        $sql = "INSERT INTO `db_moderation_topics`(`cat_id`, `rank_id`, `title`, `text`, `date`, `user_id`)
                VALUES ('$cat_id', '$rank_id', '$subject', '$text', '$date', '$user_id')";
        return $this->db->query($sql);
    }

    public function getUserTopics($user_id, $page){
        $first_topic = $page * 5 - 5;
        $count_topic = 5;
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `firstname`, `lastname`, COUNT(`topic_id`) as answers, `db_topics`.* FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)
                LEFT JOIN `db_topics_answers`
                ON(`db_topics`.`id` = `db_topics_answers`.`topic_id`)
                WHERE `db_topics`.`user_id` = '$user_id'
                GROUP BY `db_topics`.`id`
                ORDER BY `db_topics`.date DESC
                LIMIT $first_topic, $count_topic";
        return $this->db->query($sql);
    }

    public function getUserTopic($topic_id){
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_users`.`id` as user_id, `firstname`, `lastname`, `avatar`, `db_topics`.* FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)
                WHERE `db_topics`.`id` = '$topic_id'";
        return $this->db->query($sql);
    }

    public function getAnswers($page, $topic_id){
        $first_topic = $page * 5 - 5;
        $count_topic = 5;
        $sql = "SELECT `db_users`.`firstname`, `db_users`.`lastname`, `db_users`.`avatar`, `db_topics_answers`.* FROM `db_topics_answers`
                JOIN `db_users`
                ON(`db_topics_answers`.user_id = `db_users`.id)
                WHERE `db_topics_answers`.`topic_id` = '$topic_id'
                ORDER BY `db_topics_answers`.date DESC
                LIMIT $first_topic, $count_topic";
        return $this->db->query($sql);
    }

    public function countTopics($user_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_topics`
                WHERE `user_id` = '$user_id'";
        $data = $this->db->query($sql);
        $count = $data[0]['count'];
        return $count;
    }

    public function countAnswers($topic_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_topics_answers`
                WHERE `topic_id` = '$topic_id'";
        $data = $this->db->query($sql);
        $count = $data[0]['count'];
        return $count;
    }

    public function setAnswer($user_id, $topic_id, $date, $answer){
        $sql = "INSERT INTO `db_topics_answers`(`user_id`, `topic_id`, `date`, `text`)
                VALUES ('$user_id', '$topic_id', '$date', '$answer')";
        return $this->db->query($sql);
    }

    public function deleteTopic($topic_id){
        $sql = "DELETE FROM `db_topics`
                WHERE `id` = '$topic_id'";
        return $this->db->query($sql);
    }

    public function getCountViews(){
        $sql = "SELECT `topic_id`, COUNT(`topic_id`) as views FROM `db_topics_views`
                GROUP BY `topic_id`";
        return $this->db->query($sql);
    }

    public function getCountViewsByID($topic_id){
        $sql = "SELECT COUNT(`topic_id`) as views FROM `db_topics_views`
                WHERE `topic_id` = '$topic_id'
                GROUP BY `topic_id`";
        $data = $this->db->query($sql);
        if(!empty($data)){
            return $data[0]['views'];
        } else {
            return 0;
        }
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

    /* ADMIN EDIT */

    public function getTopicInfo($topic_id){
        $sql = "SELECT `subject`, `text` FROM `db_topics`
                WHERE `id` = '$topic_id'";
        return $this->db->query($sql);
    }

    public function updateTopic($topic_id, $subject, $text){
        $sql = "UPDATE `db_topics` 
                SET `subject` = '$subject', `text` = '$text'
                WHERE `id` = '$topic_id'";
        return $this->db->query($sql);
    }

}