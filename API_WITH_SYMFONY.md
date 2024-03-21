# CREER UNE API AVEC SYMFONY

## CONTROLLER ET ROUTES

Créer un controller sans tpl : `make:controller --no-template`
Installer le serializer : `composer require symfony/serializer-pack`
L'importer en paramètre ds le controller `SerializerInterface $serializer`

### Dans le controller, on va créer des routes qui seront les endpoints de l'API :
- ça suit la même structure que des routes classiques mais attention à bien stipuler la méthode HTTP
`#[Route('url', name: 'nom', methods: ['methode'], requirements: contraintes)]`
- on rajoute `: JsonResponse` à la fin des méthodes pour dire qu'on attend bienun JSON
Exemple :
`#[Route('/api/genres/{id}/shows', name: 'app_api_genres_getShows', methods: ['GET'], requirements: ['id' => 'd+'])]`

### On récupère les JSON grâce à la méthode ->json() de l'AbstractController
    `return $this->json($data, $status = 200, $headers = [], $context = []);`

    $data = données à convertir en json
    $status = code http ou réponse http
    $headers = headers éventuels par exemple pour la redirection
    $context = contexte d'application de la sérialisation, par exemple pour les groupes 

### Cas du Create
- La route est en POST !
- Bien typeHinter Request, Serializer
- On récupère la data avec getContent()
- En API REST, la convention est de rediriger vers la liste :
<!-- //TODO verifier que la mise en page est ok :) -->
            $data = $request->getContent();
            $product = $serializer->deserialize($data, product::class, 'json');
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->json(
            $product, 
            Response::HTTP_CREATED, 
            ["Location" => $this->generateUrl("app_products")]
            ); 

## SERIALIZER ET NORMALIZER

Le composant serializer va permettre de convertir l'objet en JSON = sérialiser (on désérialise qd on convertit le JSON en objet).
Son installation suffit à convertir automatiquement les objets en JSON.
Mais il a beaucoup d'autres outils pour nous permettre de régler des problèmes courants...

### Références circulaires et groupes d'annotation
Pour régler le problème des références circulaires lors de la récupération des données en JSON sur des entités ayant des relations avec d'autres, on va utiliser les groupes :

1. importer la classe des groupes dans l'entité :

`use Symfony\Component\Serializer\Annotation\Groups;`

2. au-dessus de la déclaration de la classe de l'entité, créer un groupe :

`#[Groups(['product'])]`

3. Pour chaque propriété, sauf celles concernant des relations, rajouter un sous-groupe :

`#[Groups(['productLinked'])]`

4. Récupérer, avec le controller, uniquement les groupes désirés : 

`return $this->json(
            $products ,
            Response::HTTP_OK,
            [],
            ["groups"=>['product','brandLinked']]
        );`

5. A tester : on peut aussi sélectionner certains attributs (cf doc) et ignorer des attributs lors de la sérialisation :  
Exp : `return $this->json($genreRepository->findAll(), 200, [], 
[AbstractNormalizer::IGNORED_ATTRIBUTES => ['shows']]);`

### Dénormalisation
Dans le cas où on veut pouvoir créer un enregistrement avec des infos liées à un autre enregistrement, si on en dénoramliser pas, ça va créer un nouvel enregistrement de l'entité liée.
On va utilsier le dénormaliseur cf fichier /src/Serializer/EntityDenormalizer.php
Tout fichier placé ds le dossier /Serializer sera inspecté par Symfony lors de l'utilisation du serializer.
Ce dénormalizer permet de récupérer les infos liées à une entité à partir de son id.
Il suffira de spécifier l'id lors de la création du JSON sous la forme `{"propriété":id }`.

## AUTHENTIFICATION AVEC UN JSON WEB TOKEN
cf doc. https://github.com/lexik/LexikJWTAuthenticationBundle/blob/3.x/Resources/doc/index.rst#getting-started

1. Installation du composant lexik/jwt-authentication-bundle
` composer require lexik/jwt-authentication-bundle`

2. Générer les clés 
`php bin/console lexik:jwt:generate-keypair`

3. Rajouter dans le .env
```
    JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
    JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
    JWT_PASSPHRASE=
```
4. Dans config/packages/security.yaml

```
security:
    enable_authenticator_manager: true # Only for Symfony 5.4
    # ...

    firewalls:
        login:
            pattern: ^/api/login
            stateless: true
            json_login:
                check_path: /api/login_check
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure

        api:
            pattern:   ^/api
            stateless: true
            jwt: ~

    access_control:
        - { path: ^/api/login, roles: PUBLIC_ACCESS }
        - { path: ^/api,       roles: IS_AUTHENTICATED_FULLY }
```

5. Dans config/routes.yaml : 
```
api_login_check:
    path: /api/login_check
```
Cette route a été créée automatiquement par lexikJWT.

6. Pour s'authentifier manuellement il faut copier le json des identifiants 
`{"username":"admin@gmail.com","password":"admin"}` où username est la propriété discriminante dans User.php
dans le body de la requête et envoyer à /api/login_check
ça va générer un token qu'on va copier dans authorization->bearer ds postman lors de l'accès aux routes protégées.
=> g pas compris comment c'lié à l'ACL ?
=> lire de la doc pour synthétiser





