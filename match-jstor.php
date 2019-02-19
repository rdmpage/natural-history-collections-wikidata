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
					$obj->{$headings[$k]} = $v;
				}
			}
		
			print_r($obj);	
			
			$item = '';
			
			// Try and match via Wikispecies repository code
			//$item = wikidata_item_from_wikispecies_repository($obj->Code);
			
			// Try and match to herbarium code
			//$item = wikidata_herbarium_from_code($obj->Code);
			
			// Reconcile
			$text = $obj->Name;
			$type = 'Q181916';
			wikidata_reconcile($text, $type);
			
			if ($item != '')
			{
				echo "Wikidata=$item\n";
			
				$entity = get_wikidata_entity($item);
				print_r($entity);
			}
		}
	}	
	$row_count++;
}
?>

