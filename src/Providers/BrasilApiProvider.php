<?php

namespace PauloSanda\MultipleCepApi\Providers;

use PauloSanda\MultipleCepApi\Contracts\CepProviderInterface;
use Illuminate\Support\Facades\Http;
use PauloSanda\MultipleCepApi\Utils\CepFormatter;

class BrasilApiProvider implements CepProviderInterface
{
    /**
     * URL base da API BrasilAPI
     */
    private const BASE_URL = 'https://brasilapi.com.br/api/cep/v1';

    /**
     * Busca endereço pelo CEP usando a API BrasilAPI
     *
     * @param string $cep CEP no formato original
     * @return array|null Dados do endereço ou null se não encontrado
     */
    public function search(string $cep): ?array
    {
        try {
            // BrasilAPI aceita apenas CEP sem hífen
            $cleanCep = preg_replace('/[^0-9]/', '', $cep);

            $response = Http::timeout(3)
                ->get(self::BASE_URL . "/{$cleanCep}");

            if ($response->successful()) {
                $data = $response->json();
                return $this->normalizeData($data);
            }

            return null;

        } catch (\Exception $e) {
            // BrasilAPI é confiável, não logamos erros normais
            return null;
        }
    }

    /**
     * Indica se existe um provedor de fallback após este
     *
     * @return bool true - BrasilAPI tem fallback
     */
    public function hasFallback(): bool
    {
        return true;
    }

    /**
     * Retorna o nome amigável do provedor
     *
     * @return string
     */
    public function getName(): string
    {
        return 'BrasilAPI';
    }

    /**
     * Retorna a prioridade do provedor
     * BrasilAPI tem prioridade 90 (um pouco menor que ViaCEP)
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 90;
    }

    /**
     * Define se o provedor precisa do CEP formatado de maneira específica
     * BrasilAPI precisa de CEP apenas com números
     *
     * @return string|null Formato necessário ou null
     */
    public function getRequireCepFormat(): ?string
    {
        return CepFormatter::JUST_NUMBERS;
    }

    /**
     * Normaliza os dados da API BrasilAPI para o formato padrão do pacote
     *
     * @param array $data Dados brutos da API BrasilAPI
     * @return array Dados normalizados
     */
    private function normalizeData(array $data): array
    {
        return [
            'cep'         => $data['cep'] ?? '',
            'logradouro'  => $data['street'] ?? $data['address'] ?? '',
            'bairro'      => $data['neighborhood'] ?? '',
            'cidade'      => $data['city'] ?? '',
            'estado'      => $data['state'] ?? '',
            'complemento' => $data['complement'] ?? '',
            'provider'    => 'brasilapi',
        ];
    }
}