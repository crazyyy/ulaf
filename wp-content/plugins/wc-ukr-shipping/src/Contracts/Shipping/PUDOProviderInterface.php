<?php

declare(strict_types=1);

namespace kirillbdev\WCUkrShipping\Contracts\Shipping;

use kirillbdev\WCUkrShipping\Dto\Shipping\City;
use kirillbdev\WCUkrShipping\Dto\Shipping\PUDO;

interface PUDOProviderInterface
{
    /**
     * @param string $query
     * @return City[]
     */
    public function searchCitiesByQuery(string $query): array;

    public function searchCityById(string $id): ?City;

    /**
     * @param string $cityId
     * @param string $query
     * @param int $page
     * @param array $types
     * @return array
     */
    public function searchPUDOByQuery(
        string $cityId,
        string $query,
        int $page,
        array $types = []
    ): array;

    public function searchPUDOById(string $id): ?PUDO;
}
