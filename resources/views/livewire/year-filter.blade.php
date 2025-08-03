<div>
    {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
    <x-filament::input.wrapper>
    <x-filament::input.select wire:model.live="filteredYear">
        @foreach ($yearList as $year)
            <option value="{{$year}}">{{$year}}</option>

        @endforeach
    </x-filament::input.select>
</x-filament::input.wrapper>
</div>
