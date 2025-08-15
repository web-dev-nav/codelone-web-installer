<?php

namespace CodeLone\LaravelWebInstaller\Http\Livewire;

use Filament\Forms\Components\Wizard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class Installer extends Component implements HasForms
{
    use InteractsWithForms;

    public array $data = [];

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function mount()
    {
        $this->setDefaultValues();

        if (file_exists(storage_path('installed'))) {
            try {
                return redirect(config('installer.redirect_route', '/'));
            } catch (\Exception $exception) {
                Log::info("route not found...");
                Log::info($exception->getMessage());
                return redirect()->route('installer.success');
            }
        }
    }

    public function setDefaultValues(): void
    {
        $default = [];
        foreach (config('installer.environment.form', []) as $envKey => $config) {
            Arr::set($default, 'environment.'.$envKey,
                config($config['config_key']));
        }

        $this->form->fill($default);
    }

    public function getSteps(): array
    {
        $stepConfigs = config('installer.steps', []);
        $steps = [];
        foreach ($stepConfigs as $class) {
            $steps[] = $class::make();
        }

        return $steps;
    }

    protected function getFormSchema(): array
    {
        return [
            Wizard::make($this->getSteps())
                ->submitAction(new HtmlString(Blade::render(<<<BLADE
                    <x-filament::button
                        type="submit"
                        wire:loading.attr="disabled"
                        size="lg"
                        icon="heroicon-m-sparkles"
                        color="primary"
                    >
                        <span wire:loading.remove>Complete Installation</span>
                        <span wire:loading>Installing... Please Wait</span>
                    </x-filament::button>
                BLADE
                )))
        ];
    }

    public function save(): Redirector|Application|RedirectResponse
    {
        $inputs = $this->form->getState();

        $installationManager = app(config('installer.installation_manager'));
        $result = $installationManager->run($inputs);

        if ($result) {
            Notification::make()
                ->title('Installation Completed Successfully!')
                ->body('Your application has been installed and configured.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Installation Failed')
                ->body('There was an error during installation. Please check the logs.')
                ->danger()
                ->send();
        }

        return $installationManager->redirect();
    }

    public function dehydrate(): void
    {
        Log::info("installation dehydrate...");
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }

    public function render()
    {
        return view('laravel-web-installer::livewire.installer')
            ->layout('laravel-web-installer::layouts.app');
    }
}