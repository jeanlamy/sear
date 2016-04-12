# sear
Search.io : search engine for products (experimentation)

### Why?

To make experiment over technologies like ElasticSearch, Symfony 3, HHVM, Nginx using Docker.

### What can I do with it ?

Search products information from the OpenFoodFacts database from various angles : product name, ingredient, additive,...

<<<<<<< HEAD
### Usage of OpenFoodFacts database

Elastic Indexation is based on the CSV files from OpenFoodFacts.org : 
  http://fr.openfoodfacts.org/data/fr.openfoodfacts.org.products.csv
Field Mapping can be found here : 
  http://world.openfoodfacts.org/data/data-fields.txt


### How can I run it?

```bash
  # create index
  php bin/console sear:elastic create
  # launch indexation
  php bin/console sear:elastic index
  # delete index
  php bin/console sear:elastic delete
```

### Todo

* Add index creation in ElasticSearch Service.
* Add index deletion in ElasticSearch Service.
* Set index mapping for search suggestions.
* Complete index mapping "au fur et à mesure".
* Go on on search results.
* Write a how to use.
* See how to use with Docker.




