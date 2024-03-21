# CREER UNE API AVEC SYMFONY

## Création du controller et des routes

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
        $data = $request->getContent();
        $product = $serializer->deserialize($data, product::class, 'json');
        $entityManager->persist($product);
        $entityManager->flush();
        return $this->json($product, Response::HTTP_CREATED, ["Location" => $this->generateUrl("app_products")]);

## Serializer et normalizer

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










