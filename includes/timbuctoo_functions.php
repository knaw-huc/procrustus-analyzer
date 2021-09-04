<?php

$tq = new timQuery();

function parse_timbuctoo($struc) {
    global $tq;
    if (isset($struc["data"]["dataset"]["id"])) {
        $obj = $tq->getObjectFields($struc["data"]["dataset"]["id"]);
        if (isset($struc["data"]["dataset"]["entities"])) {
            $struc["data"]["dataset"]["entities"] = get_objects($obj["data"]["__type"]["fields"]);
        } else {
            die("Incorrect model! Missing index 'entities'");
        }
    } else {
        die("No dataset defined!");
    }
    return $struc;
}

function get_objects($list) {
    $retArray = array();
    foreach ($list as $item) {
        if (is_correct_item($item)) {
            $buffer["id"] = $item["type"]["name"];
            $buffer["name"] = $item["name"];
            $buffer["is_root"] = "no";
            $buffer["notions"] = get_object_inputs($item["type"]["name"]);
            $retArray[] = $buffer;
        }

    }
    return $retArray;
}

function is_correct_item($item) {
    if ($item["type"]["kind"] != "OBJECT") {
        return false;
    }
    $name = $item["type"]["name"];
    if (strpos($name, "_CollectionList")) {
        return false;
    }
    if (strpos($name, "http___timbuctoo_huygens_knaw_nl")) {
        return false;
    }
    return true;
}

function get_object_inputs($objectName)
{
    global $tq;
    $schema = $tq->get_graphql_data($tq->getSchema($objectName));
    return filter_input_fields($schema);
}

function filter_input_fields($schema)
{
    $system_fields = array("title", "description", "image", "getAllOfPredicate", "inOtherDataSets", "rdf_type");
    $retArray = array();
    $s = $schema["data"]["__type"]["fields"];
    foreach ($s as $key => $value) {
        if (!in_array($value["name"], $system_fields)) {
            $buffer = $s[$key];
            $buffer["label"] = "";
            if ($value["name"] == "uri") {
                $buffer["index"] = "yes";
            } else {
                $buffer["index"] = "no";
            }
            $buffer["root_index"] = "no";
            $retArray[] = $buffer;
        }
    }

    return $retArray;
}
