<?php

// Match nodes based on shared Internet domains (risky)

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '', 'grbio');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$hosts = array();


if (1)
{
	// Get codes from a country
	$country = 'Argentina';
	$country = 'Mexico';
	
	
	$sql = 'SELECT host FROM nodes WHERE host IS NOT NULL AND host <> "sweetgum.nybg.org" AND country="' . $country . '"';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$hosts[] = $result->fields['host'];
		$result->MoveNext();	
	}


}

print_r($hosts);

// naive grouping

foreach ($hosts as $host)
{
	$sql = 'SELECT * FROM nodes WHERE host="' . $host . '"';
	
	$ids = array();
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$id = str_replace('%20', '_', $result->fields['id']);
		$ids[] = $id;
		$result->MoveNext();	
	}
	
	$n = count($ids);
	
	for ($i = 1; $i < $n; $i++)
	{
		echo 'REPLACE INTO edges(source, target, reason) VALUES("' . $ids[0] . '", "' . $ids[$i] . '", "same host");' . "\n";
	}
	

}

?>

