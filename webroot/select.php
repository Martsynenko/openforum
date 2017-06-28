<?php

$mysqli = new mysqli('localhost', 'root', '', 'db_openforum');
//$mysqli = new mysqli('localhost', 'openforu_Aleksandr', 'martsynenko1989', 'openforu_db_openforum');
$mysqli->query('SET NAMES UTF8');

if(isset($_POST['id_category'])){
    $cat_id = $_POST['id_category'];
    $result = $mysqli->query("SELECT * FROM `db_rank` WHERE `cat_id` = '$cat_id' ORDER BY `rank`");
    $data = [];
    while($row = mysqli_fetch_assoc($result)){
        $data[] = $row;
    }
    for($i=0;$i<count($data);$i++){
        $rank_id = $data[$i]['id'];
        $title = $data[$i]['rank'];
        echo "<option value='$rank_id'>$title</option>";
    }

    echo "<option style='color: #a0a0a0;' value='0'>Другая специальность...</option>";
}

if(isset($_POST['id_month'])){
    $num = $_POST['id_month'];
    if($num == 4||$num == 6||$num == 9||$num == 11){
        for($i=1;$i<31;$i++) {
            echo "<option value=\"$i\">$i</option>";
        }
    } elseif($num == 2){
        for($i=1;$i<30;$i++) {
            echo "<option value=\"$i\">$i</option>";
        }
    } else {
        for($i=1;$i<32;$i++) {
            echo "<option value=\"$i\">$i</option>";
        }
    }
}