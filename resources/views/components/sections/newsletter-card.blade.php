@php
    $status = session('newsletter_status');
    $serverError = session('newsletter_error');
@endphp

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
    <h3 class="font-headline-lg text-lg md:text-xl font-bold leading-snug mb-2 flex items-center gap-2">
        <span class="material-symbols-outlined">mail</span> Update Mingguan
    </h3>
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
        <input id="newsletter-email" type="email" name="email" x-model="email" value="{{ old('email') }}" required maxlength="255" placeholder="Alamat email Anda" class="form-control bg-white/15 text-white placeholder:text-white/60 focus:border-white focus:shadow-[0_0_0_2px_rgba(255,255,255,0.6)]">
        <p x-show="error" x-text="error" role="alert" class="text-sm text-white font-semibold">{{ $serverError }}</p>
        <button type="submit" :disabled="submitting" class="w-full bg-white text-primary-container font-bold rounded-lg py-3 text-sm hover:bg-surface transition-colors disabled:opacity-60">
            <span x-show="! submitting">Langganan</span><span x-show="submitting" x-cloak>Mengirim...</span>
        </button>
    </form>
</div>
