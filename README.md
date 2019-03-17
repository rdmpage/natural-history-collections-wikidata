# natural-history-collections-wikidata

Natural history collections linked to Wikidata

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


## Wikidata notes

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

## Images

Wikispecies logo 

https://commons.wikimedia.org/wiki/File:WikiSpecies_black.svg
This file is licensed under the Creative Commons Attribution-Share Alike 3.0 Unported license.


## JSTOR

https://labstest.jstor.org/zambezi/



