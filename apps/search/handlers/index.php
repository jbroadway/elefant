<?php

$appconf = parse_ini_file ('apps/search/conf/config.php', true);

$page->head = '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/jquery-ui.min.js"></script>
<script type="text/javascript" src="/apps/search/js/indextank/jquery.indextank.ize.js"></script>
<script type="text/javascript" src="/apps/search/js/indextank/jquery.indextank.autocomplete.js"></script>
<script type="text/javascript" src="/apps/search/js/indextank/jquery.indextank.ajaxsearch.js"></script>
<script type="text/javascript" src="/apps/search/js/indextank/jquery.indextank.renderer.js"></script>
<script type="text/javascript" src="/apps/search/js/indextank/jquery.indextank.instantsearch.js"></script>
<script type="text/javascript" src="/apps/search/js/indextank/jquery.indextank.basic.js"></script>
<link type="text/css" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/themes/' . $appconf['jQuery']['ui_theme'] . '/jquery-ui.css" media="all" />
<style type="text/css">
.result {
	padding-bottom: 10px;
}
.result a {
	font-weight: bold;
	display: block;
}
</style>
<script>
$(document).ready(function(){
	$("#search-form").indextank_Ize(\'' . $appconf['IndexTank']['public_api_url'] . '\', \'' . $appconf['IndexTank']['index_name'] . '\');
	var renderer =  $("#search-results").indextank_Renderer();
	$("#search-query").indextank_Autocomplete().indextank_AjaxSearch( {listeners: renderer}).indextank_InstantSearch();
});
</script>';

$page->template = 'search/index';

?>