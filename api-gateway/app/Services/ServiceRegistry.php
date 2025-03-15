<?php

namespace App\Services;

class ServiceRegistry
{
    protected array $services;

    public function __construct()
    {
        $this->services = config('services.microservices');
    }

    public function getServiceUrl(string $service): ?string
    {
        return $this->services[$service]['base_url'] ?? null;
    }

    public function getServicePrefix(string $service): string
    {
        return $this->services[$service]['prefix'] ?? $service;
    }

    public function getRouteConfig(string $service, string $route): ?string
    {
        return $this->services[$service]['routes'][$route] ?? null;
    }

    public function isValidService(string $service): bool
    {
        return isset($this->services[$service]);
    }

    public function getServiceConfig(string $service): ?array
    {
        return $this->services[$service] ?? null;
    }
}
