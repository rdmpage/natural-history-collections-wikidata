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

if (0)
{
	// Get codes which have > 1 repository
	$sql = 'SELECT code, COUNT(code) AS c FROM nodes GROUP BY code';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		if ($result->fields['c'] > 1)
		{
			$codes[] = $result->fields['code'];
		}
		$result->MoveNext();	
	}
}

if (0)
{
	// Get codes from a country
	$country = 'Argentina';
	$sql = 'SELECT code FROM nodes WHERE country="' . $country . '"';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$codes[] = $result->fields['code'];
		$result->MoveNext();	
	}


}

$codes = array('WAG');

print_r($codes);

// naive grouping

foreach ($codes as $code)
{
	$sql = 'SELECT * FROM nodes WHERE code="' . $code . '"';
	
	//$sql .= ' AND item_type="herbarium"';
	
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
		echo 'REPLACE INTO edges(source, target, reason) VALUES("' . $ids[0] . '", "' . $ids[$i] . '", "same code");' . "\n";
	}
	

}

?>

