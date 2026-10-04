@props(['search' => ''])

@php
    $status = session('newsletter_status');
    $serverError = session('newsletter_error');
@endphp

<aside class="space-y-6 lg:sticky lg:top-28">
    <div class="bg-white border border-outline-variant/20 rounded-lg shadow-sm p-6">
        <h2 class="font-headline-lg text-headline-lg text-lg text-on-surface mb-4">Cari Artikel</h2>
        <form method="GET" action="{{ url('/artikel') }}" role="search" class="relative">
            <label for="artikel-search" class="sr-only">Cari artikel</label>
            <input id="artikel-search" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Ketik topik atau masalah..." class="w-full rounded-lg bg-surface-container border-0 pl-4 pr-12 py-3 text-sm text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container">
            <button type="submit" class="absolute inset-y-0 right-0 px-4 text-outline hover:text-primary transition-colors" aria-label="Cari">
                <span class="material-symbols-outlined">search</span>
            </button>
        </form>
    </div>

    <div id="langganan"
         x-data="{
             submitting: false,
             done: @js((bool) $status),
             message: @js($status),
             error: @js($serverError),
             email: @js(old('email', '')),
             honeypot: '',
             formToken: @js(\App\Services\SubmissionGuard::issueToken()),
             async submit() {
                 this.error = null;
                 this.submitting = true;

                 try {
                     const response = await fetch('{{ route('newsletter.subscribe') }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'Accept': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                         },
                         body: JSON.stringify({ email: this.email, website: this.honeypot, form_token: this.formToken }),
                     });
                     const data = await response.json();

                     if (response.ok) {
                         this.done = true;
                         this.message = data.message;
                         this.email = '';
                     } else {
                         this.error = data.errors?.email?.[0] ?? data.message ?? 'Terjadi kesalahan. Silakan coba lagi.';
                     }
                 } catch (e) {
                     this.error = 'Terjadi kesalahan. Silakan coba lagi.';
                 } finally {
                     this.submitting = false;
                 }
             },
         }"
         class="bg-primary-container text-white rounded-lg shadow-md p-6">
        <h2 class="font-headline-lg text-headline-lg text-lg mb-2 flex items-center gap-2">
            <span class="material-symbols-outlined">mail</span> Update Mingguan
        </h2>
        <p class="text-sm text-white/80 mb-5">Dapatkan tips perawatan dan promo eksklusif langsung ke email Anda.</p>

        <p x-show="done" x-cloak role="status" class="text-sm font-semibold bg-white/15 rounded-lg px-4 py-3" x-text="message">{{ $status }}</p>

        @if ($status || $serverError)
            <noscript>
                <p role="status" class="text-sm font-semibold bg-white/15 rounded-lg px-4 py-3 mb-3">{{ $status ?? $serverError }}</p>
            </noscript>
        @endif

        <form x-show="! done" method="POST" action="{{ route('newsletter.subscribe') }}" @submit.prevent="submit" class="space-y-3" novalidate>
            @csrf
            <input type="hidden" name="form_token" value="{{ \App\Services\SubmissionGuard::issueToken() }}">
            <input type="text" name="website" x-model="honeypot" tabindex="-1" autocomplete="off" class="absolute -left-[9999px] w-px h-px opacity-0" aria-hidden="true">
            <label for="newsletter-email" class="sr-only">Alamat email Anda</label>
            <input id="newsletter-email" type="email" name="email" x-model="email" value="{{ old('email') }}" required maxlength="255" placeholder="Alamat email Anda" class="w-full rounded-lg bg-white/15 border-0 px-4 py-3 text-sm text-white placeholder:text-white/60 focus:ring-2 focus:ring-white">
            <p x-show="error" x-text="error" role="alert" class="text-sm text-white font-semibold">{{ $serverError }}</p>
            <button type="submit" :disabled="submitting" class="w-full bg-white text-primary-container font-bold rounded-lg py-3 text-sm hover:bg-surface transition-colors disabled:opacity-60">
                <span x-show="! submitting">Langganan</span><span x-show="submitting" x-cloak>Mengirim...</span>
            </button>
        </form>
    </div>
</aside>
