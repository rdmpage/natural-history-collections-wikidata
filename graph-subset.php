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



$nodes = array();
$extra_nodes = array();

// get a selection of nodes
$sql = 'SELECT * FROM nodes WHERE code="SING"';

$sql = 'SELECT * FROM nodes WHERE country="Singapore"';
$sql = 'SELECT * FROM nodes WHERE country="Mexico"';
$sql = 'SELECT * FROM nodes WHERE country="Argentina"';
$sql = 'SELECT * FROM nodes WHERE country="France"';



$nodes_dot = array();

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$id = $result->fields['id'];
	$id = str_replace('%20', '_', $id);

	$nodes_dot[] = $id . " [label=\"" . addcslashes($result->fields['name'], '"') . "\", shape=box];\n";

	$nodes[] = $id;

	$result->MoveNext();	
}


$edges_dot = array();

$sql = 'SELECT DISTINCT * FROM edges WHERE source IN ("' . join('","', $nodes) . '") OR target IN ("' . join('","', $nodes) . '")';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$id = $result->fields['id'];
	$id = str_replace('%20', '_', $id);

	$edges_dot[] = $result->fields['source'] . " -- " . $result->fields['target'] . " [label=\"" . addcslashes($result->fields['reason'], '"') . "\"];\n";
	
	if (!in_array($result->fields['source'], $nodes))
	{
		$extra_nodes[] = $result->fields['source'];
	}
	if (!in_array($result->fields['target'], $nodes))
	{
		$extra_nodes[] = $result->fields['target'];
	}
	
	
	$result->MoveNext();	
}

foreach ($extra_nodes as $extra_id)
{
	$sql = 'SELECT * FROM nodes WHERE id="' . $extra_id . '"';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$id = $result->fields['id'];
		$id = str_replace('%20', '_', $id);

		$nodes_dot[] = $id . " [label=\"" . addcslashes($result->fields['name'], '"') . "\", shape=box];\n";
		$result->MoveNext();	
	}	
	
}


echo "graph g {\n";

echo join('', $nodes_dot);
echo join('', $edges_dot);


echo "}\n";





?>

