<?php

// Import JSTOR partners into nodes table

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
				$obj->host = preg_replace('/^www\d?\./', '', $obj->host);
			
			}
		
			// print_r($obj);	
			
			
			// SQL
			if (1)
			{
			
				$keys = array();
				$values = array();
			
				$keys[] = 'id';
				$values[] = '"' . str_replace('https://plants.jstor.org/partner/', 'jstor', $obj->JSTOR) . '"';

				$keys[] = 'code';
				$values[] = '"' . $obj->Code . '"';


				$keys[] = 'item_type';
				$values[] = '"herbarium"';


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
						
			
				echo 'REPLACE INTO nodes(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";			
			}

			
		}
	}	
	$row_count++;
}
?>

