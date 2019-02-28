<?php

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/wikidata.php');

//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '', 'grbio');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$sql = 'SELECT * FROM collections WHERE id LIKE "https://plants.jstor.org/partner/%" AND wikidata IS NULL';

$sql .= ' AND country="Japan"';

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
	$type = 'Q181916'; // herbarium
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
		
	$items = wikidata_reconcile($text, $type, $properties);
	
	if (count($items) == 1)
	{
		echo $items[0] . "\n";
	}
	
	
	
	$result->MoveNext();	

}



?>
