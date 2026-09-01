<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Hanya menampilkan halaman Kontak. Submit sungguhan (simpan + notifikasi)
     * sengaja tidak diimplementasikan di fitur ini — form tervalidasi
     * sepenuhnya di sisi client (FR-007, lihat research.md §7). Ditunda ke
     * AMC-216.
     */
    public function __invoke(): View
    {
        return view('pages.kontak');
    }
}
