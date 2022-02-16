<?php

require_once "./config/config.php";
require_once "./classes/mysql.class.php";
require_once "./classes/timQuery.class.php";
require "./includes/functions.php";

$json = file_get_contents(INDEX_MODELS . CURRENT_INDEX_MODEL . ".json");
$struc = json_decode($json, true);

create_index($struc);