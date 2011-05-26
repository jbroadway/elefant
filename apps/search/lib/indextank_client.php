<?php

// sudo apt-get install php5 php5-curl


class InvalidResponseFromServer extends Exception {}
class TooManyIndexes extends Exception {}
class IndexAlreadyExists extends Exception {}
class InvalidQuery extends Exception {}
class InvalidDefinition extends Exception {}
class Unauthorized extends Exception {}
class InvalidUrl extends Exception {}
class HttpException extends Exception {}

function convert_to_map($array_object) {
    $result = new stdClass();
    
    for($i = 0; $i < sizeof($array_object); ++$i) {
        $result->{$i} = $array_object[$i];
    }
    
    return $result;
}

/*
 * Converts an array of 2 elements (which can be NULL) to a colon-separated string containing those elements.
 * NULL elements are replaced by '*'
 *
 * Examples: 
 *     map_range(array(2,4)) => "2:4";
 *     map_range(array(NULL,3)) => "*:3";
 *     map_range(array(5,NULL)) => "5:*";
 *     map_range(array(NULL,NULL)) => "*:*";
 *
 *
 * @param val: an array of 2 elements.
 */
function map_range($val) {
    return sprintf("%s:%s",($val[0] == NULL ? "*": $val[0]), ($val[1] == NULL ? "*": $val[1]));
}

function api_call($method, $url, $params=array()) {
    $splits = parse_url($url);
    if (! empty($splits['scheme'])) { $scheme = $splits['scheme'].'://'; } else { throw new InvalidUrl("[".$url."]"); }
    if (! empty($splits['host'])) { $hostname = $splits['host']; } else { throw new InvalidUrl("[".$url."]"); }
    //if (! empty($splits['user'])) { $username = $splits['user']; } else { throw new Unauthorized("[".$url."]"); }
    //if (! empty($splits['pass'])) { $password = $splits['pass']; } else { throw new Unauthorized("[".$url."]"); }
    if (! empty($splits['path'])) { $path = $splits['path']; } else { $path = ''; }
    if (! empty($splits['query'])) { $query = '?'.$splits['query']; } else { $query = ''; }
    if (! empty($splits['fragment'])) { $fragment = '#'.$splits['fragment']; } else { $fragment = ''; }
    $netloc = $hostname;
    if (! empty($splits["port"])) { $netloc = $netloc . ":" . $splits['port']; }
    // drop the auth from the url
    //$url = $scheme.$netloc.$path.$query.$fragment;
    $args = '';
    $sep = '';
    
    if ($method == "GET") {
        foreach ($params as $key => $val) {
            $args .= $sep.$key.'='.urlencode($val);
            $sep = '&';
        }
        $url .= '?'.$args;
        $args = '';
    } else {
        $args = json_encode($params);
    }

    //print "url: " . $url . ": " . $args . "\n";

    $session = curl_init($url);
    curl_setopt($session, CURLOPT_CUSTOMREQUEST, $method); // Tell curl to use HTTP method of choice
    curl_setopt($session, CURLOPT_POSTFIELDS, $args); // Tell curl that this is the body of the POST
    curl_setopt($session, CURLOPT_HEADER, false); // Tell curl not to return headers
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true); // Tell curl to return the response
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Expect:')); //Fixes the HTTP/1.1 417 Expectation Failed
    $response = curl_exec($session);
    $http_code = curl_getinfo($session,CURLINFO_HTTP_CODE);
    curl_close($session); 

    if (floor($http_code/100) == 2) { 
        return new ApiResponse($http_code,$response);
    }
    throw new HttpException($response, $http_code);
}

class ApiClient {
    /*
     * Basic client for an account.
     * It needs an API url to be constructed.
     * It has methods to manage and access the indexes of the
     * account. The objects returned by these methods implement
     * the IndexClient class.
     */

    private $api_url = NULL;

    function __construct($api_url) {
        $this->api_url = rtrim($api_url,"/");
    }
    
    public function get_index($index_name) {
        return new IndexClient($this->index_url(str_replace('/','',$index_name)));
    }

    public function list_indexes() {
        return json_decode(api_call('GET', $this->indexes_url())->response);
    }

    public function create_index($index_name) {
        $index = $this->get_index($index_name);
        $index->create_index();
        return $index;
    }

    private function indexes_url() {
        return $this->api_url . '/v1/indexes';
    }
    
    

    private function index_url($index_name) {
        return $this->indexes_url() . "/" . urlencode($index_name);
    }

}


class IndexClient {
    /*
     * Client for a specific index.
     * It allows to inspect the status of the index. 
     * It also provides methods for indexing and searching said index.
     */

    private $index_url = NULL;
    private $metadata = NULL;


