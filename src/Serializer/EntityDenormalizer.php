<?php

namespace App\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntityDenormalizer implements DenormalizerInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function denormalize($data, $type, string $format = null, array $context = []): mixed
    {

        // j'arrive ici que quand c'est une entité que je cherche via le json, du coup on va la chercher en bdd, le type c'est le fqcn et data c'est l'id
        return $this->entityManager->find($type, $data);
    }

    public function supportsDenormalization($data, $type, string $format = null, array $context = []): bool
    {
        //  vérifier si on parle d'une entité et qu'on a en valeur uniquement un nombre

        // si le type dénormalizé est une entité et que sa valeur est un nombre je sais que je vais devoir aller chercher une valeur en bdd donc j'active le dénromalize avec un return true
        if (strpos($type, "App\Entity") === 0 && is_int($data)) {
            return true;
        }
        return false;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            "object" => true,
        ];
    }
}
