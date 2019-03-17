<?php

// Match to Wikidata using reconciliation service

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/wikidata.php');

//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '', 'grbio');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$sql = 'SELECT * FROM nodes WHERE id LIKE "jstor%"';

$sql .= ' AND country="Mexico"';

$sql = 'SELECT * FROM nodes WHERE name LIKE "%normal%"';

$sql = 'SELECT * FROM nodes WHERE name LIKE "%Peru%"';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$record = new stdclass;
	$record->id 		= $result->fields['id'];
	$record->code 		= $result->fields['code'];
	$record->name 		= $result->fields['name'];
	$record->country 	= $result->fields['country'];
	
	
	$text = $record->name;
	
	/*
	if ($record->country != '')
	{
		$text .= ' ' . $record->country;
	}
	*/
	
	// test to find match to parent if we can't match herbarium
	//$text = trim(str_replace('Herbario', '', $text));
	
	
	$type = 'Q181916'; // herbarium
	
	$type = 'Q43229'; // organization
	
	$type = null;
	
	$properties = array();
	
	if (1)
	{	
		// Property value as string
		$property = new stdclass;
		$property->pid = "P17";
		$property->v = $record->country;
		$properties[] = $property;
	}
	
	echo "-- $text\n";
		
	$items = wikidata_reconcile($text, $type, $properties, true);
	
	if (count($items) == 1)
	{
		echo $items[0] . "\n";
	}
	
	
	
	$result->MoveNext();	

}



?>
