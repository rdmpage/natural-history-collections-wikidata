<?php

// Use reconciliation to get possible matcing Wikidata records, and add to nodes and edges

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/wikidata.php');

//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '', 'grbio');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


//----------------------------------------------------------------------------------------
function match_record($record)
{	
	$text = $record->name;
	
	/*
	if (isset($record->additional_name))
	{
		$text .= ' ' . $record->additional_name;
	}
	*/
	
	// look for higher
	/*
	$text = preg_replace('/herbar\w+/ui', '', $text);
	$text = trim($text);
	*/

	$type = 'Q181916'; // herbarium
	
	$type = 'Q43229'; // organization
	
	$type = 'Q167346'; // botanical garden
	
	$type = 'Q33506'; // museum
	
	$type = null;
	
	// try and be clever and guess type from name
	
	if (!$type)
	{
		if (preg_match('/museum/i', $text))
		{
			$type = 'Q33506'; // museum
			//$type = 'Q1970365'; // natural history museum
			//$type = 'Q866133'; // university museum
			//$type = 'Q17431399'; // national museum
		}	
	}

	if (!$type)
	{
		if (preg_match('/herbar/i', $text))
		{
			$type = 'Q181916'; // herbarium
		}	
	}

	if (!$type)
	{
		if (preg_match('/garden/i', $text))
		{
			$type = 'Q167346'; // botanical garden
		}	
	}
	
	if (!$type)
	{
		if (preg_match('/univer/i', $text))
		{
			$type = 'Q3918'; // university
		}	
	}
	
	
	$properties = array();
	
	if (isset($record->country))
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
		$item = $items[0];
		
		// details
		
		$entity = get_wikidata_entity($item);
	
		//print_r($entity);	
	
		// to SQL
		$keys = array();
		$values = array();

		$keys[] = 'id';
		$values[] = '"' . $item . '"';

	
		if (isset($entity->code))
		{
			$keys[] = 'code';
			$values[] = '"' . join(';', $entity->code) . '"';
		}

		if (isset($entity->type))
		{
			$keys[] = 'item_type';
			$values[] = '"' . join(';', $entity->type) . '"';
		}
	
		if (isset($entity->name))
		{
			$keys[] = 'name';
			$values[] = '"' . addcslashes($entity->name['en'], '"') . '"';
		}
	
		if (isset($entity->url))
		{
			$keys[] = 'url';
			$values[] = '"' . $entity->url[0] . '"';
		
			$parts = parse_url($entity->url[0]);
			$host = $parts['host'];
			$host = preg_replace('/^www\./', '', $host);					
		
			$keys[] = 'host';
			$values[] = '"' . $host . '"';
		}
	
		if (isset($entity->isPartOf))
		{
			$keys[] = 'is_part_of';
			$values[] = '"' . join(';', $entity->isPartOf) . '"';
		}

	
		if (isset($entity->sameAs))
		{
			if (preg_match('/species.wikimedia.org/', $entity->sameAs[0]))
			{
				$keys[] = 'wikispecies';
				$values[] = '"' . $entity->sameAs[0] . '"';
			}
		}
	
		if (isset($entity->latitude))
		{
			$keys[] = 'latitude';
			$values[] = $entity->latitude;
		}

		if (isset($entity->longitude))
		{
			$keys[] = 'longitude';
			$values[] = $entity->longitude;
		}
	
	
		// node
		echo 'REPLACE INTO nodes(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";			
		
		// edge
		echo 'REPLACE INTO edges(source, target, reason) VALUES("' . $record->id . '", "' . $item . '", "reconcile");' . "\n";
	}	
}

$sql = 'SELECT * FROM nodes WHERE code="JMC"';

$sql = 'SELECT * FROM nodes WHERE name = "Botanische Staatssammlung München"';


$sql = 'SELECT * FROM nodes WHERE name = "Taiwan Forestry Research Institute"';

$sql = 'SELECT * FROM nodes WHERE name = "South China Botanical Garden"';

$sql = 'SELECT * FROM nodes WHERE name = "Queensland Institute of Medical Research"';

$sql = 'SELECT * FROM nodes WHERE additional_name LIKE "%herbarium%" and country="Australia"';

$sql = 'SELECT * FROM nodes WHERE name LIKE "%idaho%"';

$sql = 'SELECT * FROM nodes WHERE id  LIKE "jstor%"';

$sql = 'SELECT * FROM nodes WHERE code = "AC"';

$sql = 'SELECT * FROM nodes WHERE code = "HULE"';
$sql = 'SELECT * FROM nodes WHERE code = "HUSC"';
$sql = 'SELECT * FROM nodes WHERE code = "CMUH"';
$sql = 'SELECT * FROM nodes WHERE code = "NMBE" AND id NOT LIKE "wikispecies%" AND id NOT LIKE "Q%"';

$sql = 'SELECT * FROM nodes WHERE id  LIKE "jstor%"';



$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$record = new stdclass;
	$record->id 		= $result->fields['id'];
	$record->code 		= $result->fields['code'];
	$record->name 		= $result->fields['name'];
		
	if ($result->fields['country'] != '')
	{
		$record->country 	= $result->fields['country'];
	}

	if ($result->fields['additional_name'] != '')
	{
		$record->additional_name 	= $result->fields['additional_name'];
	}
	
	match_record($record);	
	
	$result->MoveNext();	

}



?>
