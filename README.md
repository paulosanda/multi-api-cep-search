# Laravel Multiple CEP API

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-10%2B%20%7C%2011%2B-red)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

Um pacote SOLID para busca de CEP no Laravel com múltiplos provedores e fallback automático.

## 📦 Instalação

\`\`\`bash
composer require paulosanda/laravel-multiple-cep-api
\`\`\`

O pacote usa Package Discovery do Laravel. Não precisa registrar manualmente.

## 🚀 Uso Básico

\`\`\`php
use PauloSanda\MultipleCepApi\CepService;

$cepService = app(CepService::class);
$endereco = $cepService->search('01001000');

// Ou via Facade (se configurada no composer.json):
// $endereco = \Cep::search('01001-000');

if ($endereco) {
echo $endereco['logradouro'];    // Rua
echo $endereco['bairro'];        // Bairro  
echo $endereco['cidade'];        // Cidade
echo $endereco['estado'];        // Estado
echo $endereco['provider'];      // Provedor que encontrou
}
\`\`\`

## ⚙️ Configuração (Opcional)

\`\`\`bash
php artisan vendor:publish --tag=cep-config
\`\`\`

Edite \`config/cep.php\`:

\`\`\`php
return [
'providers' => [
\PauloSanda\MultipleCepApi\Providers\ViaCepProvider::class,
\PauloSanda\MultipleCepApi\Providers\BrasilApiProvider::class,
\PauloSanda\MultipleCepApi\Providers\OpenCepProvider::class,
// Adicione provedores customizados aqui
],
'timeout' => 3,
'retry_attempts' => 3,
];
\`\`\`

## 🔌 Provedores Padrão

| Provedor | Prioridade | Fallback | Formato |
|----------|-----------|----------|---------|
| **ViaCEP** | 100 | Sim | Qualquer |
| **BrasilAPI** | 90 | Sim | Apenas números |
| **OpenCEP** | 80 | Não | Apenas números |

## 🛠️ Criando Provedores Customizados

1. Implemente a interface:

\`\`\`php
use PauloSanda\MultipleCepApi\Contracts\CepProviderInterface;
use PauloSanda\MultipleCepApi\Utils\CepFormatter;

class MeuProvedorCustomizado implements CepProviderInterface
{
public function search(string $cep): ?array
{
// Sua implementação
}

    public function hasFallback(): bool
    {
        return true;
    }
    
    public function getName(): string
    {
        return 'MeuProvedor';
    }
    
    public function getPriority(): int
    {
        return 95;
    }
    
    public function getRequireCepFormat(): ?string
    {
        return CepFormatter::JUST_NUMBERS;
    }
}
\`\`\`

2. Adicione ao config:

\`\`\`php
'providers' => [
\PauloSanda\MultipleCepApi\Providers\ViaCepProvider::class,
\App\Providers\MeuProvedorCustomizado::class,
\PauloSanda\MultipleCepApi\Providers\BrasilApiProvider::class,
],
\`\`\`


## ⚠️ Importante

Este pacote usa **Facades do Laravel** (\`Http\`, \`Log\`):
- Funciona apenas dentro de aplicações Laravel
- Testes via CLI puro não funcionam sem Laravel

## 📁 Estrutura

\`\`\`
src/
├── CepService.php              # Serviço principal
├── CepServiceProvider.php      # ServiceProvider
├── Contracts/
│   └── CepProviderInterface.php
├── Providers/
│   ├── ViaCepProvider.php
│   ├── BrasilApiProvider.php  
│   └── OpenCepProvider.php
├── Utils/
│   └── CepFormatter.php
└── Facades/
└── Cep.php
\`\`\`

## 🔧 Princípios SOLID

1. **Single Responsibility**: Cada classe tem uma responsabilidade
2. **Open/Closed**: Aberto para novos provedores
3. **Liskov Substitution**: Provedores substituíveis
4. **Interface Segregation**: Interface específica
5. **Dependency Inversion**: Depende de abstrações

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch (\`git checkout -b feature/MinhaFeature\`)
3. Commit (\`git commit -m 'Adiciona MinhaFeature'\`)
4. Push (\`git push origin feature/MinhaFeature\`)
5. Abra um Pull Request

## 📄 Licença

MIT - veja [LICENSE](LICENSE) para detalhes.

## 👨‍💻 Autor

**Paulo Sanda** - [GitHub](https://github.com/paulosanda)