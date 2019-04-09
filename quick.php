<?php

// Export mapping(s) to Wikidata

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '', 'grbio');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

// get a cluster

$cluster = new stdclass;
$cluster->members = array();
$cluster->types = array();

$cluster->ih_code = array();
$cluster->code = array();


$sql = 'SELECT * FROM nodes WHERE cluster_id="Q5946290"';


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{	
	$obj = new stdclass;
	
	$obj->id = $result->fields['id'];
	
	if ($result->fields['code'] != '')
	{
		$obj->code = $result->fields['code'];
		
		if ($result->fields['item_type'] == 'herbarium')
		{
			$cluster->ih_code = array_merge($cluster->ih_code, array($obj->code));
		}
		else
		{
			$cluster->code = array_merge($cluster->code, array($obj->code));
		}
	}

	if ($result->fields['unique_code'] != '')
	{
		$obj->unique_code = $result->fields['unique_code'];
		
		$cluster->code = array_merge($cluster->code, array($obj->unique_code));
	}
	
	if ($result->fields['item_type'] != '')
	{
		$obj->type = preg_split('/;\s*/', $result->fields['item_type']);
		
		$cluster->types = array_merge($cluster->types, $obj->type);
	}		

	if ($result->fields['country'] != '')
	{
		$obj->country = $result->fields['country'];
	}

	if ($result->fields['name'] != '')
	{
		$obj->name = $result->fields['name'];
	}
	
	// extended name?
	if ($result->fields['additional_name'] != '')
	{
		$obj->name .= ' ' . $result->fields['additional_name'];
	}
	
	if ($result->fields['wikispecies'] != '')
	{
		$obj->wikispecies = $result->fields['wikispecies'];
	}

	if ($result->fields['host'] != '')
	{
		$obj->host = $result->fields['host'];
	}
	
	if ($result->fields['address'] != '')
	{
		$obj->address = $result->fields['address'];
	}	

	if ($result->fields['latitude'] != '')
	{
		$obj->latitude = $result->fields['latitude'];
	}	

	if ($result->fields['longitude'] != '')
	{
		$obj->longitude = $result->fields['longitude'];
	}	
	


	$cluster->members[$obj->id] = $obj;

	$result->MoveNext();	
}

// clean
$cluster->code = array_unique($cluster->code);
$cluster->ih_code = array_unique($cluster->ih_code);
$cluster->unique_code = array_unique($cluster->unique_code);

// sanity checks

print_r($cluster);

$statements = array();

foreach ($cluster->members as $k => $v)
{
	// Wikidata item?
	if (preg_match('/^Q\d+/', $k))
	{
		// Doesn't have a code
		if (!isset($v->code))
		{
			// Add IH code if we have one (or more)
			if (count($cluster->ih_code) > 0)
			{
				foreach ($cluster->ih_code as $code)
				{			
					$statements[] = array($k, 'P5858', '"' . $code . '"');
				}			
			}
			
			// Biodiversity repository code
			if (count($cluster->code) > 0)
			{
				foreach ($cluster->code as $code)
				{			
					$statements[] = array($k, 'P4090', '"' . $code . '"');
				}			
			}
			
		}
	}
}

print_r($statements);

foreach ($statements as $st)
{
	echo join("\t", $st) . "\n";
}

echo "\n";






?>
