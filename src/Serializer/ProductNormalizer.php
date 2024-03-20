<?php
namespace App\Serializer;

use App\Entity\Brand;
use App\Entity\Genre;
use App\Entity\Product;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,

        private UrlGeneratorInterface $router,
    ) {
    }

    public function normalize($product, string $brand = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($product, $brand, $context);

        // Here, add, edit, or delete some data:
        $data['href']['self'] = $this->router->generate('app_products', [
            'id' => $product->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Product;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Product::class => true,
            Brand::class => true,

        ];
    }
}