    function __construct($index_url, $metadata=NULL) {
        $this->index_url = $index_url;
        $this->metadata = $metadata;
    }

    public function exists() {
        /*
         * Returns whether an index for the name of this instance
         * exists, if it doesn't it can be created by calling
         * create_index()
         */
        try {
            $this->refresh_metadata();
            return true;
        } catch (HttpException $e) {
            if ($e->getCode() == 404) {
                return false;
            } else {
                throw $e;
            }
        }
    }
         
    public function has_started() {
        /*
         * Returns whether this index is responsive. Newly created
         * indexes can take a little while to get started. 
         * If this method returns False most methods in this class
         * will raise an HttpException with a status of 503.
         */
        $this->refresh_metadata();
        return $this->metadata->{'started'};
    }

    public function get_code() {
        $this->refresh_metadata();
        return $this->metadata['code'];
    }

    public function get_size() {
        $this->refresh_metadata();
        return $this->metadata['size'];
    }

    public function get_creation_time() {
        $this->refresh_metadata();
        return $this->metadata->{'creation_time'};
    }


    public function create_index() {
        /*
         * Creates this index. 
         * If it already existed a IndexAlreadyExists exception is raised. 
         * If the account has reached the limit a TooManyIndexes exception is raised
         */
        try {
            $res = api_call('PUT', $this->index_url);
            if ($res->status == 204) {
                throw new IndexAlreadyExists('An index for the given name already exists');
            }
        } catch (HttpException $e) {
            if ($e->getCode() == 409) {
                throw new TooManyIndexes($e->getMessage());
            }
            throw $e;
        }
    }

    public function delete_index() {
        $res = api_call('DELETE', $this->index_url);
        return $res->status;
    }

    public function add_document($docid, $fields, $variables = NULL) {
        /*
         * Indexes a document for the given docid and fields.
         * Arguments:
         *     docid: unique document identifier. A String no longer than 1024 bytes. Can not be NULL
         *     fields: map with the document fields. field names and values MUST be UTF-8 encoded.
         *     variables (optional): map integer -> float with values for variables that can
         *                           later be used in scoring functions during searches.
         */
        $res =  api_call('PUT', $this->docs_url(), $this->as_document($docid, $fields, $variables));
        return $res->status;
    }


    public function add_documents($documents=array()) {
        /*
         * Indexes an array of documents. Each element (document) on the array needs to be an
         * array, with 'docid', 'fields' and optionally 'variables' keys.
         * The semantic is the same as IndexClient->add_document();
         * Arguments:
         *     documents: an array of arrays, each representing a document
         */
        $data = array();
        foreach ($documents as $i => $doc_data) {
            try {
                // make sure docid is set
                if (!array_key_exists("docid", $doc_data)) {
                    throw new InvalidArgumentException("document $i lacks 'docid'");
                }
                $docid = $doc_data['docid'];
                
                // make sure fields is set
                if (!array_key_exists("fields", $doc_data)) {
                    throw new InvalidArgumentException("document $i lacks 'fields'");
                }
                $fields = $doc_data['fields'];
                
                // set $variables
                if (!array_key_exists("variables", $doc_data)) {
                    $variables = NULL;
                } else {
                    $variables = $doc_data['variables'];
                }

                $data[] = $this->as_document($docid, $fields, $variables);
            } catch (InvalidArgumentException $iae) {
                throw new InvalidArgumentException("while processing document $i: " . $iae->getMessage());
            }
        }

        $res = api_call('PUT', $this->docs_url(), $data);
        return json_decode($res->response);
    }


    public function delete_document($docid) {
        /*
         * Deletes the given docid from the index if it existed. otherwise, does nothing.
         * Arguments:
         *     docid: unique document identifier
         */
        $res = api_call('DELETE', $this->docs_url(), array("docid" => $docid));
        return $res->status;
    }

    public function update_variables($docid, $variables) {
        /*
         * Updates the variables of the document for the given docid.
         * Arguments:
         *     docid: unique document identifier
         *     variables: map integer -> float with values for variables that can
         *                later be used in scoring functions during searches.
         */
        $res = api_call('PUT', $this->variables_url(), array("docid" => $docid, "variables" => convert_to_map($variables)));
        return $res->status;
    }

    public function update_categories($docid, $categories) {
        /*
         * Updates the category values of the document for the given docid.
         * Arguments:
         *     docid: unique document identifier
         *     categories: map string -> string where each key is a category name pointing to its value
         */
        $res = api_call('PUT', $this->categories_url(), array("docid" => $docid, "categories" => $categories));
        return $res->status;
    }

