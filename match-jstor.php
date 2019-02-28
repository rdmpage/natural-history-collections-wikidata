<?php

// Parse JSTOR list of partners and match to Wikidata

require_once(dirname(__FILE__) . '/wikidata.php');

//----------------------------------------------------------------------------------------
// http://stackoverflow.com/a/5996888/9684
function translate_quoted($string) {
  $search  = array("\\t", "\\n", "\\r");
  $replace = array( "\t",  "\n",  "\r");
  return str_replace($search, $replace, $string);
}

//----------------------------------------------------------------------------------------

$filename = 'jstor/partners.tsv';
//$filename = 'jstor/test.tsv';

$headings = array();

$row_count = 0;

$file = @fopen($filename, "r") or die("couldn't open $filename");
		
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = fgetcsv(
		$file_handle, 
		0, 
		translate_quoted("\t"),
		translate_quoted('"') 
		);
		
	$go = is_array($row);
	
	if ($go)
	{
		if ($row_count == 0)
		{
			$headings = $row;		
		}
		else
		{
			$obj = new stdclass;
		
			foreach ($row as $k => $v)
			{
				if ($v != '')
				{
					if ($headings[$k] == 'Name')
					{
						$v = str_replace(' (' . $obj->Code . ')', '', $v);
					}
					
					$obj->{$headings[$k]} = $v;
				}
			}
		
			if (isset($obj->URL))
			{
				$parts = parse_url($obj->URL);
				
				$obj->host = $parts['host'];
				$obj->host = preg_replace('/^www\./', '', $obj->host);
			
			}
		
			//print_r($obj);	
			
			
			// SQL
			if (0)
			{
			
				$keys = array();
				$values = array();
			
				$keys[] = 'id';
				$values[] = '"' . $obj->JSTOR . '"';

				$keys[] = 'cluster_id';
				$values[] = '"' . $obj->JSTOR . '"';

				$keys[] = 'code';
				$values[] = '"' . $obj->Code . '"';

				$keys[] = 'name';
				$values[] = '"' . $obj->Name . '"';
			
				if (isset($obj->Country))
				{
					$keys[] = 'country';
					$values[] = '"' . $obj->Country . '"';
				}

				if (isset($obj->URL))
				{
					$keys[] = 'url';
					$values[] = '"' . $obj->URL . '"';
				}

				if (isset($obj->host))
				{
					$keys[] = 'host';
					$values[] = '"' . $obj->host . '"';
				}
						
			
				echo 'REPLACE INTO collections(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";			
			}
			
			
			
			
			
			
			$item = '';
			
			
			$mode = 1;
			
			if ($mode == 0)
			{			
				// Try and match to herbarium code
				$item = wikidata_herbarium_from_code($obj->Code);
			}
			
			if ($mode == 1)
			{			
				// Try and match to wikispecies code
				$item = wikidata_item_from_wikispecies_repository($obj->Code);
			}
			
			
			/*
			// Reconcile
			$text = $obj->Name;
			$type = 'Q181916';
			$type = null;
			wikidata_reconcile($text, $type);
			*/
			
			
			if ($item != '')
			{
				//echo "Wikidata=$item\n";
				
				if ($mode == 0)
				{
					// match to wikidata item using identifier
					echo 'UPDATE collections SET wikidata="' . $item . '", wikidata_match_reason="IH code" WHERE id="' . $obj->JSTOR . '";' . "\n";
				}
				
				if ($mode == 1)
				{
					// match to Wikispecies code (maybe item already exists but not linked to code?)
					echo 'UPDATE collections SET wikidata="' . $item . '", wikidata_match_reason="Wikispecies code" WHERE id="' . $obj->JSTOR . '";' . "\n";
				}
			
			
				// Get details of Wikidata entity and add that to database
				$entity = get_wikidata_entity($item);
				
				//print_r($entity);
				
				
				// to SQL
				$keys = array();
				$values = array();
			
				$keys[] = 'id';
				$values[] = '"' . $item . '"';

				$keys[] = 'cluster_id';
				$values[] = '"' . $item . '"';

				$keys[] = 'wikidata';
				$values[] = '"' . $item . '"';
				
				if (isset($entity->code))
				{
					$keys[] = 'code';
					$values[] = '"' . join(';', $entity->code) . '"';
				}

				if (isset($entity->type))
				{
					$keys[] = '_type';
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
					$keys[] = 'wikidata_parent';
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
				
				echo 'REPLACE INTO collections(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";			
			}
			
		}
	}	
	$row_count++;
}
?>

