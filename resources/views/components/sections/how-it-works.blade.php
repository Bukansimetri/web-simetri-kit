@php
    $steps = [
        ['number' => '01', 'title' => 'Panel & PV Cell', 'description' => 'Menyerap sinar matahari dan mengubahnya menjadi energi listrik searah (DC).'],
        ['number' => '02', 'title' => 'DC Power', 'description' => 'Aliran listrik DC mengalir aman melalui kabel khusus menuju inverter utama.'],
        ['number' => '03', 'title' => 'Inverter', 'description' => 'Jantung sistem. Mengubah arus DC menjadi arus bolak-balik (AC) untuk alat elektronik.'],
        ['number' => '04', 'title' => 'Storage / Grid', 'description' => 'Energi digunakan langsung, disimpan di baterai, atau diekspor ke PLN (net-metering).'],
    ];
@endphp
<section class="py-24 px-6 overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-20">
            <h2 class="font-headline-lg text-headline-lg text-primary mb-4">Sederhana dan Mulus</h2>
            <p class="text-lg font-body-md text-body-md text-on-surface-variant">Bagaimana cahaya matahari berubah menjadi energi di rumah Anda.</p>
        </div>

        <div class="relative flex flex-col md:flex-row justify-between items-center md:items-start gap-16 md:gap-4">
            <div class="hidden md:block absolute top-12 left-10 right-10 h-0.5 border-t-2 border-dashed border-primary/20 -z-10"></div>

            @foreach ($steps as $step)
                <div class="w-full md:w-1/4 relative group transition-transform duration-300 hover:-translate-y-2">
                    <div class="w-20 h-20 bg-white rounded-full border-4 border-surface shadow-sm flex items-center justify-center font-headline-lg text-2xl text-primary mx-auto mb-6 group-hover:scale-110 group-hover:border-primary-container group-hover:shadow-md transition-all z-10 relative">
                        {{ $step['number'] }}
                    </div>
                    <div class="bg-white p-6 shadow-md text-center group-hover:shadow-lg transition-shadow rounded-lg">
                        <h4 class="font-bold text-lg text-primary mb-2">{{ $step['title'] }}</h4>
                        <p class="text-sm font-body-sm text-body-sm text-on-surface-variant">{{ $step['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
