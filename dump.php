<?php

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '', 'grbio');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$sql = 'SELECT * FROM collections WHERE id LIKE "https://plants.jstor.org/partner/%"';

$countries = array();

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$record = new stdclass;
	$record->id 		= $result->fields['id'];
	$record->code 		= $result->fields['code'];
	$record->name 		= $result->fields['name'];
	$record->country 	= $result->fields['country'];
	
	if ($result->fields['wikidata'] != '')
	{
		$record->wikidata 	= $result->fields['wikidata'];
	}
	
	if (!isset($countries[$record->country]))
	{
		$countries[$record->country] = array();
	}
	
	$countries[$record->country][] = $record;
	$result->MoveNext();	

}


//print_r($countries);

echo "<html>\n";
echo '<body style="font-family:sans-serif;font-size:1em;">';

echo '<table border="0">';
echo '<tbody style="font-size:0.8em;">';

foreach ($countries as $country => $collections)
{
	echo '<tr><td colspan="2">' . '<br/>' . '<b>' . $country . '</b>' . '</td></tr>';
	
	foreach ($collections as $collection)
	{
		echo '<tr>';
		
		echo '<td>';
		echo $collection->name . ' (' . $collection->code . ')';		
		echo '</td>';
		
		if (isset($collection->wikidata))
		{
			echo '<td>';
			echo '<a href="http://wikidata.org/wiki/' . $collection->wikidata . '" target=_new">' . $collection->wikidata . '</a>';		
			echo '</td>';		
		}
		else
		{
			echo '<td align="right"></td>';
		}
		
		
		echo '</tr>';
	}
}

echo '</tbody>';
echo '</table>';

echo "</body>\n";
echo "</html>\n";


?>

