# dmw1

## INSTALLATION
#### Installer wsl2
```bash
wsl -l -o #pour voir les distributions
wsl --install -d Ubuntu-20.04
```
> Si erreur update windows ou activer virtualisation BIOS ou faire installation manuelle


#### Créer le Docker à partir d'un Repository

Installer les 3 extensions disponibles sur : [Microsoft Learn](https://learn.microsoft.com/en-us/windows/wsl/tutorials/wsl-containers#develop-in-remote-containers-using-vs-code)

- Ouvrir Ubuntu et creer le dossier de notre repository.
- Initialiser git et pull le repo source
- Lancer VSCODE Studio avec la commande "code ."

> note: Si Ubuntu ne fonctionne pas après avoir installer wsl2, trouver Ubuntu dans le store windows et le telecharger.

```bash
mkdir repo_name
cd repo_name
git init
git branch -M main
git remote add origin https://github.com/user_name/repo_name.git
git pull https://github.com/ldandoy/sf-dwm-23.git
code . #pour ouvrir code dans le dossier
```

Ensuite, il faut ouvrir Docker. Pour cela:
- Ctrl+Shift+P puis "Dev Containers: Open Folder in Container"

#### Créer le Docker à partir d'un fichier YML
Ouvrir le contener php dans le terminal intégré de VSCODE et faire la commande:
```bash
docker-compose up -d    
```
> -d, detach mode est pour liberer la console et que la bdd demarre en arriere plan
> docker-compose up lance docker, et si le projet n'a jamais été composé il va le build d'abord. on peut forcer le rebuild avec docker-compose --build

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
Certains dossiers/fichier ne doivent pas apparaitre dans GIT.
- mysql
- var
- vendor
- .env

### Controllers & Templates
Les controllers sont les pages PHP de doctrine. Pour la configuration des sites, tout se passe dans app/config, app/src et app/template.

Sur docker, ouvrir php dans le terminal:
```bash
composer require annotations
```
On utiliser les attributs plutôt que les YAML. De la forme /controller/fonction (/lucky/number)
- dans app/src Controller, créer le fichier php controller de la page/API
- dans app.src/templates, créer le html.twig de la template

Les templates sont des vues et ne contiennent pas des appels aux bases de données ou calculs, les templates sont normalement déjà ordonnées etc. Le reste, plus poussé est fait directement dans le container php.
Donc on préfère faire des calculs ou des boucles plus poussées dans le Controller.php plutôt que dans le html.twig.

[Symphony Doc Generating Controller - CRUD](https://symfony.com/doc/current/controller.html#generating-controllers)

### Doctrine Database
Dans le container php de docker en CLI, installer doctrine avec les commandes:

```bash
composer require symfony/orm-pack
composer require --dev symfony/maker-bundle
```

>note: normalement avec --webapp lors de l'install de symfony on a pas besoin de le faire
Dans app/.env, setup up la base de donnée:
`DATABASE_URL=mysql://root:@db_name:3306/db_name?serverVersion=5.7`

Puis créer la db sur phpmyadmin avec la commande:
`php bin/console doctrine:database:create` ou `php bin/console d:d:c` 

Notre db à déja été créer lors du build avec docker.

>note: toutes les commandes doivent être faites dans le container php de docker

## UTILISATION

### Créer une entité avec doctrine

[Symphony Doc Creating an Entity Class](https://symfony.com/doc/current/doctrine.html#creating-an-entity-class)

Créer l'entité (une table) et la push vers la base de données avec la commande :
> note: toutes les commandes doivent être faites dans le container php de docker

###### Exemple:
Entité "Ticket" avec un label, une description, une date de création, un reporter, un assignee et un status.

La commande make:entity permet de créer une entité, ainsi que de modifier une entité existante (ajouter de nouveaux champs). On peut toujours ajuster l'entité dans le fichier généré (App/Entity/Ticket) si besoin.


```bash
php bin/console make:entity
```

>note: la propriété 'id' est créer automatiquement, de même que les setters et getters pour tous les champs créer au travers de la commande.

Si l'entité à été créer à la main, on peut utiliser --regenerate pour ajouter les setters et getters.
```bash
php bin/console make:entity --regenerate
php bin/console make:migration
```
> Pour regenerer les setters/getters après un changement, aussi passer --overwrite.

Examiner la migration (si des champs ont été ajoutés ou manquants, etc.).
Dans le fichier de migration, il y a une partie up (monter la migration) et une partie down (revenir en arrière). Par défaut, il fait les migrations up.

La commande précédente a préparé la migration, pour exécuter la migration, utiliser la commande suivante :
```bash
php bin/console doctrine:migrations:migrate
```

Il vaut mieut commit les migrations plutôt que d'en initialiser plusieurs, sinon il y yaura des erreurs, auquel cas supprimer toutes les migrations.

Exemple:
`SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'product' already exists`
> vérifier dans phpmyadmin si la table existe, et modifier la migration générée si c'est le cas (retirer la ligne de création de la table).

### Select * via doctrine
Un select via doctrine consiste à **persister** la récupération d'objets de la base de données.
Pour cela, on doit créer un nouveau controller dans lequel on va récupérer les données de l'entité.

```bash
php bin/console make:controller TicketController
```

La route peut avoir un nom different, mais le nom du controller "class TicketController" doit être le même que l'entité.

```php
//app/src/Controller/TicketController.php
#[Route('/tickets')]
class TicketController extends AbstractController
{   
    public function index(): Response
    {}
}
```

Dans notre classe Ticket Controller on peut spécifier la route de notre résultat:
```php
class TicketController extends AbstractController
{
    #[Route('/', name: 'tickets_list')]
    public function index(): Response
    {}
}
```
Pour récupérer les données de l'entité, on a besoin du ManagerRegistry.
```php
use Doctrine\Persistence\ManagerRegistry;
```

Dans notre fonction index() on va passer le ManagerRegistry
```php
public function index(ManagerRegistry $doctrine): Response
```

Cela va nous permettre d'utiliser la methode getRepository du ManagerRegistry.
```php
    //dans la fonction index:
    $tickets = $doctrine->getRepository(Ticket::class)->findAll();
```

Il faut ajouter le `use Ticket`, car Ticket n'est pas un controller, donc lorsqu'on appelle l'entité Ticket dans getRepository il va chercher le controller Ticket.

```php
use App\Entity\Ticket;
```

On peut voir ce que contient ticket avec dd():

```php
        dd($tickets);
```

Ensuite on va render l'html de ce ticket que l'on a récupérer
```php
        return $this->render('tickets/list.html.twig', [
            'tickets' => $tickets,
        ]);
```

Ainsi, on va créer un dossier tickets et un fichier list.html.twig dans lequel on va parcourir notre variable 'tickets'.

```php
//app/src/templates/tickets/list.html.twig
{% for ticket in tickets %}
    {{ ticket.label }}<br />
{% endfor %}
```

>note: commenter dd() pour executer le render. dd() stop le programme après exécution, dump and die.

### Insersion via doctrine
Une insersion via doctrine consiste à **persister** un objet dans la base de donnée.
Pour cela, on doit créer un nouveau controller dans lequel on va créer un objet Ticket avec les valeurs que l'on veut insérer.

```bash
php bin/console make:controller TicketController
```

Ajouter les imports:
```php
use App\Entity\Ticket;
use Doctrine\Persistence\ManagerRegistry;
```
Changer le nom de la route en "create_ticket":

```php
#[Route('/ticket', name: 'create_ticket')]
```
Notre methode index() devient createTicket()

([Voir Symfony Doc](https://symfony.com/doc/current/doctrine.html#persisting-objects-to-the-database))
