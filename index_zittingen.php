<?php
require_once "./config/config.php";
require_once "./classes/timQuery.class.php";
require "./includes/functions.php";

$json = file_get_contents("/Users/robzeeman/Documents/DI/procrustes/dev/analyzer/data/1728_pres.json");
$struc = json_decode($json, true);

zittingen($struc);
