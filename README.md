# dmw1

## INSTALLATION
#### Installer wsl2
```bash
wsl -l -o #pour voir les distributions
wsl --install -d Ubuntu-20.04
```
> Si erreur update windows ou activer virtualisation BIOS ou faire installation manuelle

#### Docker

On peut build docker à partir d'une url github.
`docker build https://github.com/ldandoy/sf-dwm-23`

Sinon on peut nous mêmes écrire le docker-composer.yml et le placer dans le dossier du projet. Créer un dossier Php dans lequel on met le Dockerfile.
`docker build php/Dockerfile`

#### Run docker
Ouvrir le fichier yml dans le terminal intégré et faire la commande:
```bash
docker-compose up -d
```
> -d, detach mode est pour liberer la console et que la bdd demarre en arriere plan

#### Execute PHP
Sur Docker, ouvrir le container php dans le terminal et faire la commande:
```bash
docker-compose exec php bash
```
> note: le ficher app doit etre vide

Puis executer la commande pour isntaller symphony
```bash
symfony new . --webapp
```

>note: sur docker on peut ouvrir le container nginx dans le browser, il redirige vers localhost.

#### GitIgnore
Certains fichier ne doivent pas apparaitre dans GIT.
- mysql
- var
- vendor

## UTILISATION

Tout se passe dans app/config, app/src et app/template.

#### Controllers & Templates
On utiliser les attributs plutôt que les YAML. De la forme /controller/fonction (/lucky/number)
- dans app/src Controller, créer le fichier php controller de la page/API
- dans app.src/templates, créer le html.twig de la template

Les templates sont des vues et ne contiennent pas des appels aux bases de données ou calculs, les templates sont normalement déjà ordonnées etc. Le reste, plus poussé est fait directement dans le container php

[Symphony Doc Generating Controller - CRUD](https://symfony.com/doc/current/controller.html#generating-controllers)

#### Databases
Installer doctrine avec les commandes:
```bash
composer require symfony/orm-pack
composer require --dev symfony/maker-bundle
```
Dans app/.env, setup up la base de donnée:
`DATABASE_URL=mysql://root:@db_name:3306/db_name?serverVersion=5.7`

Puis créer la db sur phpmyadmin avec la commande:
`php bin/console doctrine:database:create` ou `php bin/console d:d:c` 

Notre db à déja été créer lors du build avec docker.

>note: toutes les commandes doivent être faites dans le container php de docker

#### Créer une entité

[Symphony Doc Creating an Entity Class](https://symfony.com/doc/current/doctrine.html#creating-an-entity-class)

Create the Entity (a table) and push it to the database with the command:

```bash
php bin/console make:migration
```
>note: toutes les commandes doivent être faites dans le container php de docker

The previous command prepared the commit, to execute the commit, use the following command:
```bash
php bin/console doctrine:migrations:migrate
```

You can also use a php command to create an Entity instead of writing it yourself, which also includes the "namespace", "use" and setters and getters automatically:

```bash
php bin/console make:entity
```
>note: the property 'id' is also done automatically. the command also allows to edit an existing entity.

`SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'product' already exists`