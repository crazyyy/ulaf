<?php

declare(strict_types=1);

namespace kirillbdev\WCUkrShipping\Component\Shipping;

use kirillbdev\WCUkrShipping\Api\SmartyParcelWPApi;
use kirillbdev\WCUkrShipping\Contracts\Shipping\PUDOProviderInterface;
use kirillbdev\WCUkrShipping\Dto\Shipping\City;
use kirillbdev\WCUkrShipping\Dto\Shipping\PUDO;

class SmartyParcelPUDOProvider implements PUDOProviderInterface
{
    private string $carrierSlug;
    private string $lang;
    private SmartyParcelWPApi $api;

    public function __construct(string $carrierSlug, string $lang, SmartyParcelWPApi $api)
    {
        $this->carrierSlug = $carrierSlug;
        $this->lang = $lang;
        $this->api = $api;
    }

    public function searchCitiesByQuery(string $query): array
    {
        $response = $this->api->sendRequest('/v1/locator/cities', null, [
            'carrier_slug' => $this->carrierSlug,
            'query' => $query,
            'language' => $this->lang,
        ]);

        return array_map(function (array $city) {
            return new City($city['carrier_city_id'], $city['name'], $city['name']);
        }, $response['cities']);
    }

    public function searchCityById(string $id): ?City
    {
        throw new \RuntimeException('Not implemented');
    }

    public function searchPUDOByQuery(string $cityId, string $query, int $page, array $types = []): array
    {
        $mappedTypes = [];
        if (in_array(PUDO::PUDO_TYPE_WAREHOUSE, $types, true)) {
            $mappedTypes[] = 'warehouse';
            $mappedTypes[] = 'pudo';
        }
        if (in_array(PUDO::PUDO_TYPE_LOCKER, $types, true)) {
            $mappedTypes[] = 'parcel_locker';
        }

        $params = [
            'carrier_slug' => $this->carrierSlug,
            'language' => $this->lang,
            'page' => $page,
            'limit' => 20,
            'types' => $mappedTypes,
        ];
        if (!empty($query)) {
            $params['query'] = $query;
        }
        if ($this->carrierSlug === 'nova_post') {
            $params['country_code'] = $cityId;
        } else {
            $params['carrier_city_id'] = $cityId;
        }
        $response = $this->api->sendRequest('/v1/locator/pudo-points', null, $params);

        $data = array_map(function (array $item) use ($cityId) {
            return new PUDO(
                $item['carrier_pudo_id'],
                $cityId,
                $item['name'],
                $item['name'],
                $item['type'] === 'parcel_locker'
                    ? PUDO::PUDO_TYPE_LOCKER
                    : PUDO::PUDO_TYPE_WAREHOUSE
            );
        }, $response['pudo_points']);

        return [
            'data' => $data,
            'total' => count($data),
        ];
    }

    public function searchPUDOById(string $id): ?PUDO
    {
        throw new \RuntimeException('Not implemented');
    }
}
