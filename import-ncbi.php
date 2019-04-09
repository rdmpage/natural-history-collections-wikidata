<?php

// Import ncbi into nodes table

//----------------------------------------------------------------------------------------
// http://stackoverflow.com/a/5996888/9684
function translate_quoted($string) {
  $search  = array("\\t", "\\n", "\\r");
  $replace = array( "\t",  "\n",  "\r");
  return str_replace($search, $replace, $string);
}

//----------------------------------------------------------------------------------------

$filename = 'ncbi/Institution_codes.tsv';

$headings = array();

$row_count = 0;

$file = @fopen($filename, "r") or die("couldn't open $filename");
		
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = fgetcsv(
		$file_handle, 
		0, 
		translate_quoted('\t'),
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
		
			// print_r($obj);	

/*
	[inst_id] => 8612
    [inst_code] => HAG
    [inst_name] => Universidad Nacional Amazonica de Madre de Dios (UNAMAD)
    [country] => Peru
    [address] => Av. Jorge Chavez 1160 Puerto Maldonado, Madre de Dios /Tambopata 17001  
    [home_url] => http://investigacion.unamad.edu.pe/
    [collection_type] => herbarium
    [qualifier_type] => specimen_voucher
    [unique_name] => HAG
*/

			$entity = new stdclass;
			
			$address = array();
			
			foreach ($obj as $k => $v)			
			{
				if ($v != '')
				{
					switch ($k)
					{
						case 'inst_id':
							$entity->id = $v;
							$entity->id = 'ncbi' . $entity->id;
							break;
								
						case 'inst_code':
							$entity->code = $v;
							break;
							
						case 'collection_type':
							$entity->item_type = strtolower($v);
							break;

						case 'inst_name':
							$entity->name = $v;
							break;

						case 'home_url':
							$entity->url = $v;							
							
							if (!preg_match('/^https?:\/\//', $entity->url))
							{
								$entity->url = 'http://' . $entity->url;
							}
				
							$parts = parse_url($entity->url);
							$entity->host = $parts['host'];
							$entity->host = preg_replace('/^www\d?\./', '', $entity->host);					
							break;
													
						case 'country':
							$entity->country = $v;
							break;
							
						case 'address':
							$entity->address = $v;
							break;
							
						case 'unique_name':
							$entity->unique_code = $v;
							break;

						case 'aka':
							$entity->alias = $v;
							break;

						case 'url_rule':
							$entity->url_rule = $v;
							break;
			
						default:
							break;
					}			
				}
			}
			
			// print_r($entity);
			
			if (count($address) > 0)
			{
				$entity->address = join(', ', $address);
				//print_r($entity);
				//exit();
			}
			
			
			// SQL
			if (1)
			{
			
				$keys = array();
				$values = array();
			
				foreach ($entity as $k => $v)					
				{
					$keys[] = $k;
					$values[] = '"' . addcslashes($v, '"') . '"';
				}
			
				echo 'REPLACE INTO nodes(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";			
			}

			
		}
	}	
	$row_count++;
}
?>

