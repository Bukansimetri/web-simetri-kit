@props([
    'title' => 'Siap beralih ke energi matahari?',
    'description' => 'Tim ahli kami siap membantu menganalisa kebutuhan dan memberikan desain sistem gratis.',
    'buttonLabel' => 'Konsultasi Gratis',
    'buttonHref' => url('/kontak'),
])

<section class="py-24 px-6 max-w-5xl mx-auto">
    <div class="bg-primary p-12 md:p-16 text-center relative overflow-hidden shadow-md rounded-lg">
        <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-primary-container rounded-full blur-3xl opacity-40"></div>

        <div class="relative z-10">
            <h2 class="font-headline-lg text-headline-lg md:text-4xl text-white mb-4">{{ $title }}</h2>
            <p class="text-white/80 mb-8 max-w-xl mx-auto">{{ $description }}</p>
            <a href="{{ $buttonHref }}" class="btn-fill inline-block bg-primary-container font-bold px-8 py-4 hover:scale-105 transition-transform shadow-lg text-white rounded-lg">
                {{ $buttonLabel }}
            </a>
        </div>
    </div>
</section>
