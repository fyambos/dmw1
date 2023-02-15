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

Dans notre classe TicketController on peut spécifier la route de notre résultat:
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
Pour cela, on doit ajouter une nouvelle fonction dans notre TicketController, afin de créer un objet Ticket avec les valeurs que l'on veut insérer.

> note: ([Voir Symfony Doc](https://symfony.com/doc/current/doctrine.html#persisting-objects-to-the-database))

Sous la fonction index(), créer la route pour créer un ticket

```php
    public function index(ManagerRegistry $doctrine): Response
    {
     //...
    }
    #[Route('/new', name: 'create_ticket')]
```

Sous la route on créer une fonction pour créer un ticket:
```php
public function createTicket(): Response
    {
    }
```

On a encore besoin de passer le ManagerRegistry en parametre:
```php
public function createTicket(ManagerRegistry $doctrine): Response
```
Cela va nous permettre d'utiliser la methode getManager() du ManagerRegistry.
```php
    //dans la fonction createTicket:
        $entityManager = $doctrine->getManager();
```
On instancie un ticket, puis on utilise les setters pour affecter les valeurs
```php
        $product = new Product();
        $product->setName('Keyboard');
```

### Ubuntu
Si on déplace le dossier du projet vers Ubuntu, on aura besoin de changer les droits de var, qui sont associés à l'utilisateur, il faut donner les droits à www-data.
```bash
sudo chown www-data:www-data var -R
```
> note: Si on a une erreur de version, utiliser 3 au lieu de 3.8

### Twig

Dans le base.html.twig on a le stylesheet block et le javascript block. Ce sont les blocks dans lesquels on déposera les CSS et les scripts. Si ils étaient placés dans les twigs spécifiques aux Controllers (lucky, ticket, etc) alors ils overriderait ceux du base.html.twig. Ainsi, les fonctions `encore_entry_link_tags('app')` et `encore_entry_script_tags('app')`  ne seraient plus utilisée, le dernier sert notemment à afficher la barre symfony avec des info utiles en bas de la page.

On peut importer le base.html.twig en faisant un extends dans nos templates.
```js
{% extends 'base.html.twig' %}
```

Une fois que on a extends d'une autre template, on ne peut pas ajouter du contenu sans le mettre dans une balise block.

```js
{% block body %}
   //Some content//
{% endblock %}
```

### Bootstrap

Pour ajouter [Bootstrap](https://getbootstrap.com/docs/5.2/getting-started/introduction/) au projet, on ajoute la ligne
```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
```

À la fin du <head>.

Puis:
```html
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
```
À la fin du <body>.

On utilise Bootstrap dans nos templates twig, voir la [Documentation](https://getbootstrap.com/docs/4.1/getting-started/introduction/)

>note: On peut utiliser Tailwind à la place de Bootstrap, qui nécéssite plus de configurations.

### Formulaire

#### Créer un formulaire

Afin d'enregistrer dans la base de donnée, on réalise un formulaire que l'utilisateur rempli. Pour cela, utiliser le [Form Type](https://symfony.com/doc/current/forms.html).

Dans la page d'acceuil, ajouter un bouton "Creer Ticket" qui redirige vers la page de formulaire. A l'issue de l'envoi du formulaire, le ticket est ajouté à la base s'il est conforme.

Pour pouvoir utiliser Form Type il faut installer form.
```php
 composer require symfony/form
```
>rappel: commandes PHP à effectuer dans le contener php (dans le terminal docker)

Créer le fichier scr/Form/Type/TicketType.php

On créer une classe pour le formulaire de notre entité séparement du Controller pour avoir un TicketController propre, et avoir un un TicketType réutilisable.

```php
namespace App\Form\Type;
use Symfony\Component\Form\AbstractType;

class TicketType extends AbstractType {

}
```

Dans la classe Ticket Type, on va créer une fonction buildForm. (Qui ne retourne rien).

```php
public function buildForm(): void {
    }
```

Pour construire un formulaire on a besoin de la FormBuilderInterface.

```php
use Symfony\Component\Form\FormBuilderInterface;

public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    }
```

Dans la fonction buildForm on va donc utiliser le `$builder` et affecter les données provenant de `$options`.
```php
public function buildForm(FormBuilderInterface $builder, array $options): void
    {
            ->add('label', TextType::class)
            ->add('status', TextType::class)
            ->add('summary', TextType::class)
            ->add('reporter', TextType::class)
            ->add('assignee', TextType::class)
            ->add('save', SubmitType::class)
    }
```

On a besoin donc de use TextType et SubmitType. Ici tous nos champs sont des strings mais si on avait des int ou date on aurait besoin de DateType, NumberType, etc. Voir les [Field Types](https://symfony.com/doc/current/reference/forms/types.html#supported-field-types) supportés.

>Note: Installer MakerBundle permet de generer des classes formulaire avec make:form et make:registration-form.

#### Ajouter le formulaire au Controller

Dans le TicketController, on va ajouter le TypeTicket
```php
use App\Form\Type\TicketType;
```

Puis on modifier la fonction createTicket. Au lieu de créer un ticket et de l'envoyer dans la base, on récupérer les données du formulaire dans un objet ticket et l'envoyer à la base.

```php
    $entityManager = $doctrine->getManager();
    $ticket = new Ticket();
    //créer le formulaire
    $form = $this->createForm(TicketType::class, $ticket);
```

Le système de formulaire est suffisamment intelligent pour accéder à la valeur de la propriété protégée du ticket via les méthodes getTicket() et setTicket() de la classe Ticket. 

Ensuite, on vérifie que l'utilisateur à bien rempli les champs. Si c'est le cas, on récupère les données du formulaire.
```php
if ($form->isSubmitted() && $form->isValid()) {
    $ticket = $form->getData();
    $ticket->setCreated(new \DateTime()); //format Y-m-d H:i:s
}
```

#### Récupérer les données du formulaire

Cependant, pour que on puisse récupérer les données, il faut récupérée la requete envoyée par le formulaire. 

```php
use Symfony\Component\HttpFoundation\Request;
public function createTicket(Request $request, ManagerRegistry $doctrine): Response {
    // ...
}
```
Dans notre condition if, on va utiliser la fonction handleRequest sur le formulaire. Et *ensuite* on récupère les données.
On peut aussi affecter des données nous même, exemple, la date.
```php
use Symfony\Component\HttpFoundation\Request;
//...
public function createTicket(Request $request, ManagerRegistry $doctrine): Response {
    // ...
    if ($form->isSubmitted() && $form->isValid()) {
        $form->handleRequest($request);
        $ticket = $form->getData();
        $form = $this->createForm(TicketType::class, $ticket);
    }
}
```

#### Insérer les données du formulaire
Si les champs sont bien remplis, on peut ajouter dans la base de données (même principe que le précédent createTicket()). Sinon, on ajoute pas dans la base de donnée et on redemande la complétion du formulaire.
```php
    if ($form->isSubmitted() && $form->isValid()) {
        //... $entityManager;$ticket;$form
        $form->handleRequest($request);
        //... persist ticket and add ticket to database ...//
        return $this->redirectToRoute('tickets_list');
    }
    return $this->renderForm('tickets/new.html.twig', [
            'form' => $form,
    ]);
```
#### Afficher le formulaire

Enfin, on a besoin de créer le twig pour la page de formulaire.
Il suffit de créer le fichier templates/tickets/new.html.twig et d'y ajouter:
```js
{{ form(form) }}
```

On peut aussi extends le base.html.twig et ajouter une balise titre, il faudra mettre le formulaire dans un block body.

#### OptionResolver

Il est de bonne pratique d'ajouter dans les Form Type l'entité dont il est question, et d'utiliser OptionsResolver pour specifier l'option `'data_class'`
```php
use App\Entity\Ticket;
use Symfony\Component\OptionsResolver\OptionsResolver;
    // ...
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
 ```

#### Validation de formulaire

Executer la commande pour installer Validator:

```php
composer require symfony/validator
```

On peut valider les champs en ajoutant des set de règles.
Créer le fichier app/config/validator/validation.yaml. Les règles sont des propriétés de l'entité:
```yaml
App\Entity\Ticket:
    properties:
        label:
            - NotBlank: ~
        summary:
            - NotBlank: ~
        reporter:
            - NotBlank: ~
```

Sinon, on peut modifier directement l'entité:

```php
// src/Entity/Task.php
namespace App\Entity;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Mapping\ClassMetadata;
// ...
class Ticket
{
    // ...

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('label', new NotBlank());
        $metadata->addPropertyConstraint('summary', new NotBlank());
        $metadata->addPropertyConstraint('reporter', new NotBlank());
    }
}
```

#### Messages d'erreur de formulaire plus lisible
On peut désactiver l'option `legacy_error_message` dans le twig.yaml pour avoir des messages par défaut plus user-friendly.
```yaml
# config/packages/framework.yaml
framework:
    form:
        legacy_error_messages: false
```

#### Formulaire et Bootstrap
On peut modifier le package framework.yaml pour que les formulaires symfony utilisent bootstrap 5.
```yaml
# config/packages/twig.yaml
twig:
    form_themes: ['bootstrap_5_layout.html.twig']
```
### Flash Messages
[Doc Controller](https://symfony.com/doc/current/controller.html#flash-messages)

We want to do edit/delete actions on tickets and have confirmation/error messages.
We can do this with addFlash, in the TicketController.php in our delete method:
```javascript
$this->addFlash(
                'success',
                'The ticket was successfully deleted!'
            );
            return $this->redirectToRoute('tickets_list');

In the list.html.twig get the flash messages (the class here is a bootstrap class):
```

```javascript
{# read and display several types of flash messages #}
{% for label, messages in app.flashes(['success', 'warning','error','info']) %}
  {% for message in messages %}
    <div class="alert alert-{{ label }}" role="alert"> 
      {{ message }}
    </div>
  {% endfor %}
{% endfor %}
```

#### Authenficication

[Doc Security](https://symfony.com/doc/current/security.html)
```docker-compose exec php bash```
```php bin/console make:user```
Choix a faire:
- User
- yes
- email
- yes

On créer donc une entité User, enregistré en BDD avec doctrine, avec "email" et option d'hashage de mot de passe. Cela créer aussi les champs "role", et "password".

```php bin/console make:migration```
```php bin/console doctrine:migrations:migrate```
http://127.0.0.1:8080/ la table user est bien créer

il faut activier les droits de modif sur security.yaml
```exit```
```sudo chown username:username app/config/packages/security.yaml```

```docker-compose exec php bash```
```composer require symfonycasts/verify-email-bundle```
```php bin/console make:registration-form```

Choix à faire:
- yes
- no
- yes

sur http://127.0.0.1/register on créer un user (exemple: user@test.com mot de passe 1234)
on remarque que malgre l'op'authentification auto apres registration on est pas authe
en effet il rest la partie auth a faire

php bin/console make:auth
Choix à faire:
- 1
- LoginAuthenticator
- SecurityController
- yes

## Controle d'accès
Dans notre TicketControler on ajoute:
```php
use Symfony\Component\Security\Http\Attribute\IsGranted;
```
Puis sur nos routes on ajoute l'accès que l'on veut donner. Par exemple, la page Lucky est visible pour tous. Tickets est visible pour les utilisateurs authentifiés uniquement. Certaines routes faisant partie de la page ticket ne sont accéssible que pour les admins.

On a donc deux roles:
ROLE_USER et ROLE_ADMIN.

Si l'on veut directement mettre une restriction sur une route, on peut mettre ceci devant la route au niveau du Controller. Lorsqu'un une personne non autorisée tente d'entrée dans la page, une erreur symphony AccessDeniedHttpException s'affiche par défaut. Il faut changer cette redirection par la suite.

```php
#[IsGranted('ROLE_USER')]
//route ici
///...
#[IsGranted('ROLE_ADMIN')]
//autre route ici
```

On peut aussi faire une redirection à l'interieur de la route et rediriger directement, en regardant si le role de l'utilisateur
```php
 #[Route('/admin', name: "adminpage")]
    public function admin(ManagerRegistry $doctrine): Response
    {
        $roles = $this->getUser()->getRoles();
        
        if(in_array('ROLE_ADMIN', $roles)){

            return $this->render('admin/adminpage.html.twig');

        }

        return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
     
    }
```

Si l'on veut le faire au niveau des template twigs on peut utiliser:

```javascript
{% if is_granted('ROLE_ADMIN') %}
    <a href="...">Delete</a>
{% endif %}
```

(https://github.com/fyambos/dmw1)
(https://github.com/fyambos/dmw1/issues?q=is:open%20is:issue%20assignee:fyambos%20sort:created-asc)
