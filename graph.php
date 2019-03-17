<?php

// Dump the graph of repositories

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '', 'grbio');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 




echo "graph g {\n";

$sql = 'SELECT * FROM nodes';
$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$id = $result->fields['id'];
	$id = str_replace('%20', '_', $id);

	echo "\t" . $id . " [label=\"" . addcslashes($result->fields['name'], '"') . "\"];\n";

	//echo "\t" . $id . " [label=\"" . $id . "\"];\n";

	$result->MoveNext();	
}


$sql = 'SELECT * FROM edges';
$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$id = $result->fields['id'];
	$id = str_replace('%20', '_', $id);

	echo "\t" . $result->fields['source'] . " -- " . $result->fields['target'] . " [label=\"" . addcslashes($result->fields['reason'], '"') . "\"];\n";
	$result->MoveNext();	
}




echo "}\n";




?>

