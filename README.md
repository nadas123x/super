# Cours Symfony Grafikart

## Extensions utiles
- Twig Language : coloration des fichiers twig

## Soucis rencontrés + corrections
### 3. Découverte de doctrine : 
- Erreur : Conflit lors de l'ajout du package slugify
- Solution : Installer une version antérieurs : composer require cocur/slugify:3.1
### 10. Image à la une
- Erreur : le package liip/imagine-bundle ne marche pas
- Solution : Il faut utiliser la commande *symfony serve* ET PAS *php -S localhost:8000 -t public*

## Rappel architecture
### Architecture d'un projet Symfony :
- bin : contient les commandes que l'on peut utiliser dans la console
- config : contient les fichiers de configurations yaml
    - packages : contient les config des différents packages (connexion à la BDD, mailer...)
    - __routes.yaml__ : configuration des routes
    - __services.yaml__ : définit les services que l'on utilise
- public : Racine du serveur (il faut pointer sur ce dossier quand on lance le serveur)
- src : Contient le code du PHP de l'appli. Correspondant au namepsace App (pour l'autoloader)
    - Controller : Dossier qui content les fichiers Controller qui vont récupérer les informations des requêtes SQL du Repository, récupère / contrôle la cohérence des informations (soumissions de formulaire) et les stocke en BDD. Chaque fonction d'un controller est annoté du lien l'appelant, son nom et sa méthode (GET, POST) et va rediriger vers une page.
    - DataFixtures (package) : Dossier qui va contenir des jeux de test qui vont pouvoir être chargées automatiquement en base de données
    - Entity : Contient toutes les classes représentants les entités (ex : user) avec pour chaque propriété des règles automatiquement créées par composer (qui seront appliqués à la BDD). On peut y ajouter des Assert (package) afin de vérifier le bon format des données avant validation dans un formulaire
    - Form : Contient les formulaires créés par composer. On va pouvoir y ajouter / modifier les champs du formulaires se trouvant dans la fonction buildForm et leur passer des règles d'affichage (required, label, attributs)
    - Repository : Va contenir les fichiers qui vont permettrent la communication avec la base de données via des requêtes customisables suivant les paramètres s'il y en a. Chaque fonction va renvoyer une query (requête) OU un résultat de recherche suivant le besoin.
- templates : contient les vues / pages de l'application en twig
- test : pour les tests unitaires et fonctionnels
- translations : pour le multilangue
- var : contient le cache et les logs
- vendor : contient tous les packages téléchargés
- __.env__ : Contient des infos comme la connexion à la base de données

### Détails des sources (src) :
- Controller : Un Controller par type de route, avec un propre au login
- DataFixtures : Une Fixture par entité à remplir
- Entity : Une entité par objet par table en BDD. On enregistre dans un objet avant de l'envoyer en BDD dans un contrôleur
- Form : Un par formulaire à remplir dans les pages. On peut y placer des contraintes à vérifier (required, pattern...). On peut récupérer les infos du formulaire dans la requête pour faire une recherche en BDD ou modifier un élément en BDD.
- Listener : On déclare les listeners à l'intérieur pour s'abonner à des changements sur des éléments (effacer des fichiers en cache en même temps que des éléments en BDD)
- Repository : Un Repository par entité. On y place nos requêtes SQL personnalisées pour les appeler dans le Controller.

## Tips
### Symfony :
- Ajouter un style Bootstrap à une formulaire
    - Dans : config/packages/twig.yaml
    ```yaml
    form_theme: ['bootstrap_4_layout.html.twig']
    ```
    - Dans le fichier html.twig :
    ````twig
    <div class="container mt-4">
        <h1>Editer le bien</h1>
        {{ form_start(form) }}
        {{ form_widget(form) }}
        <button class="btn btn-primary">Editer</button>
        {{ form_end(form) }}
    </div>
    ````

- Ajouter des traductions (ex : form) pour changer les champs des formulaires (solution 1)
    - Créer un fichier : forms.fr.yaml avec les correspondances -> ex : City: Ville
    - Dans config\services.yaml : 
    ```yaml
    parameters:
        locale: 'fr'
    ```
    - Dans config\packages\translation.yaml :
    ```yaml
    framework:
    default_locale: '%locale%'
    translator:
        default_path: '%kernel.project_dir%/translations'
        fallbacks:
            - '%locale%'
    ```
    - Dans src\Form\PropertyType.php : 
    ```php
    $resolver->setDefaults([
            'data_class' => Property::class,
            'translation_domain' => 'forms'
    ]);
    ```

- Pour changer les champs des formulaires (solution 2)
    - Dans src\Form\PropertyType.php :
    ```php
        // 1. paramètre, 2. type, 3. label
        $builder
        ->add('city', null, [
            'label' => 'Ville'
        ])
        // On défini un type ChoiceType -> liste déroulante
        ->add('heat', ChoiceType::class, [
                'choices' => array_flip(Property::HEAT)
        ])
    ```
- On peut voir toutes les options dans security.yaml en entrant la commande suivante :
```
php bin/console config:dump-reference security
```
- Même exemple pour l'autowiring
```
php bin/console debug:autowiring
```

- On peut ajouter la base de données avec les fixtures au lieu de tout effacer :
```
php bin/console doctrine:fixtures:load --append
```

- On peut annuler une migration en revenant en arrière, supprimer la mauvaise migration et en refaire une pour la recharger :
```
php bin/console doctrine:migrations:migrate DoctrineMigrations\Version20201014100149
```

- Attention avec Doctrine quand on crée une relation : Il faut faire attention à la classe qui est propriétaire d'une autre
    - Le propriétaire est défini par l'annotation inversedBy : C'est sur celui-là qu'on pourra effectuer les méthodes add
    - La classe appartenant au propriétaire aura mappedBy
    - Si les liaisons ne font pas faites correctements, il n'y aura pas de persistance en base de données
    - Il faut lancer la commande make:entity sur le propriétaire pour le définir sans avoir à le faire manuellement

- On peut créer tout un système de CRUD (Create, Read, update, delete) autour d'une entité avec la commande + le nom de l'entité :
```
php bin/console make:crud
```
    - La création sera automatique mais on pourra modifier des fichiers comme : le Form, les templates (conseillé) et le contrôleur.
  
- Doctrine, via l'ORM, peut déclencher des évènements à différentes étapes de modifications : avant / après l'enregistrement en BDD, avant / après la persistence
  - On crée un Suscriber dans un nouveau répertoire dans **src** qui implémente : EventSubscriber et on indique dans la fonction **getSubscribedEvents()** sur quelle fonction on souscrit
  - Dans services.yaml : On place un nouveau service qui correspond au namespace du Suscriber


### PHP :
- array_flip : Remplace les clés par les valeurs, et les valeurs par les clés
