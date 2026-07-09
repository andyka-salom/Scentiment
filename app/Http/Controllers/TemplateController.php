<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    public function index()
    {
        // Built-in templates (seed-able later)
        $templates = [
            [
                'id' => 'penilaian_karyawan',
                'title' => 'Form Penilaian Karyawan',
                'description' => 'Template form penilaian kinerja karyawan tahunan dengan bobot skor.',
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => 'indigo',
                'questions' => 12,
                'category' => 'HR',
            ],
            [
                'id' => 'survey_kepuasan',
                'title' => 'Survey Kepuasan Pelanggan',
                'description' => 'Survey singkat untuk mengukur tingkat kepuasan dan NPS pelanggan.',
                'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                'color' => 'yellow',
                'questions' => 8,
                'category' => 'Marketing',
            ],
            [
                'id' => 'audit_toko',
                'title' => 'Audit Toko / Checklist QC',
                'description' => 'Checklist inspeksi toko harian untuk memastikan standar kualitas terpenuhi.',
                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                'color' => 'emerald',
                'questions' => 15,
                'category' => 'Operasional',
            ],
            [
                'id' => 'registrasi_event',
                'title' => 'Registrasi Event',
                'description' => 'Form pendaftaran peserta event dengan validasi data diri lengkap.',
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'color' => 'blue',
                'questions' => 10,
                'category' => 'Event',
            ],
            [
                'id' => 'feedback_produk',
                'title' => 'Feedback Produk',
                'description' => 'Form masukan produk dari pelanggan dengan rating per kategori.',
                'icon' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z',
                'color' => 'purple',
                'questions' => 9,
                'category' => 'Produk',
            ],
        ];

        return view('templates.index', compact('templates'));
    }
}
