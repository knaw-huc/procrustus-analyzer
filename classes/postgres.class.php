<?php

class pdb {
    protected $con;

    public function __construct($con) {
        $this->con = pg_connect($con);
    }

    function get_tables() {
        $results = pg_query($this->con, "SELECT tablename FROM pg_catalog.pg_tables where schemaname = 'public'");
        return $this->ass_arr($results);
    }

    function get_fields($table) {
        $results = pg_query($this->con, "SELECT column_name, is_nullable, data_type FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table'");
        return $this->ass_arr($results);
    }

    private
    function ass_arr($results)
    {
        $retArray = array();
        while ($row = pg_fetch_assoc($results)) {
            $retArray[] = $row;
        }
        return $retArray;
    }

}
