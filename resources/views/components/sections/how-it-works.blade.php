@php
    $steps = [
        ['number' => '01', 'title' => 'Panel & PV Cell', 'description' => 'Menyerap sinar matahari dan mengubahnya menjadi energi listrik searah (DC).', 'rotate' => '-rotate-1', 'mt' => 'md:mt-0', 'emphasized' => false],
        ['number' => '02', 'title' => 'DC Power', 'description' => 'Aliran listrik DC mengalir aman melalui kabel khusus menuju inverter utama.', 'rotate' => 'rotate-2', 'mt' => 'md:mt-12', 'emphasized' => false],
        ['number' => '03', 'title' => 'Inverter', 'description' => 'Jantung sistem. Mengubah arus DC menjadi arus bolak-balik (AC) untuk alat elektronik.', 'rotate' => '-rotate-2', 'mt' => 'md:mt-4', 'emphasized' => true],
        ['number' => '04', 'title' => 'Storage / Grid', 'description' => 'Energi digunakan langsung, disimpan di baterai, atau diekspor ke PLN (net-metering).', 'rotate' => 'rotate-1', 'mt' => 'md:mt-16', 'emphasized' => false],
    ];
@endphp
<section class="reveal-element py-24 px-6 overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-20">
            <h2 class="font-headline-xl text-3xl md:text-5xl font-extrabold tracking-tight mb-4 text-primary">Sederhana dan Mulus</h2>
            <p class="text-base md:text-lg font-medium max-w-2xl mx-auto text-secondary">Bagaimana cahaya matahari bertransformasi menjadi energi andal untuk rumah dan bisnis Anda.</p>
        </div>

        <div class="relative flex flex-col md:flex-row justify-between items-center md:items-start gap-16 md:gap-4">
            <div class="hidden md:block absolute top-12 left-10 right-10 h-0.5 border-t-2 border-dashed border-primary/20 -z-10"></div>

            @foreach ($steps as $step)
                <div class="w-full md:w-1/4 relative group {{ $step['mt'] }} transition-transform duration-300 hover:-translate-y-2">
                    <div @class([
                        'w-20 h-20 rounded-full border-4 border-surface shadow-sm flex items-center justify-center font-headline-lg text-2xl mx-auto mb-6 group-hover:scale-110 group-hover:shadow-md transition-all z-10 relative',
                        'text-white bg-primary-container' => $step['emphasized'],
                        'bg-white text-primary group-hover:border-primary-container' => ! $step['emphasized'],
                    ])>{{ $step['number'] }}</div>
                    <div class="bg-white p-6 shadow-md text-center {{ $step['rotate'] }} group-hover:shadow-lg transition-shadow rounded-lg">
                        <h4 class="font-bold text-lg text-primary mb-2">{{ $step['title'] }}</h4>
                        <p class="text-sm text-on-surface-variant leading-relaxed">{{ $step['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
