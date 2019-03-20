<?php

// Compute components for global graph of repositories
// Components are based on entries in edges table.

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '', 'grbio');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

//----------------------------------------------------------------------------------------
// Disjoint-set data structure


$sets = array();

function makeset($label) {

	$x = new stdclass;	
	$x->label = $label;
	$x->parent = $x;
	
	return $x;
}

function find($x) {
	if ($x != $x->parent) {
		$x->parent = find($x->parent);
	}
	return $x->parent;
		
}

function union($x, $y) {	
	$x_root = find($x);
	$y_root = find($y);
	$x_root->parent = $y_root;	
}

//----------------------------------------------------------------------------------------

// nodes

$sql = 'SELECT * FROM edges';

//$sql .= ' WHERE source IN("jstorTEX", "jstorLL", "jstorBRI")';


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{	
	$source = $result->fields['source'];
	$target = $result->fields['target'];
	
	if (!isset($sets[$source]))
	{
		$sets[$source] = makeset($source);
	}

	if (!isset($sets[$target]))
	{
		$sets[$target] = makeset($target);
	}
	$result->MoveNext();	
}

echo "Makeset\n";
print_r($parents);

// edges

$sql = 'SELECT * FROM edges';

//$sql .= ' WHERE source IN("jstorTEX", "jstorLL", "jstorBRI")';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$source = $result->fields['source'];
	$target = $result->fields['target'];
	
	echo "$source -- $target\n";
	
	union($sets[$source], $sets[$target]);
	
	$result->MoveNext();	
}

echo "added edges\n";

// compute components

$components = array();

foreach ($sets as $label => $x)
{
	$p = find($x);
	echo $label . " -> ";
	echo $p->label . "\n";
}








?>

