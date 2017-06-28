<?php

class modelFeedback extends Model{

    public function getAllFeedbackMessages(){
        $sql = "SELECT * FROM `db_feedback`
                ORDER BY `date`";
        return $this->db->query($sql);
    }

    /* ADMIN ANSWER */

    public function getMessageInfo($message_id){
        $sql = "SELECT `name`, `email` FROM `db_feedback`
                WHERE `id` = '$message_id'";
        return $this->db->query($sql);
    }

    /* ADMIN DELETE */

    public function deleteFeedbackMessage($message_id){
        $sql = "DELETE FROM `db_feedback`
                WHERE `id` = '$message_id'";
        return $this->db->query($sql);
    }
}