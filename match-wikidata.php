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

$sql = 'SELECT * FROM nodes WHERE code="JMC"';


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$text = $result->fields['name'];

	$type = null;
	$properties = array();

	if (1)
	{	
		// Property value as string
		$property = new stdclass;
		$property->pid = "P17";
		$property->v = $result->fields['country'];
		$properties[] = $property;
	}
	
	//echo $text . "\n";
		
	$items = wikidata_reconcile($text, $type, $properties);
	
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
	
		// node
		echo 'REPLACE INTO nodes(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";			
		
		// edge
		echo 'REPLACE INTO edges(source, target, reason) VALUES("' . $result->fields['id'] . '", "' . $item . '", "reconcile");' . "\n";

		
		
	}	
	
	
	$result->MoveNext();	

}



?>
