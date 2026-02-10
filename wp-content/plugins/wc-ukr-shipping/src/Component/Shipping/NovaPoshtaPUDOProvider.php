<?php

declare(strict_types=1);

namespace kirillbdev\WCUkrShipping\Component\Shipping;

use kirillbdev\WCUkrShipping\Contracts\Shipping\PUDOProviderInterface;
use kirillbdev\WCUkrShipping\DB\Repositories\CityRepository;
use kirillbdev\WCUkrShipping\DB\Repositories\WarehouseRepository;
use kirillbdev\WCUkrShipping\Dto\Shipping\City;
use kirillbdev\WCUkrShipping\Dto\Shipping\PUDO;

/**
 * todo Remove by SmartyParcel Locator in future
 */
class NovaPoshtaPUDOProvider implements PUDOProviderInterface
{
    private CityRepository $cityRepository;
    private WarehouseRepository $warehouseRepository;

    public function __construct(CityRepository $cityRepository, WarehouseRepository $warehouseRepository)
    {
        $this->cityRepository = $cityRepository;
        $this->warehouseRepository = $warehouseRepository;
    }

    public function searchCitiesByQuery(string $query): array
    {
        $result = $this->cityRepository->searchCitiesByQuery($query);

        return array_map(function (array $row) {
            return new City($row['ref'], $row['description'], $row['description_ru']);
        }, $result);
    }

    public function searchCityById(string $id): ?City
    {
        $result = $this->cityRepository->getCityByRef($id);
        if ($result === null) {
            return null;
        }

        return new City($result->ref, $result->description, $result->description_ru);
    }

    public function searchPUDOByQuery(string $cityId, string $query, int $page, array $types = []): array
    {
        $mappedTypes = [];
        if (in_array(PUDO::PUDO_TYPE_WAREHOUSE, $types, true)) {
            $mappedTypes[] = WCUS_WAREHOUSE_TYPE_REGULAR;
            $mappedTypes[] = WCUS_WAREHOUSE_TYPE_CARGO;
        }
        if (in_array(PUDO::PUDO_TYPE_LOCKER, $types, true)) {
            $mappedTypes[] = WCUS_WAREHOUSE_TYPE_POSHTOMAT;
        }

        $total = $this->warehouseRepository->countByQuery($query, $cityId, $mappedTypes);
        $data = array_map(function (array $row) {
            return new PUDO(
                $row['ref'],
                $row['city_ref'],
                $row['description'],
                $row['description_ru'],
                (int)$row['warehouse_type'] === WCUS_WAREHOUSE_TYPE_POSHTOMAT
                    ? PUDO::PUDO_TYPE_LOCKER
                    : PUDO::PUDO_TYPE_WAREHOUSE
            );
        }, $this->warehouseRepository->searchByQuery($query, $cityId, $page, 20, $mappedTypes));

        return [
            'data' => $data,
            'total' => $total,
        ];
    }

    public function searchPUDOById(string $id): ?PUDO
    {
        $result = $this->warehouseRepository->getWarehouseByRef($id);
        if ($result === null) {
            return null;
        }

        return new PUDO(
            $result->ref,
            $result->city_ref,
            $result->description,
            $result->description_ru,
            (int)$result->warehouse_type === WCUS_WAREHOUSE_TYPE_POSHTOMAT
                ? PUDO::PUDO_TYPE_LOCKER
                : PUDO::PUDO_TYPE_WAREHOUSE
        );
    }
}
