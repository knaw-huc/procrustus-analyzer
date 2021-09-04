<?php
define('APPPATH', dirname(dirname(__FILE__)));
define("MODEL_DIR", APPPATH . "/models/");
define("CURRENT_MODEL", "dwc");
define("INDEX_MODELS", APPPATH . "/tim_index_models/");
define("CURRENT_INDEX_MODEL", "abbreviated_delegates");
define("OUTPUT_DIR", APPPATH . "/output/");
define('TIMBUCTOO_SERVER', 'http://localhost:8080/v5/graphql');
define('INDEX_URL', 'http://localhost:9200/');