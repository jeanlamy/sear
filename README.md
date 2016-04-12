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

* Complete index mapping "au fur et à mesure".
* Add pagination logic to search results
* Add categories navigation to search results
* Add product modal with all informations
* Allow user to search product by entering an additive or ingredient name


* See how to use it with docker