    public function promote($docid, $query) {
        /*
         * Makes the given docid the top result of the given query.
         * Arguments:
         *     docid: unique document identifier
         *     query: the query for which to promote the document
         */
        $res = api_call('PUT', $this->promote_url(), array("docid" => $docid, "query" => $query));
        return $res->status;
    }

    public function add_function($function_index, $definition) {
        try {
            $res = api_call('PUT', $this->function_url($function_index), array("definition" => $definition));
            return $res->status;
        } catch (HttpException $e) {
            if ($e->getCode() == 400) {
                throw new InvalidDefinition($e->getMessage());
            }
            throw $e;
        }
    }

    public function delete_function($function_index) {
        $res = api_call('DELETE', $this->function_url($function_index));
        return $res->status;
    }

    public function list_functions() {
        $res = api_call('GET', $this->functions_url());
        return json_decode($res->response);
    }


    /*
     * Performs a search.
     *
     * @param variables: An array with 'query variables'. Example: array( 0 => 3, 1 => 34);
     * @param docvar_filters: An array with filters for document variables. 
     *     Example: array(0 => array(array(1,4), array(6, 9), array(16,NULL)))
     *     Document variable 0 should be between 1 and 4 OR 6 and 9 OR greater than 16
     * @param function_filters: An array with filters for function scores. 
     *     Example: array(2 => array(array(2,6), array(7, 11), array(15,NULL)))
     *     Scoring function 2 must return a value between 2 and 6 OR 7 and 11 OR greater than 15 for documents matching this query.
     *
     */
    public function search($query, $start=NULL, $len=NULL, $scoring_function=NULL, $snippet_fields=NULL, $fetch_fields=NULL, $category_filters=NULL, $variables=NULL, $docvar_filters=NULL, $function_filters=NULL) {
        $params = array("q" => $query);
        if ($start != NULL) { $params["start"] = $start; }
        if ($len != NULL) { $params["len"] = $len; }
        if ($scoring_function != NULL) { $params["function"] = (string)$scoring_function; }
        if ($snippet_fields != NULL) { $params["snippet"] = $snippet_fields; }
        if ($fetch_fields != NULL) { $params["fetch"] = $fetch_fields; }
        if ($category_filters != NULL) { $params["category_filters"] = $category_filters; }
        if ($variables) {
            foreach( $variables as $k => $v)
            {
                $params["var".strval($k)] = $v;
            }
        }

        if ($docvar_filters){
            // $docvar_filters is something like
            // { 3 => [ (1, 3), (5, NULL) ]} to filter_docvar3 => 1:3,5:*
            foreach( $docvar_filters as $k => $v){
                $params["filter_docvar".strval($k)] = implode(array_map( 'map_range', $v), ",");
            }
        }
        
        if ($function_filters){
            // $function_filters is something like
            // { 2 => [ (1, 4), (7, NULL) ]} to filter_function2 => 1:4,7:*
            foreach( $docvar_filters as $k => $v){
                $params["filter_function".strval($k)] = implode(array_map( 'map_range', $v), ",");
            }
        }

        try {
            $res = api_call('GET', $this->search_url(), $params);
            return json_decode($res->response);
        } catch (HttpException $e) {
            if ($e->getCode() == 400) {
                throw new InvalidQuery($e->getMessage());
            }
            throw $e;
        }
    }


    private function get_metadata() {
        if ($this->metadata == NULL) {
            return $this->refresh_metadata();
        }
        return $this->$metadata;
    }

    private function refresh_metadata() {
        $res = api_call('GET', $this->index_url, array());
        $this->metadata = json_decode($res->response);
        return $this->metadata;
    }

    /*
     * Creates a 'document', useful for IndexClient->add_document and IndexClient->add_documents
     */
    private function as_document($docid, $fields, $variables = NULL){
        if (NULL == $docid) throw new InvalidArgumentException("\$docid can't be NULL");
        if (mb_strlen($docid, '8bit') > 1024) throw new InvalidArgumentException("\$docid can't be longer than 1024 bytes");
        $data = array("docid" => $docid, "fields" => $fields);
        if ($variables != NULL) {
            $data["variables"] = convert_to_map($variables);
        }
        return $data;
    }

    private function docs_url()        { return $this->index_url . "/docs"; }
    private function variables_url()   { return $this->index_url . "/docs/variables"; }
    private function categories_url()   { return $this->index_url . "/docs/categories"; }
    private function promote_url()     { return $this->index_url . "/promote"; }
    private function search_url()      { return $this->index_url . "/search"; }
    private function functions_url()   { return $this->index_url . "/functions"; }
    private function function_url($n)  { return $this->index_url . "/functions/". $n; }
    
}

class ApiResponse {
    public $status = NULL;
    public $response = NULL;
    function __construct($status, $response) {
        $this->status = $status;
        $this->response = $response;
    }
}



