<?php

class modelForum extends Model{

    public function getCountTopicsSection(){
        $sql = "SELECT `cat_id`, COUNT(`id`) as count FROM `db_topics`
                GROUP BY `cat_id`";
        return $this->db->query($sql);
    }

    public function checkVisit($ip, $date){
        $sql = "SELECT `id` FROM `db_visits`
                WHERE `ip_address` = '$ip' AND `date` = '$date'";
        $data = $this->db->query($sql);
        if(empty($data)){
            return true;
        } else {
            return false;
        }
    }

    public function insertVisit($ip, $date){
        $sql = "INSERT INTO `db_visits`(`ip_address`, `date`)
                VALUES ('$ip', '$date')";
        return $this->db->query($sql);
    }

    public function deleteExpiredUsers($time){
        $sql = "DELETE FROM `db_users`
                WHERE `time_end` < '$time' AND `time_end` != '0'";
        return $this->db->query($sql);
    }

    public function getDataUser($time_start){
        $sql = "SELECT * FROM `db_users`
                WHERE `time_start` = '$time_start'";
        $data = $this->db->query($sql);
        return $data[0];
    }

    public function checkExpiredUser($id, $time){
        $sql = "SELECT `time_end` FROM `db_users`
                WHERE `id` = '$id'";
        $data = $this->db->query($sql);
        if($time > $data[0]['time_end']){
            return true;
        } else return false;
    }

    public function insertRandUser($name, $time_start, $time_end){
        $sql = "INSERT INTO `db_users`(`firstname`, `cat_id`, `rank_id`, `time_start`, `time_end`)
                VALUES('$name', '1', '1', '$time_start', '$time_end')";
        return $this->db->query($sql);
    }

