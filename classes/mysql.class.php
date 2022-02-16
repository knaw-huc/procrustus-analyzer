<?php

class db
{
    function get_tables($con) {
        return $this->ass_arr($con->query("SHOW TABLES"));
    }

    function getFields($table, $con) {
        return $this->ass_arr($con->query("SHOW FULL COLUMNS FROM $table"));
    }

    private function ass_arr($results)
    {
        $data = array();
        $retArray = array();

        while ($row = $results->fetch_assoc()) {
            $data[] = $row;
        }
        $retArray["number_of_records"] = count($data);
        $retArray["data"] = $data;
        return $retArray;
    }
}