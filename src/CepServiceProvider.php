<?php

namespace PauloSanda\MultipleCepApi;

use Illuminate\Support\ServiceProvider;

class CepServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CepService::class, function ($app) {
            $service = new CepService();

            // Carrega provedores da configuração
            $providersConfig = config('cep.providers', []);

            // Se não houver configuração, usa provedores padrão
            if (empty($providersConfig)) {
                $providersConfig = $this->getDefaultProviders();
            }

            foreach ($providersConfig as $providerClass) {
                if (class_exists($providerClass)) {
                    $service->addProvider(new $providerClass());
                }
            }

            return $service;
        });

        // Opcional: Registrar uma action/facade se desejar
        // $this->app->bind(\PauloSanda\MultipleCepApi\CepGetAction::class, ...);
    }

    public function boot(): void
    {
        // Publica o arquivo de configuração
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cep.php' => config_path('cep.php'),
            ], 'cep-config');
        }
    }

    /**
     * Retorna a lista de provedores padrão
     */
    private function getDefaultProviders(): array
    {
        return [
            // Aqui você lista os provedores que vêm com o pacote
            // Exemplo (quando você criar):
            // \PauloSanda\MultipleCepApi\Providers\ViaCepProvider::class,
            // \PauloSanda\MultipleCepApi\Providers\BrasilApiProvider::class,
        ];
    }
}