    public function getTopics($page){
        $first_topic = $page * 10 - 10;
        $count_topic = 10;
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_users`.`id` as `user_id`, `firstname`, `lastname`, `email`, COUNT(`topic_id`) as answers, `db_topics`.* FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)
                LEFT JOIN `db_topics_answers`
                ON(`db_topics`.`id` = `db_topics_answers`.`topic_id`)
                GROUP BY `db_topics`.`id`
                ORDER BY `db_topics`.date DESC
                LIMIT $first_topic, $count_topic";
        return $this->db->query($sql);
    }

    public function getIDTopics(){
        $sql = "SELECT `id` FROM `db_topics`
                ORDER BY `db_topics`.`date` DESC";
        return $this->db->query($sql);
    }

    public function getTopicsSort($page, $cat, $rank, $sort){
        $first_topic = $page * 10 - 10;
        $count_topic = 10;
        if($sort == '0'){
            $order = 'date';
        } elseif($sort == '1'){
            $order = 'answers';
        }
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `firstname`, `lastname`, `email`, COUNT(`topic_id`) as answers, `db_topics`.* FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)
                LEFT JOIN `db_topics_answers`
                ON(`db_topics`.`id` = `db_topics_answers`.`topic_id`)
                WHERE `db_topics`.cat_id = '$cat' AND `db_topics`.`rank_id` = '$rank'
                GROUP BY `db_topics`.`id`
                ORDER BY `$order` DESC
                LIMIT $first_topic, $count_topic";
        return $this->db->query($sql);
    }

    public function getIDTopicsSort($cat, $rank, $sort){
        if($sort == '0'){
            $order = 'date';
        } elseif($sort == '1'){
            $order = 'answers';
        }
        $sql = "SELECT `db_topics`.`id`, COUNT(`topic_id`) as answers, `db_topics`.`date` FROM `db_topics`
                LEFT JOIN `db_topics_answers`
                ON(`db_topics`.`id` = `db_topics_answers`.`topic_id`)
                WHERE `db_topics`.cat_id = '$cat' AND `db_topics`.`rank_id` = '$rank'
                GROUP BY `db_topics`.`id`
                ORDER BY $order DESC";
        return $this->db->query($sql);
    }

    public function getIDTopicsSearch($words){
        /*Получаю все записи*/
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_topics`.`id`, `db_topics`.`subject` FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)";
        $data = $this->db->query($sql);
        $words_array = explode(' ', $words);
        $id_array = [];
        foreach ($words_array as $word){
            $word = mb_strtolower($word);
            for($i = 0;$i<count($data);$i++){
                $text = $data[$i]['category'];
                $text_array = explode(' ', $text);
                foreach ($text_array as $word_text){
                    $word_text = mb_strtolower($word_text);
                    $lev = levenshtein($word, $word_text);
                    if($lev == 0||$lev<=2){
                        $id_array[] = $data[$i]['id'];
                    }
                }

                $text = $data[$i]['rank'];
                $text_array = explode(' ', $text);
                foreach ($text_array as &$word_text){
                    $word_text = mb_strtolower($word_text);
                    $lev = levenshtein($word, $word_text);
                    if($lev == 0||$lev<=2){
                        $id_array[] = $data[$i]['id'];
                    }
                }

                $text = $data[$i]['subject'];
                $text_array = explode(' ', $text);
                foreach ($text_array as &$word_text){
                    $word_text = mb_strtolower($word_text);
                    $lev = levenshtein($word, $word_text);
                    if($lev == 0||$lev<=2){
                        $id_array[] = $data[$i]['id'];
                    }
                }
            }
        }
        if(empty($id_array)) return false;
        else {
            /*Сортирую по релевантности*/
            $id_array = array_count_values($id_array);
            arsort($id_array);

            /*Извлекаю только нужные элементы массива $data*/
            $result = [];
            foreach ($id_array as $key => $id){
                for($i=0;$i<count($data);$i++) {
                    if($data[$i]['id'] == $key){
                        $result[] = ($data[$i]['id']);
                    }
                }
            }

            return $result;
        }
    }



    public function getTopicsSearch($topics_id, $words, $page){
        /*Получаю все записи*/
        $first_topic = $page * 10 - 10;
        $count_topic = 10;
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `firstname`, `lastname`, `email`, COUNT(`topic_id`) as answers, `db_topics`.* FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)
                LEFT JOIN `db_topics_answers`
                ON(`db_topics`.`id` = `db_topics_answers`.`topic_id`)
                WHERE `db_topics`.`id` IN($topics_id)
                GROUP BY `db_topics`.`id`
                LIMIT $first_topic, $count_topic";
        $data = $this->db->query($sql);
        $id_array = [];
        $words_array = explode(' ', $words);
        foreach ($words_array as $word){
            $word = mb_strtolower($word);
            for($i = 0;$i<count($data);$i++){
                $text = $data[$i]['category'];
                $text_array = explode(' ', $text);
                foreach ($text_array as &$word_text){
                    //$word_text = mb_strtolower($word_text);
                    $lev = levenshtein($word, $word_text);
                    if($lev == 0||$lev<=2){
                        $id_array[] = $data[$i]['id'];
                        $word_text = "<span class='search-word'>$word_text</span>";
                    }
                }
                $text = implode(' ', $text_array);
                $data[$i]['category'] = $text;

                $text = $data[$i]['rank'];
                $text_array = explode(' ', $text);
                foreach ($text_array as &$word_text){
                    //$word_text = mb_strtolower($word_text);
                    $lev = levenshtein($word, $word_text);
                    if($lev == 0||$lev<=2){
                        $id_array[] = $data[$i]['id'];
                        $word_text = "<span class='search-word'>$word_text</span>";
                    }
                }
                $text = implode(' ', $text_array);
                $data[$i]['rank'] = $text;

                $text = $data[$i]['subject'];
                $text_array = explode(' ', $text);
                foreach ($text_array as &$word_text){
                    //$word_text = mb_strtolower($word_text);
                    $lev = levenshtein($word, $word_text);
                    if($lev == 0||$lev<=2){
                        $id_array[] = $data[$i]['id'];
                        $word_text = "<span class='search-word'>$word_text</span>";
                    }
                }
                $text = implode(' ', $text_array);
                $data[$i]['subject'] = $text;
            }
        }

        /*Сортирую по релевантности*/
        $id_array = array_count_values($id_array);
        arsort($id_array);

        /*Извлекаю только нужные элементы массива $data*/
        $result = [];
        foreach ($id_array as $key => $id){
            for($i=0;$i<count($data);$i++) {
                if($data[$i]['id'] == $key){
                    $result[] = ($data[$i]);
                }
            }
        }

        return $result;
    }

    public function getCategories(){
        $sql = 'SELECT * FROM `db_category`
                ORDER BY `category`';
        return $this->db->query($sql);
    }

    public function getRanks($cat_id){
        $sql = "SELECT * FROM `db_rank` WHERE `cat_id` = $cat_id
                ORDER BY `rank`";
        return $this->db->query($sql);
    }

    public function getAllRanks(){
        $sql = "SELECT * FROM `db_rank`";
        return $this->db->query($sql);
    }

    public function getAllTopics(){
        $sql = "SELECT `subject` FROM `db_topics`";
        return $this->db->query($sql);
    }

    public function getTitleCategory($cat_id){
        $sql = "SELECT `category` FROM `db_category` 
                WHERE `id` = $cat_id";
        $data = $this->db->query($sql);
        $title = $data[0]['category'];
        return $title;
    }

    public function checkIssetRank($rank){
        $sql = "SELECT `id` FROM `db_rank`
                WHERE `rank` = '$rank'";
        $data = $this->db->query($sql);
        return $data;
    }

    public function getTitleRank($rank_id){
        $sql = "SELECT `rank` FROM `db_rank` 
                WHERE `id` = $rank_id";
        $data = $this->db->query($sql);
        if(!empty($data)){
            $title = $data[0]['rank'];
            return $title;
        } else {
            return false;
        }
    }

    public function countTopics(){
        $sql = 'SELECT COUNT(`id`) as count FROM `db_topics`';
        $data = $this->db->query($sql);
        $count = $data[0]['count'];
        return $count;
    }

    public function countTopicsSort($cat, $rank){
        $sql = "SELECT COUNT(`id`) as count FROM `db_topics`
                WHERE `cat_id` = $cat AND `rank_id` = $rank";
        $data = $this->db->query($sql);
        $count = $data[0]['count'];
        return $count;
    }

    public function countTopicsSearch($words){
        $data = $this->getIDTopicsSearch($words);
        $count = count($data);
        return $count;
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
        return $data[0]['views'];
    }



    /* FORUM SECTION */

    public function getCountTopicsBySection($cat_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_topics`
                WHERE `cat_id` = '$cat_id'";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountMessagesBySection($cat_id){
        $sql = "SELECT COUNT(`topic_id`) as count FROM `db_topics_answers`
                JOIN `db_topics`
                ON(`db_topics`.`id` = `db_topics_answers`.`topic_id`)
                WHERE `cat_id` = '$cat_id'";
        $data = $this->db->query($sql);
        if(!empty($data)){
            return $data[0]['count'];
        }
    }

    public function getCountUsersBySection($cat_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_users`
                WHERE `db_users`.`cat_id` = '$cat_id' AND `db_users`.`email` != ''";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getRanksBySection($cat_id){
        $sql = "SELECT `id`, `rank` FROM `db_rank`
                WHERE `cat_id` = '$cat_id'
                ORDER BY `rank`";
        return $this->db->query($sql);
    }

    public function getTopicsBySection($page, $cat_id){
        $first_topic = $page * 10 - 10;
        $count_topic = 10;
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_users`.`id` as `user_id`, `firstname`, `lastname`, `email`, COUNT(`topic_id`) as answers, `db_topics`.* FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)
                LEFT JOIN `db_topics_answers`
                ON(`db_topics`.`id` = `db_topics_answers`.`topic_id`)
                WHERE `db_topics`.`cat_id` = '$cat_id'
                GROUP BY `db_topics`.`id`
                ORDER BY `db_topics`.date DESC
                LIMIT $first_topic, $count_topic";
        return $this->db->query($sql);
    }

    public function getTopicsBySectionSort($page, $cat_id){
        $first_topic = $page * 10 - 10;
        $count_topic = 10;
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_users`.`id` as `user_id`, `firstname`, `lastname`, `email`, COUNT(`topic_id`) as answers, `db_topics`.* FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)
                LEFT JOIN `db_topics_answers`
                ON(`db_topics`.`id` = `db_topics_answers`.`topic_id`)
                WHERE `db_topics`.`cat_id` = '$cat_id'
                GROUP BY `db_topics`.`id`
                ORDER BY answers DESC
                LIMIT $first_topic, $count_topic";
        return $this->db->query($sql);
    }

    public function getIDTopicsBySection($cat_id){
        $sql = "SELECT `db_topics`.`id` FROM `db_topics`
                WHERE `cat_id` = '$cat_id'
                ORDER BY `db_topics`.`date` DESC";
        return $this->db->query($sql);
    }

    public function getIDTopicsBySectionSort($cat_id){
        $sql = "SELECT `db_topics`.`id`, COUNT(`topic_id`) as answers FROM `db_topics`
                LEFT JOIN `db_topics_answers`
                ON(`db_topics`.`id` = `db_topics_answers`.`topic_id`)
                WHERE `cat_id` = '$cat_id'
                GROUP BY `db_topics`.`id`
                ORDER BY answers DESC";
        return $this->db->query($sql);
    }

    public function getIDTopicsByRanks($ranks){
        $sql = "SELECT `id` FROM `db_topics`
                WHERE `db_topics`.`rank_id` IN($ranks)
                ORDER BY `db_topics`.date DESC";
        return $this->db->query($sql);
    }

    public function getIDTopicsByRanksSort($ranks){
        $sql = "SELECT `db_topics`.`id`, COUNT(`topic_id`) as answers FROM `db_topics`
                LEFT JOIN `db_topics_answers`
                ON(`db_topics`.`id` = `db_topics_answers`.`topic_id`)
                WHERE `db_topics`.`rank_id` IN($ranks)
                GROUP BY `db_topics`.`id`
                ORDER BY answers DESC";
        return $this->db->query($sql);
    }

    public function getRanksByID($str){
        $sql = "SELECT `id`, `rank` FROM `db_rank`
                WHERE `id` IN($str)";
        return $this->db->query($sql);
    }

    public function getTopicsByRanks($page, $ranks){
        $first_topic = $page * 10 - 10;
        $count_topic = 10;
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_users`.`id` as `user_id`, `firstname`, `lastname`, `email`, COUNT(`topic_id`) as answers, `db_topics`.* FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)
                LEFT JOIN `db_topics_answers`
                ON(`db_topics`.`id` = `db_topics_answers`.`topic_id`)
                WHERE `db_topics`.`rank_id` IN($ranks)
                GROUP BY `db_topics`.`id`
                ORDER BY `db_topics`.date DESC
                LIMIT $first_topic, $count_topic";
        return $this->db->query($sql);
    }

    public function getTopicsByRanksSort($page, $ranks){
        $first_topic = $page * 10 - 10;
        $count_topic = 10;
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_users`.`id` as `user_id`, `firstname`, `lastname`, `email`, COUNT(`topic_id`) as answers, `db_topics`.* FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)
                LEFT JOIN `db_topics_answers`
                ON(`db_topics`.`id` = `db_topics_answers`.`topic_id`)
                WHERE `db_topics`.`rank_id` IN($ranks)
                GROUP BY `db_topics`.`id`
                ORDER BY `answers` DESC
                LIMIT $first_topic, $count_topic";
        return $this->db->query($sql);
    }

    public function getCountTopicsByRanks($ranks){
        $sql = "SELECT COUNT(`id`) as count FROM `db_topics`
                WHERE `rank_id` IN($ranks)";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }


    /* FORUM VIEW */

    public function setAnswer($user_id, $topic_id, $date, $answer){
        $sql = "INSERT INTO `db_topics_answers`(`user_id`, `topic_id`, `date`, `text`)
                VALUES ('$user_id', '$topic_id', '$date', '$answer')";
        return $this->db->query($sql);
    }

    public function getUserTopic($topic_id){
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_users`.`id` as `user_id`, `firstname`, `lastname`, `avatar`, `city`, `email`, `db_users`.`rank_id` as user_rank, `db_topics`.* FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)
                WHERE `db_topics`.`id` = '$topic_id'";
        return $this->db->query($sql);
    }

    public function getUserRank($user_rank){
        $sql = "SELECT `rank` FROM `db_rank`
                WHERE `id` = '$user_rank'";
        $data = $this->db->query($sql);
        return $data[0]['rank'];
    }

    public function getAnswers($page, $topic_id){
        $first_topic = $page * 5 - 5;
        $count_topic = 5;
        $sql = "SELECT `db_users`.`firstname`, `db_users`.`lastname`, `db_users`.`avatar`, `city`, `email`, `db_rank`.`rank`, `db_topics_answers`.* FROM `db_topics_answers`
                JOIN `db_users`
                ON(`db_topics_answers`.user_id = `db_users`.id)
                JOIN `db_rank`
                ON(`db_rank`.`id` = `db_users`.`rank_id`)
                WHERE `db_topics_answers`.`topic_id` = '$topic_id'
                ORDER BY `db_topics_answers`.date DESC
                LIMIT $first_topic, $count_topic";
        return $this->db->query($sql);
    }

    public function getComments($topic_id){
        $sql = "SELECT * FROM `db_topics_comments`
                WHERE `topic_id` = '$topic_id'";
        return $this->db->query($sql);
    }

    public function countAnswers($topic_id){
        $sql = "SELECT COUNT(`id`) as count FROM `db_topics_answers`
                WHERE `topic_id` = '$topic_id'";
        $data = $this->db->query($sql);
        $count = $data[0]['count'];
        return $count;
    }

    public function setTopicViews($topic_id, $ip, $date){
        $sql = "SELECT `id` FROM `db_topics_views`
                WHERE `topic_id` = '$topic_id' AND `ip` = '$ip' AND `date` = '$date'";
        $data = $this->db->query($sql);
        if(empty($data)){
            $sql = "INSERT INTO `db_topics_views`(`topic_id`, `views`, `ip`, `date`)
                    VALUES ('$topic_id', '1', '$ip', '$date')";
            return $this->db->query($sql);
        } else return false;
//        else {
//            $sql = "UPDATE `db_topics_views` SET `views` = `views` + 1";
//            return $this->db->query($sql);
//        }
    }

    public function getAnswerInfoByID($answer_id){
        $sql = "SELECT `db_users`.`firstname`, `db_users`.`lastname`, `db_topics_answers`.* FROM `db_topics_answers`
                JOIN `db_users`
                ON(`db_users`.`id` = `db_topics_answers`.`user_id`)
                WHERE `db_topics_answers`.`id` = '$answer_id'";
        return $this->db->query($sql);
    }

    public function insertAnswerComment($topic_id, $answer_id, $firstname, $lastname, $date_answer, $text){
        $sql = "INSERT INTO `db_topics_comments` (`topic_id`, `answer_id`, `firstname`, `lastname`, `date`, `text`)
                VALUES ('$topic_id', '$answer_id', '$firstname', '$lastname', '$date_answer', '$text')";
        return $this->db->query($sql);
    }

    public function getAnswerID($user_id, $date, $answer){
        $sql = "SELECT `id` FROM `db_topics_answers`
                WHERE `user_id` = '$user_id' AND `date` = '$date' AND `text` = '$answer'";
        $data = $this->db->query($sql);
        return $data[0]['id'];
    }

    /* PAGE FEEDBACK */

    public function checkValidName($name){
        if(preg_match('/^[a-zA-Zа-яА-Я]+$/u', $name)){
            return true;
        } else {
            return false;
        }
    }

    public function insertFeedback($firstname, $email, $subject, $text, $date){
        $sql = "INSERT INTO `db_feedback`(`name`, `email`, `subject`, `text`, `date`)
                VALUES('$firstname', '$email', '$subject', '$text', '$date')";
        return $this->db->query($sql);
    }


    /* ADMIN FORUM INDEX */

    public function getDataAllTopics(){
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `db_users`.`firstname`, `db_users`.`lastname`, `db_topics`.* 
                FROM `db_topics`
                JOIN `db_category`
                ON(`db_category`.`id` = `db_topics`.`cat_id`)
                JOIN `db_rank`
                ON(`db_rank`.`id` = `db_topics`.`rank_id`)
                JOIN `db_users`
                ON(`db_users`.`id` = `db_topics`.`user_id`)
                ORDER BY `date` DESC";
        return $this->db->query($sql);
    }

    /* ADMIN STATISTIC */

    public function getCountUsers(){
        $sql = "SELECT COUNT(`id`) as count FROM `db_users`
                WHERE `email` != ''";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountUsersToday($date){
        $sql = "SELECT COUNT(`id`) as count FROM `db_users`
                WHERE `email` != '' AND `date_reg` = '$date'";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountTopics(){
        $sql = "SELECT COUNT(`id`) as count FROM `db_topics`";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountTopicsToday($date){
        $array = explode(':', $date);
        $year = $array[0];
        $month = $array[1];
        $day = $array[2];
        $sql = "SELECT COUNT(`id`) as count FROM `db_topics`
                WHERE year(`date`) = '$year' AND month(`date`) = '$month' AND day(`date`) = '$day'";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountMessages(){
        $sql = "SELECT COUNT(`id`) as count FROM `db_messages`";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountMessagesToday($date){
        $array = explode(':', $date);
        $year = $array[0];
        $month = $array[1];
        $day = $array[2];
        $sql = "SELECT COUNT(`id`) as count FROM `db_messages`
                WHERE year(`date`) = '$year' AND month(`date`) = '$month' AND day(`date`) = '$day'";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountVisits(){
        $sql = "SELECT COUNT(`id`) as count FROM `db_visits`";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountVisitsToday($date){
        $sql = "SELECT COUNT(`id`) as count FROM `db_visits`
                WHERE `date` = '$date'";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountCategory(){
        $sql = "SELECT COUNT(`id`) as count FROM `db_category`";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

    public function getCountRank(){
        $sql = "SELECT COUNT(`id`) as count FROM `db_rank`";
        $data = $this->db->query($sql);
        return $data[0]['count'];
    }

}
