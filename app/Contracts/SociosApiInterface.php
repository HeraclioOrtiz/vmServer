<?php

namespace App\Contracts;

interface SociosApiInterface
{
    /**
     * Get socio data by DNI from external API.
     *
     * @param string $dni
     * @return array|null
     * @throws \App\Exceptions\ApiConnectionException
     */
    public function getSocioPorDni(string $dni): ?array;

    /**
     * Build direct URL for socio photo.
     *
     * @param string $socioId
     * @return string|null Direct URL to image
     */
    public function buildFotoUrl(string $socioId): ?string;
}
