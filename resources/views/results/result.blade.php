{{--
    @var \App\Models\Category[] $result
--}}
<x-layout>
    @vite('resources/scss/app.scss')
    {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}
    <div class="">
        <div class="container">
            <div class="columns is-centered">
                <div class="column">
                    <!-- Page that displays all results for each year -->
                    <h1>Results for year of {{ $year }} </h1>
                    <br>
                    <br>
                    <div :id="slug" class="awardSectionContainer py-6">
                        @foreach ($categorylist as $resultcategory)
                            <x-results.result-category :resultcategory='$resultcategory' />
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
