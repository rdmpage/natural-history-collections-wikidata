<?php

// Get details on Wikispecies "stub" records in Wikidata

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
	$text = $record->name_ws;
	
	// clean
	
	$text = preg_replace('/\s+\(\d+.*$/u', '', $text);

	$type = 'Q181916'; // herbarium
	
	$type = 'Q43229'; // organization
	
	$type = 'Q167346'; // botanical garden
	
	$type = 'Q33506'; // museum
	
	$type = null;
	
	// try and be clever and guess type from name
	
	if (!$type)
	{
		if (preg_match('/muse(o|um)/i', $text))
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
	
	if (1)
	{
		$items = wikidata_reconcile($text, $type, $properties, false);
		if (count($items) == 1)
		{
			$item = $items[0];
	
			// details
	
			$entity = get_wikidata_entity($item);

			//print_r($entity);
	
			if ($record->id != $entity->id)	
			{
				echo "Candidate for match\n";
				print_r($entity);
			}
		}
	}
	else
	{
	
		$parts = explode(",", $text);
	
		$found = false;
	
		while ((count($parts) > 0) && !$found)
		{
			$query_string = join(' ', $parts);
		
			echo "-- $query_string\n";
		
			$items = wikidata_reconcile($query_string, $type, $properties, false);
			if (count($items) == 1)
			{
				$found = true;
				$item = $items[0];
		
				// details
		
				$entity = get_wikidata_entity($item);
	
				//print_r($entity);
		
				if ($record->id != $entity->id)	
				{
					echo "Candidate for match\n";
					print_r($entity);
				}
			}
			array_pop($parts);
		}
	}
}

//----------------------------------------------------------------------------------------
function ws_record(&$record)
{
	$db = NewADOConnection('mysqli');
	$db->Connect("localhost", 'root' , '' , 'grbio');
	
	// Ensure fields are (only) indexed by column name
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
	$db->EXECUTE("set names 'utf8'"); 

	$sql = 'SELECT * FROM nodes WHERE id="wikispecies' . $record->code . '"';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
	
		if ($result->fields['name'] != '')
		{
			$record->name_ws = $result->fields['name'];
			
			match_record($record);
		}


		$result->MoveNext();	

	}

}

//----------------------------------------------------------------------------------------

// Wikidata records derived from Wikispecies and have acronym as the only label
// get these and see if there are potentially better matches
$sql = 'SELECT * FROM nodes WHERE nodes.id LIKE "Q%" AND nodes.item_type IS null AND nodes.code = nodes.name;';

//$sql = 'SELECT * FROM nodes WHERE nodes.id ="wikispeciesBPBM"';


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
	
	//print_r($record);
	
	ws_record($record);	
	
	
	
	$result->MoveNext();	

}



?>
