# natural-history-collections-wikidata

Natural history collections linked to Wikidata

## Twitter,blogs, and other conversations

https://twitter.com/dpsSpiders/status/1108752958106279936

https://iphylo.blogspot.com/2016/08/grbio-call-for-community-curation-what.html

https://discourse.gbif.org/t/collections-catalogue-grbio/688

## Quickstatements

https://tools.wmflabs.org/quickstatements

## NCBI

Find repositories by acronym

https://www.ncbi.nlm.nih.gov/biocollections/?term=MCZ

Collections in a repository (281 = MCZ)

https://www.ncbi.nlm.nih.gov/biocollections/281

Find sequences from material in a collection (= 281)

https://www.ncbi.nlm.nih.gov/nuccore?term="collection_281"[Properties]


## Other examples

https://www.idigbio.org/content/shining-new-light-world’s-collections 

https://www.idigbio.org/portal/collections

https://www.tdwg.org/community/cd/

## Wikidata queries

### Find institution codes from Ringgold or grid

```
SELECT DISTINCT * 
WHERE 
{ 
  VALUES ?identifier {"grid.4903.e"}  
  {
    # institution that includes collection has grid or ringgold
    ?institution wdt:P3500|wdt:P2427 ?identifier .
    # various part of relationships
    ?collection wdt:P195|wdt:P137|wdt:P749|wdt:P361 ?institution .
  }
  UNION
  {
    # collection itself has grid or ringgold
    ?collection wdt:P3500|wdt:P2427 ?identifier .
  }
    
  # Code(s) for collection
  {
    # Index Herb. or Biodiv Repo ID
    ?collection wdt:P5858|wdt:P4090 ?code .
  }
  UNION
  {
    # Derive from Wikispecies URL
    ?wikispecies schema:about ?collection .
    BIND( REPLACE( STR(?wikispecies),"https://species.wikimedia.org/wiki/","" ) AS ?code). 
    FILTER contains (STR(?wikispecies),'species.wikimedia.org')
  }
}



```


## Rethink

OK, we need to think about this more clearly. Model as a graph where nodes are records from all the source datasets (JSTOR, NCBI, Wikidata, GrBio, etc.). Edges are matches between nodes based on various criteria (e.g., shared code, reconciliation with Wikidata, matching URLs, etc.). Get components of graph to get clusters corresponding to repositories. Need to handle cases where cluster may include > 1 repository (e.g., duplicate codes), and cases where we have multiple Wikidata records for same thing (e.g., Wikispecies page for code has a Wikidata page and so does the repository). May need to think about maximum weighted matching, etc. within a component.

So, first step is to import all data into a ```nodes``` table where id is unique id of record in source datasets. Then we apply various techniques to identify possible matches, such as adding a edge between JSTOR and GrBio records with same IH code, between JSTOR/GrBio and IH codes in Wikidata, between matches based on reconciling with Wikidata, etc.

Filtering for possible errors could include counting namespaces in each cluster. Each component should only have one record from each namespace, having multiple records from same namespace (e.g., more than one GrBio record, more than one Wikidata record) is a flag that we have a problem (although could also occur when we have codes that have been merged into one institution over time).


## GrBio

Main site dead, but developmental version still live at http://dev.grbio.org

## ASIH codes

http://asih.org/standard-symbolic-codes/about-symbolic-codes, Excel file http://symbiont.ansp.org/ixingu/library/Symbolic_Codes_for_Collections_v6.5_2016.xls


## Reconciliation via Wikidata

See https://github.com/OpenRefine/OpenRefine/wiki/Reconciliation-Service-API


## Wikidata notes and problematic examples

### Handling multiple collection codes

#### MO and UMO

https://www.wikidata.org/wiki/Q55829201

UMO
start time
2016
stated as
Dunn-Palmer Herbarium, University of Missouri
has cause
merge


### Bogor Zoological Museum

https://species.wikimedia.org/wiki/BZM

- BZM
- IPBD (Institute for Plant Diseases, Buitenzorg, Java)
- MZB (Museum Zoologicum Bogoriense/Museum Zoologi Bogor, Cibinong, Java, Indonesia)
- ZMBJ (Zoologische Museum Buitenzorg, Java)
- MBBJ (Museum Bogoriense, Bogor, Java)

See also:
 
Robert Holmberg 1998. Museum Zoologicum Bogoriense (MZB), Indonesia. Newsletter of the American Arachnological Society, April 1998, 57:10 http://hdl.handle.net/2149/1320 https://auspace.athabascau.ca/bitstream/handle/2149/1320/MuseumZoologicum.pdf?sequence=1&isAllowed=y

## Images

Wikispecies logo 

https://commons.wikimedia.org/wiki/File:WikiSpecies_black.svg
This file is licensed under the Creative Commons Attribution-Share Alike 3.0 Unported license.


## JSTOR

https://labstest.jstor.org/zambezi/

## Publications about collections

Could add publication on a collection to Wikidata (if not already there) and make the collection the subject of that publication.

### Example publications

Edição Especial Herbários do Brasil - 66º Congresso Nacional de Botânica published by: UNISANTA http://periodicos.unisanta.br/index.php/bio/issue/view/53/showToc





