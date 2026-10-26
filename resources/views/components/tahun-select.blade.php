@props([
    'selected',
    'label' => 'Tahun (Periode Feb–Jan)',
    'maxYear' => now()->year + 1,
])

@php
    $years = collect(range($maxYear, now()->year - 5))->push((int) $selected)->unique()->sortDesc();
@endphp

<label for="tahun" class="mb-2 block text-sm font-semibold text-gray-700">{{ $label }}</label>
<select id="tahun" name="tahun" {{ $attributes->class(['block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500']) }}>
    @foreach($years as $year)
        <option value="{{ $year }}" @selected((int) $selected === $year)>{{ $year }}</option>
    @endforeach
</select>
