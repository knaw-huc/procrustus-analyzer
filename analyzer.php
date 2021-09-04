<?php
require_once "./config/config.php";
require_once "./classes/db.class.php";
require_once "./classes/timQuery.class.php";
require_once "./includes/timbuctoo_functions.php";
require "./includes/functions.php";

$json = file_get_contents(MODEL_DIR . CURRENT_MODEL . ".json");
$struc = json_decode($json, true);

parse($struc);
