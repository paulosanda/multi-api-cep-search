<?php

namespace PauloSanda\MultipleCepApi;

use PauloSanda\MultipleCepApi\Contracts\CepProviderInterface;
use PauloSanda\MultipleCepApi\Utils\CepFormatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CepService
{
    private Collection $providers;

    public function __construct()
    {
        $this->providers = collect();
    }

    /**
     * Adiciona um provedor ao serviço
     */
    public function addProvider(CepProviderInterface $provider): self
    {
        $this->providers->push($provider);
        return $this;
    }

    /**
     * Busca um endereço pelo CEP, tentando provedores em ordem de prioridade
     */
    public function search(string $cep): ?array
    {
        // Validação do CEP
        if (!CepFormatter::validate($cep)) {
            Log::debug('CEP inválido', ['cep' => $cep]);
            return null;
        }

        // Ordenar provedores por prioridade (maior primeiro)
        $sortedProviders = $this->providers->sortByDesc(
            fn($provider) => $provider->getPriority()
        );

        foreach ($sortedProviders as $provider) {
            // Formatar o CEP conforme necessário para o provedor
            $formatedCep = $this->formatZipCodeForProvider($cep, $provider);

            Log::debug('Tentando provedor', [
                'provedor' => $provider->getName(),
                'cep_formatado' => $formatedCep
            ]);

            // Tentar busca com o provedor atual
            $result = $provider->search($formatedCep);

            if ($result !== null) {
                // Adicionar CEP normalizado ao resultado
                $result['normalized_cep'] = CepFormatter::normalize($cep);
                Log::debug('CEP encontrado', [
                    'provedor' => $provider->getName(),
                    'cep' => $cep
                ]);
                return $result;
            }

            // Se o provedor não tem fallback, para a cadeia
            if (!$provider->hasFallback()) {
                Log::debug('Provedor sem fallback, parando cadeia', [
                    'provedor' => $provider->getName()
                ]);
                break;
            }
        }

        Log::debug('Nenhum provedor encontrou o CEP', ['cep' => $cep]);
        return null;
    }

    /**
     * Formata o CEP conforme o requerimento do provedor
     */
    private function formatZipCodeForProvider(string $cep, CepProviderInterface $provider): string
    {
        $requiredFormat = $provider->getRequireCepFormat();

        if ($requiredFormat === null) {
            return $cep; // Mantém o formato original
        }

        return CepFormatter::format($cep, $requiredFormat);
    }

    /**
     * Retorna todos os provedores registrados
     */
    public function getProviders(): Collection
    {
        return $this->providers;
    }

    /**
     * Define uma coleção inteira de provedores (útil para configuração)
     */
    public function setProviders(array|Collection $providers): self
    {
        $this->providers = collect($providers);
        return $this;
    }

    /**
     * Limpa todos os provedores registrados
     */
    public function clearProviders(): self
    {
        $this->providers = collect();
        return $this;
    }
}