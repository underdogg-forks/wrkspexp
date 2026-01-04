<x-filament-panels::page>
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach($this->getCompanies() as $company)
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ $company->name }}
                    </h3>
                </div>
                
                @if($company->description)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ $company->description }}
                    </p>
                @endif

                @if($company->email)
                    <p class="text-sm text-gray-500 dark:text-gray-500 mb-2">
                        {{ $company->email }}
                    </p>
                @endif

                <div class="mt-4">
                    <a href="{{ route('filament.company.pages.dashboard', ['tenant' => $company->slug]) }}" 
                       class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        Open Workspace
                        <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    @if($this->getCompanies()->isEmpty())
        <div class="rounded-lg border border-gray-200 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No companies</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                You don't have access to any companies yet.
            </p>
        </div>
    @endif
</x-filament-panels::page>
