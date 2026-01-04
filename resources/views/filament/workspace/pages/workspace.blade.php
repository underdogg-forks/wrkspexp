<x-filament-panels::page>
    <x-filament-tables::container>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($this->getCompanies() as $company)
                <x-filament::section
                    :heading="$company->name"
                    :description="$company->description"
                    class="hover:shadow-lg transition-shadow"
                >
                    <x-slot name="headerEnd">
                        <x-filament::button
                            :href="route('filament.company.pages.dashboard', ['tenant' => $company->slug])"
                            icon="heroicon-m-arrow-right"
                            tag="a"
                        >
                            {{ trans('workspace.open_workspace') }}
                        </x-filament::button>
                    </x-slot>

                    @if($company->communicatables->isNotEmpty())
                        <x-filament::badge class="mb-2">
                            {{ $company->communicatables->first()->value }}
                        </x-filament::badge>
                    @endif
                </x-filament::section>
            @empty
                <x-filament::section
                    :heading="trans('workspace.no_companies')"
                    :description="trans('workspace.no_companies_description')"
                    icon="heroicon-o-building-office-2"
                    icon-color="gray"
                    class="col-span-full"
                />
            @endforelse
        </div>
    </x-filament-tables::container>
</x-filament-panels::page>
