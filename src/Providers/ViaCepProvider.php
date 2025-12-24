<?php

namespace PauloSanda\MultipleCepApi\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use PauloSanda\MultipleCepApi\Contracts\CepProviderInterface;
use PauloSanda\MultipleCepApi\Utils\CepFormatter;

class ViaCepProvider implements CepProviderInterface
{
    /**
     * URL base da API ViaCEP
     */
    private const BASE_URL = 'https://viacep.com.br/ws';

    /**
     * Busca endereço pelo CEP usando a API ViaCEP
     *
     * @param string $cep CEP no formato original
     * @return array|null Dados do endereço ou null se não encontrado
     */
    public function search(string $cep): ?array
    {
        try {
            // ViaCEP aceita CEP com ou sem hífen
            $response = Http::timeout(3)
                ->get(self::BASE_URL . "/{$cep}/json");

            if ($response->successful()) {
                $data = $response->json();

                // ViaCEP retorna {"erro": true} quando CEP não existe
                if (isset($data['erro']) && $data['erro'] === true) {
                    return null;
                }

                return $this->normalize($data);
            }

            return null;

        } catch (\Exception $e) {
            Log::warning('ViaCEP falhou', [
                'cep' => $cep,
                'erro' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Indica se existe um provedor de fallback após este
     *
     * @return bool Sempre true - ViaCEP é o primeiro, mas tem fallback
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
        return 'ViaCEP';
    }

    /**
     * Retorna a prioridade do provedor
     * ViaCEP tem prioridade 100 (alta) por ser gratuito e confiável
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 100;
    }

    /**
     * Define se o provedor precisa do CEP formatado de maneira específica
     * ViaCEP aceita CEP com ou sem hífen, então retorna null
     *
     * @return string|null
     */
    public function getRequireCepFormat(): ?string
    {
        return CepFormatter::ANY;
    }

    /**
     * Normaliza os dados da API ViaCEP para o formato padrão do pacote
     *
     * @param array $dados Dados brutos da API ViaCEP
     * @return array Dados normalizados
     */
    private function normalize(array $dados): array
    {
        return [
            'cep'         => $dados['cep'] ?? '',
            'logradouro'  => $dados['logradouro'] ?? '',
            'bairro'      => $dados['bairro'] ?? '',
            'cidade'      => $dados['localidade'] ?? '',
            'estado'      => $dados['uf'] ?? '',
            'complemento' => $dados['complemento'] ?? '',
            'provider'    => 'viacep', // Identificador interno
        ];
    }
}