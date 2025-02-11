<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\ApiEndpointInterface;
use Misery\Component\Common\Client\ApiEndPointsInterface;

class AkeneoApiEndpoints implements ApiEndPointsInterface
{
    private array $endpointSet = [
        ApiAttributesEndpoint::NAME => ApiAttributesEndpoint::class,
        ApiOptionsEndpoint::NAME => ApiOptionsEndpoint::class,
        ApiProductsEndpoint::NAME => ApiProductsEndpoint::class,
        ApiProductModelsEndpoint::NAME => ApiProductModelsEndpoint::class,
        ApiCategoriesEndpoint::NAME => ApiCategoriesEndpoint::class,
        ApiReferenceEntitiesEndpoint::NAME => ApiReferenceEntitiesEndpoint::class,
        ApiFamiliesEndpoint::NAME => ApiFamiliesEndpoint::class,
        ApiFamilyVariantsEndpoint::NAME => ApiFamilyVariantsEndpoint::class,
        ApiAssetsEndpoint::NAME => ApiAssetsEndpoint::class,
    ];

    public function getEndPoint(string $endpointName): ApiEndpointInterface
    {
        if (isset($this->endpointSet[$endpointName])) {
            return new $this->endpointSet[$endpointName];
        }

        throw new \Exception(sprintf('Unknown akeneo endpoint found %s', $endpointName));
    }
}