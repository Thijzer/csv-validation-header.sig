<?php

namespace Misery\Component\Akeneo;

class StandardValueCreator
{
    public function create(string $code, string $data, string $locale = null, string $scope = null)
    {
        return [
            'code' => $code,
            'locale' => $locale,
            'scope' => $scope,
            'data' => $data,
        ];
    }

    public function createUnit(string $code, string $unit, string $data, string $locale = null, string $scope = null)
    {
        return [
            'code' => $code,
            'locale' => $locale,
            'scope' => $scope,
            'data' => [
                'amount' => $data,
                'unit' => $unit,
            ],
        ];
    }

    public function createArrayData(string $code, array $data, string $locale = null, string $scope = null)
    {
        return [
            'code' => $code,
            'locale' => $locale,
            'scope' => $scope,
            'data' => $data,
        ];
    }
}