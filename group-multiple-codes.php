<?php

// Match nodes

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '', 'grbio');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$codes = array();



if (1)
{
	// cases where there are multiple codes
	$sql = 'SELECT code FROM nodes WHERE code LIKE "%;%"';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$codes[] = $result->fields['code'];
		
		$result->MoveNext();	
	}
	
	$codes = array_unique($codes);


}

print_r($codes);

// naive grouping

foreach ($codes as $code)
{
	$individual_codes = explode(';', $code);
	
	foreach ($individual_codes as $c)
	{
		$sql = 'SELECT * FROM nodes WHERE code = "' . $c . '" OR code = "' . $code . '"';
	
		//echo $sql . "\n";
	
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
			echo 'REPLACE INTO edges(source, target, reason) VALUES("' . $ids[0] . '", "' . $ids[$i] . '", "code intersection");' . "\n";
		}
	}	

}

?>

