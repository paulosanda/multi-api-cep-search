<?php
namespace PauloSanda\MultipleCepApi\Contracts;

interface CepProviderInterface
{
    /**
     * Busca endereço pelo CEP
     *
     * @param string $cep CEP no formato original (como fornecido pelo usuário)
     * @return array|null Dados do endereço ou null se não encontrado
     */
    public function search(string $cep): ?array;

    /**
     * Indica se existe um provedor de fallback após este
     */
    public function hasFallback(): bool;

    /**
     * Retorna o nome amigável do provedor
     */
    public function getName(): string;

    /**
     * Retorna a prioridade do provedor (maior número = maior prioridade)
     */
    public function getPriority(): int;

    /**
     * Define se o provedor precisa do CEP formatado de maneira específica
     * Retorna o formato necessário ou null para usar o padrão
     */
    public function getRequireCepFormat(): ?string;
}
