<?php

class ForumTopics extends Model{

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
        $sql = "INSERT INTO `db_users`(`firstname`, `category`, `rank`, `time_start`, `time_end`)
                VALUES('$name', '1', '1', '$time_start', '$time_end')";
        return $this->db->query($sql);
    }

    public function getTopics($page){
        $first_topic = $page * 5 - 5;
        $count_topic = 5;
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `firstname`, `lastname`, `db_topics`.* FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)
                ORDER BY `db_topics`.date DESC
                LIMIT $first_topic, $count_topic";
        return $this->db->query($sql);
    }

    public function getTopicsSort($page, $cat, $rank, $sort){
        $first_topic = $page * 5 - 5;
        $count_topic = 5;
        if($sort == '0'){
            $order = 'date';
        } elseif($sort == '1'){
            $order = 'views';
        }
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `firstname`, `lastname`, `db_topics`.* FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)
                WHERE `db_topics`.cat_id = $cat AND `db_topics`.`rank_id` = $rank
                ORDER BY `db_topics`.$order DESC
                LIMIT $first_topic, $count_topic";
        return $this->db->query($sql);
    }

    public function getTopicsSearch($words, $page = 1){
        /*Получаю все записи*/
        $first_topic = $page * 5 - 5;
        $count_topic = 5;
        $sql = "SELECT `db_category`.`category`, `db_rank`.`rank`, `firstname`, `lastname`, `db_topics`.* FROM `db_topics`
                JOIN `db_users`
                ON(`db_topics`.user_id = `db_users`.id)
                JOIN `db_category`
                ON(`db_topics`.`cat_id` = `db_category`.`id`)
                JOIN `db_rank`
                ON(`db_topics`.`rank_id` = `db_rank`.`id`)
                LIMIT $first_topic, $count_topic";
        $data = $this->db->query($sql);
        $words_array = explode(' ', $words);
        $id_array = [];
        foreach ($words_array as $word){
            $word = mb_strtolower($word);
            for($i = 0;$i<count($data);$i++){
                $text = $data[$i]['category'];
                $text_array = explode(' ', $text);
                foreach ($text_array as &$word_text){
                    $word_text = mb_strtolower($word_text);
                    $lev = levenshtein($word, $word_text);
                    if($lev == 0||$lev<=2){
                        $id_array[] = $data[$i]['id'];
                        $word_text = "<span style='background-color: #edecff; 
                                                   -webkit-border-radius: 2px;
                                                   -moz-border-radius: 2px;
                                                   border-radius: 2px;'>$word_text</span>";
                    }
                }
                $text = implode(' ', $text_array);
                $data[$i]['category'] = $text;

                $text = $data[$i]['rank'];
                $text_array = explode(' ', $text);
                foreach ($text_array as &$word_text){
                    $word_text = mb_strtolower($word_text);
                    $lev = levenshtein($word, $word_text);
                    if($lev == 0||$lev<=2){
                        $id_array[] = $data[$i]['id'];
                        $word_text = "<span style='background-color: #edecff; 
                                                   -webkit-border-radius: 2px;
                                                   -moz-border-radius: 2px;
                                                   border-radius: 2px;'>$word_text</span>";
                    }
                }
                $text = implode(' ', $text_array);
                $data[$i]['rank'] = $text;

                $text = $data[$i]['subject'];
                $text_array = explode(' ', $text);
                foreach ($text_array as &$word_text){
                    $word_text = mb_strtolower($word_text);
                    $lev = levenshtein($word, $word_text);
                    if($lev == 0||$lev<=2){
                        $id_array[] = $data[$i]['id'];
                        $word_text = "<span style='background-color: #edecff; 
                                                   -webkit-border-radius: 2px;
                                                   -moz-border-radius: 2px;
                                                   border-radius: 2px;'>$word_text</span>";
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
        $sql = 'SELECT * FROM `db_category`';
        return $this->db->query($sql);
    }

    public function getRanks($cat_id){
        $sql = "SELECT * FROM `db_rank` WHERE `cat_id` = $cat_id";
        return $this->db->query($sql);
    }

    public function getTitleCategory($cat_id){
        $sql = "SELECT `category` FROM `db_category` 
                WHERE `id` = $cat_id";
        $data = $this->db->query($sql);
        $title = $data[0]['category'];
        return $title;
    }

    public function getTitleRank($rank_id){
        $sql = "SELECT `rank` FROM `db_rank` 
                WHERE `id` = $rank_id";
        $data = $this->db->query($sql);
        $title = $data[0]['rank'];
        return $title;
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
        $data = $this->getTopicsSearch($words);
        $count = count($data);
        return $count;
    }

}
