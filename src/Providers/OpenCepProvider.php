<?php
// src/Providers/OpenCepProvider.php
namespace PauloSanda\MultipleCepApi\Providers;

use Illuminate\Support\Facades\Http;
use PauloSanda\MultipleCepApi\Contracts\CepProviderInterface;
use PauloSanda\MultipleCepApi\Utils\CepFormatter;

class OpenCepProvider implements CepProviderInterface
{
    /**
     * URL base da API OpenCEP
     */
    private const BASE_URL = 'https://opencep.com/v1';

    /**
     * Busca endereço pelo CEP usando a API OpenCEP
     *
     * @param string $cep CEP no formato original
     * @return array|null Dados do endereço ou null se não encontrado
     */
    public function search(string $cep): ?array
    {
        try {
            // OpenCEP precisa de CEP apenas com números
            $cleanCep = CepFormatter::normalize($cep);

            $response = Http::timeout(3)
                ->get(self::BASE_URL . "/{$cleanCep}");

            if ($response->successful()) {
                $data = $response->json();
                return $this->normalizeData($data);
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Indica se existe um provedor de fallback após este
     * OpenCEP é o último da cadeia
     *
     * @return bool
     */
    public function hasFallback(): bool
    {
        return false;
    }

    /**
     * Retorna o nome amigável do provedor
     *
     * @return string
     */
    public function getName(): string
    {
        return 'OpenCEP';
    }

    /**
     * Retorna a prioridade do provedor
     * OpenCEP tem prioridade baixa (80)
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 80;
    }

    /**
     * Define se o provedor precisa do CEP formatado de maneira específica
     * OpenCEP precisa de CEP apenas com números
     *
     * @return string|null
     */
    public function getRequireCepFormat(): ?string
    {
        return CepFormatter::JUST_NUMBERS;
    }

    /**
     * Normaliza os dados da API OpenCEP para o formato padrão do pacote
     *
     * @param array $data Dados brutos da API OpenCEP
     * @return array Dados normalizados
     */
    private function normalizeData(array $data): array
    {
        return [
            'cep'         => $data['cep'] ?? '',
            'logradouro'  => $data['logradouro'] ?? '',
            'bairro'      => $data['bairro'] ?? '',
            'cidade'      => $data['localidade'] ?? '',
            'estado'      => $data['uf'] ?? '',
            'complemento' => $data['complemento'] ?? '',
            'provider'    => 'opencep',
        ];
    }
}