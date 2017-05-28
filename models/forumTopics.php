<?php

class Top extends Model{

    public function getAllTopicsDefault(){
        $sql = 'select * from `db_topics`';

        return $this->db->query($sql);
    }
}