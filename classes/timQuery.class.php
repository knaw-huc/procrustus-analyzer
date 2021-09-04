<?php


class timQuery
{
    function get_graphql_data($json)
    {
        $options = array();
        $options[] = 'Accept: application/json';

            $options[] = 'Authorization: fake';


        $ch = curl_init(TIMBUCTOO_SERVER . '?query=' . urlencode($json));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $options);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $timArray = $this->json2array($response);
        return $timArray;
    }

    function json2array($json)
    {
        return json_decode($json, 'JSON_OBJECT_AS_ARRAY');
    }

    function getObjectFields($objectName)
    {
        $json = "query GetObjectFields { __type(name: \"$objectName\") { name kind fields { name type { name kind ofType { name kind} interfaces {name}}}}}";
        return $this->get_graphql_data($json);
    }



    function getSchema($type)
    {
        return "{ __type(name: \"$type\") {name fields {name type {name}}}}";
    }

    function getInputFields($type)
    {
        return "query inputFields {__type(name: \"$type\") {name inputFields {name type {name}}}}";
    }


}