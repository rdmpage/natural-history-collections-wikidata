<?php

// Get Wikidata record that matches a repository code

require_once(dirname(__FILE__) . '/wikidata.php');


$terms = array('SING');

$terms = array('http://www.pref.kyoto.jp/plant/');

$terms = array(
	'http://www.hunnu.edu.cn',
	'http://www.hunnu.edu.cn/',
	'http://www.swu.edu.cn/',
	'http://www.swust.edu.cn/',
	'http://www.ynu.edu.cn/'
	
);

// Natural History Museums in Wikidata	
// SELECT * WHERE { ?repository wdt:P31 wd:Q1970365 }

$terms = array(

'Q422',
'Q116998',
'Q122945',
'Q148554',
'Q217717',
'Q220270',
'Q222297',
'Q233098',
'Q282749',
'Q309388',
'Q376739',
'Q388376',
'Q525190',
'Q663025',
'Q688704',
'Q706441',
'Q778686',
'Q779703',
'Q838691',
'Q846583',
'Q848411',
'Q965731',
'Q978464',
'Q1032232',
'Q1043983',
'Q1111807',
'Q1275772',
'Q1282056',
'Q1286799',
'Q1312268',
'Q1420103',
'Q1420782',
'Q1465387',
'Q1533416',
'Q1545745',
'Q1582567',
'Q1663690',
'Q1684512',
'Q1749600',
'Q1760005',
'Q1771439',
'Q1788142',
'Q1799158',
'Q1840963',
'Q1876154',
'Q1954475',
'Q1954759',
'Q1954786',
'Q1966699',
'Q1970000',
'Q1970332',
'Q1970402',
'Q1970407',
'Q2036255',
'Q2046672',
'Q2086562',
'Q2087619',
'Q2269936',
'Q2324612',
'Q2502709',
'Q2822400',
'Q2944171',
'Q3014352',
'Q3074272',
'Q3112812',
'Q3247157',
'Q3328400',
'Q3328425',
'Q3329065',
'Q3329546',
'Q3329689',
'Q3330050',
'Q3330052',
'Q3330830',
'Q3330860',
'Q3330862',
'Q3330865',
'Q3330867',
'Q3330869',
'Q3330870',
'Q3330871',
'Q3330874',
'Q3330875',
'Q3330877',
'Q3330880',
'Q3330885',
'Q3330888',
'Q3330889',
'Q3330890',
'Q3330892',
'Q3330896',
'Q3330897',
'Q3330898',
'Q3432011',
'Q3813327',
'Q3816595',
'Q3867830',
'Q3867834',
'Q3868230',
'Q3915972',
'Q24259331',
'Q26255818',
'Q28221511',
'Q28222005',
'Q28222180',
'Q28451570',
'Q30142691',
'Q30682712',
'Q30728500',
'Q31085951',
'Q35998685',
'Q36000768',
'Q36003676',
'Q38317068',
'Q39048101',
'Q46909633',
'Q47068072',
'Q47110890',
'Q47110906',
'Q47163979',
'Q47477237',
'Q48002743',
'Q52158006',
'Q53880243',
'Q56402898',
'Q56641090',
'Q57337530',
'Q58051025',
'Q58636577',
'Q58636607',
'Q58636608',
'Q58670041',
'Q59331959',
'Q59359897',
'Q60533061',
'Q61745699',
'Q4121358',
'Q4155883',
'Q4193904',
'Q4868941',
'Q4883219',
'Q4892280',
'Q4948003',
'Q4985458',
'Q5001268',
'Q5253320',
'Q5278627',
'Q5430021',
'Q5445032',
'Q5509457',
'Q5518996',
'Q5535310',
'Q5572022',
'Q5588226',
'Q5596372',
'Q5610644',
'Q5647057',
'Q5755315',
'Q5987387',
'Q5999587',
'Q6033655',
'Q6034168',
'Q6088683',
'Q6101933',
'Q6388857',
'Q6395279',
'Q6492605',
'Q6583114',
'Q6735528',
'Q6940852',
'Q6940980',
'Q6941080',
'Q6941084',
'Q6974486',
'Q6974487',
'Q6980515',
'Q6980518',
'Q6980520',
'Q6980521',
'Q6980528',
'Q7205781',
'Q7235303',
'Q7271593',
'Q7323031',
'Q7445283',
'Q7451507',
'Q7525146',
'Q7569537',
'Q7639734',
'Q7852186',
'Q7859080',
'Q7895767',
'Q8366704',
'Q9046889',
'Q10333589',
'Q11786999',
'Q11828170',
'Q11854506',
'Q11868298',
'Q11870293',
'Q12062222',
'Q12063591',
'Q12291458',
'Q12292252',
'Q12328428',
'Q12328429',
'Q12361299',
'Q12499644',
'Q12881259',
'Q13018177',
'Q13645742',
'Q14681600',
'Q15222070',
'Q15958804',
'Q16467783',
'Q16665658',
'Q16826292',
'Q16854808',
'Q16948742',
'Q16998098',
'Q17020278',
'Q17099134',
'Q17488243',
'Q18005082',
'Q18201907',
'Q18346796',
'Q19704676',
'Q19840336',
'Q20443735',
'Q20987445',
'Q20988467',
'Q21493730',
'Q22073078',
'Q22073346',
'Q22812110',
'Q23021636',

);

$mode = 2;


foreach ($terms as $term)
{
	$item = '';

	if ($mode == 0)
	{			
		// Try and match to herbarium code
		$item = wikidata_herbarium_from_code($term);
	}

	if ($mode == 1)
	{			
		// Try and match to wikispecies code
		$item = wikidata_item_from_wikispecies_repository($term);
	}

	// Direct import
	if ($mode == 2)
	{
		$item = $term;
	}


	if ($mode == 3)
	{
		$item = wikidata_item_from_url($term);
	}

	if ($item != '')
	{
		// Get details of Wikidata entity and add that to database
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
			$name = '';
			
			// English by default...
			if (isset($entity->name['en']))
			{
				$name = $entity->name['en'];
			}
			else
			{
				$name = '';
				foreach ($entity->name as $lang => $value)
				{
					if ($name == '')
					{
						$name = $value;
					}
				}
				
			}
			
			$keys[] = 'name';			
			$values[] = '"' . addcslashes($name, '"') . '"';			
			
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
		

	
		if (isset($entity->sameAs))
		{
			if (preg_match('/species.wikimedia.org/', $entity->sameAs[0]))
			{
				$keys[] = 'wikispecies';
				$values[] = '"' . $entity->sameAs[0] . '"';
			}
		}
	
	
		echo 'REPLACE INTO nodes(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";			
	}
}

?>
