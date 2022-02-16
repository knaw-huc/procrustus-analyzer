<?php
/*Analyzer functions*/

function parse($struc) {
    $retJSON = array();
    if (isset($struc["data"]["datasource"]["type"])) {
        switch($struc["data"]["datasource"]["type"]) {
            case "MySQL":
                write("Data source: MySQL");
                $retJSON = parse_mysql($struc);
                break;
            case "PostgresDB":
                write("Data source: Postgres DB");
                $retJSON = parse_postgres($struc);
                break;
            case "Timbuctoo":
                write("Datasource: Timbuctoo");
                $retJSON = parse_timbuctoo($struc);
                break;
            default:
                write("Unknown data source!");
        }
        write_generated_model($retJSON);
    } else {
        die("No data source type defined!");
    }
}

function parse_mysql($struc) {
    $db = new db();

    $db_server = $struc["data"]["datasource"]["properties"]["server"];
    $db_user = $struc["data"]["datasource"]["properties"]["user"];
    $db_passwd = $struc["data"]["datasource"]["properties"]["password"];
    $db_name = $struc["data"]["datasource"]["properties"]["database"];
    $con = new mysqli($db_server, $db_user, $db_passwd, $db_name);
    $tables = $db->get_tables($con);
    foreach ($tables["data"] as $table) {
        $first = array_shift($table);
        $buffer = array();
        $buffer["entity"] = $first;
        $buffer["is_root"] = "no";
        $buffer["notions"] = add_mysql_notions($first, $db, $con);
        $struc["data"]["entities"][] = $buffer;
    }
    return $struc;
}

function parse_postgres($struc) {
    $con = get_postgres_connect_str($struc);
    $pdb = new pdb($con);

    $tables = $pdb->get_tables();
    foreach ($tables as $table) {
        echo $table["tablename"] . "\n";
        $buffer = array();
        $buffer["entity"] = $table["tablename"];
        $buffer["is_root"] = "no";
        $buffer["notions"] = add_postgres_notions($table["tablename"], $pdb);
        $struc["data"]["entities"][] = $buffer;
    }
    return $struc;
}



function get_postgres_connect_str($struc) {
    $db_server = $struc["data"]["datasource"]["properties"]["server"];
    if (isset($struc["data"]["datasource"]["properties"]["port"])) {
        $db_port = $struc["data"]["datasource"]["properties"]["port"];
    } else {
        $db_port = "5432";
    }
    $db_user = $struc["data"]["datasource"]["properties"]["user"];
    $db_passwd = $struc["data"]["datasource"]["properties"]["password"];
    $db_name = $struc["data"]["datasource"]["properties"]["database"];

    return "host=$db_server port=$db_port dbname=$db_name user=$db_user password=$db_passwd";
}

function write_generated_model($struc) {
    if (count($struc)) {
        file_put_contents(OUTPUT_DIR . CURRENT_MODEL . ".json", json_encode($struc));
        write("Ready");
    } else {
        write("No model generated!");
    }

}

function add_mysql_notions($table, $db, $con) {
    $fields = $db->getFields($table, $con);
    return process_mysql_fields($fields);
}

function add_postgres_notions($table, $pdb) {
    $fields = $pdb->get_fields($table);
    return process_postgres_fields($fields);
}

function process_mysql_fields($fields) {
    $retArray = array();
    foreach ($fields["data"] as $field) {
        $buffer = array();
        $buffer["notion"] = $field["Field"];
        $buffer["attributes"] = array();
        $buffer["indexer"] = array();
        if (strlen($field["Comment"])) {
            $buffer["attributes"]["label"] = $field["Comment"];
            $buffer["attributes"]["display"] = "yes";
            $buffer["attributes"]["display_order"] = "0";
        } else {
            $buffer["attributes"]["label"] =  $field["Field"];
            $buffer["attributes"]["display"] = "no";
            $buffer["attributes"]["display_order"] = "0";
        }
        $retArray[] = $buffer;
    }
    return $retArray;
}

function process_postgres_fields($fields) {
    $retArray = array();
    foreach ($fields as $field) {
        $buffer = array();
        $buffer["notion"] = $field["column_name"];
        $buffer["attributes"] = array();
        $buffer["indexer"] = array();
        $buffer["attributes"]["label"] =  $field["column_name"];
        $buffer["attributes"]["display"] = "no";
        $buffer["attributes"]["display_order"] = "0";

        $retArray[] = $buffer;
    }
    return $retArray;
}

function write($str) {
    echo "$str\n";
}

/* Indexer functions */
function create_index($struc) {
    $tq = new Timquery();

    $dataset_id = $struc["dataset_id"];
    $prefix = $struc["prefix"];
    $collection_prefix = $struc["collection_prefix"];
    $notion_prefix = $struc["notion_prefix"];
    $entity_name = $struc["entity"]["name"];
    $entity_title = $struc["entity"]["title"];
    $fields = get_fields($struc, $prefix, $notion_prefix);
    $query = "{ dataSets { $dataset_id { $prefix$collection_prefix$entity_name(count: 8000) {total items { uri $fields }} } }}";
    echo $query . "\n";
    $result = $tq->get_graphql_data($query);
    $items = $result["data"]["dataSets"][$dataset_id]["$prefix$collection_prefix$entity_name"]["items"];
    process($items, $prefix, $notion_prefix, $entity_title);
}

function get_fields($struc, $prefix, $notion_prefix) {
    $retStr = "";
    foreach ($struc["entity"]["notions"] as $notion) {
        $retStr .= "$prefix$notion_prefix{$notion["name"]} { value } ";
    }
    return $retStr;
}

function process($items, $prefix, $notion_prefix, $entity_title) {
    foreach ($items as $item) {
        process_item($item, $prefix, $notion_prefix, $entity_title);
    }
}

function process_item($item, $prefix, $notion_prefix, $entity_title) {
    $values = array();
    foreach ($item as $key => $value) {
        if ($key == "uri") {
            $values[$key] = $value;
        } else {
            $new_key = strtolower(str_replace("$prefix$notion_prefix", "", $key));
            $values[$new_key] = $value["value"];
        }
    }
   // print_r($values);
   publish($values, INDEX_URL . $entity_title . "/_doc");
}

function publish($passage, $url)
{
    $json_struc = json_encode($passage);
    $options = array('Content-type: application/json', 'Content-Length: ' . strlen($json_struc));
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $options);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_struc);
    //curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    echo $response;
    curl_close($ch);
    //echo "$id indexed\n";
}

/* Zitingen functies */

function zittingen($struc) {
    $i = 0;
    foreach ($struc as $element) {
        $i++;
        processElement($element, $i);
    }
}

function processElement($el, $i) {
    $element = array("zittingsdag_id" => $el["metadata"]["zittingsdag_id"]);
    $element["uri"] = "http://example.org/datasets/u33707283d426f900d4d33707283d426f900d4d0d/delegates/rawData/5d1240e1-6b39-44b7-920b-272d00e2e66b-sessions_csv/entities/" . $i;
    $element["inventory_num"] = $el["metadata"]["inventory_num"];
    $element["url"] = $el["metadata"]["url"];
    $element["text"] = $el["metadata"]["text"];
    $element["spans"] = $el["spans"];
    pr($el["metadata"]["zittingsdag_id"]);
    publish($element, INDEX_URL . "sessions" . "/_doc");
}

function pr($line) {
    echo "$line\n";
}
