## Mise en place de la BDD
```shell
# Création de la base de donnée :
symfony console d:d:c
```
```shell
# Éxécution des migrations
symfony console d:m:m 
```
```shell
# Importation données
symfony console app:import-data
```