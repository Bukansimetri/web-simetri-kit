@php
    $reasons = [
        ['icon' => 'savings', 'title' => 'Efisien & Terjangkau', 'description' => 'Turunkan tagihan listrik hingga 80% dengan panel efisiensi tinggi berteknologi monokristalin terbaru.', 'emphasized' => false],
        ['icon' => 'verified', 'title' => 'Garansi Panjang', 'description' => 'Ketenangan pikiran dengan garansi performa panel hingga 25 tahun dan garansi pengerjaan profesional.', 'emphasized' => true],
        ['icon' => 'eco', 'title' => 'Ramah Lingkungan', 'description' => 'Kurangi jejak karbon Anda. Satu instalasi setara dengan menanam puluhan pohon setiap tahunnya.', 'emphasized' => false],
    ];
@endphp
<section class="py-24 px-6 max-w-7xl mx-auto">
    <div class="mb-16 md:w-1/2">
        <h2 class="font-headline-lg text-headline-lg text-primary mb-4">Mengapa Beralih<br>Bersama SUOER?</h2>
        <p class="text-lg font-body-md text-body-md text-on-surface-variant">Investasi cerdas untuk masa depan, dirancang dengan presisi untuk kondisi iklim Indonesia.</p>
    </div>

    <div class="grid md:grid-cols-3 gap-8">
        @foreach ($reasons as $reason)
            <div @class([
                'p-8 shadow-md transition-all duration-300 rounded-lg',
                'bg-primary-container -translate-y-4 shadow-lg' => $reason['emphasized'],
                'bg-white border border-gray-100 hover:-translate-y-4 hover:shadow-lg' => ! $reason['emphasized'],
            ])>
                <div @class([
                    'w-14 h-14 rounded-lg flex items-center justify-center mb-6',
                    'bg-white/30' => $reason['emphasized'],
                    'bg-surface-container-low text-primary' => ! $reason['emphasized'],
                ])>
                    <span @class(['material-symbols-outlined text-3xl', 'text-white' => $reason['emphasized']])>{{ $reason['icon'] }}</span>
                </div>
                <h3 @class(['font-headline-lg text-xl mb-3', 'text-white' => $reason['emphasized'], 'text-primary' => ! $reason['emphasized']])>
                    {{ $reason['title'] }}
                </h3>
                <p @class(['font-medium' => $reason['emphasized'], 'text-white/90' => $reason['emphasized'], 'text-on-surface-variant' => ! $reason['emphasized']])>
                    {{ $reason['description'] }}
                </p>
            </div>
        @endforeach
    </div>
</section>
