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
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
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
}