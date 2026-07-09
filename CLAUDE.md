# CLAUDE.md — FormFlow (Dynamic Form & Assessment System)

Sistem form builder dinamis ala Tally: form penilaian/survey dibuat via builder tanpa kode, dipublish via link/QR, jawaban dimonitor & di-export dari backoffice. Referensi lengkap: `prd.md` (baca sebelum implement fitur baru).

## Stack

- **Laravel 13** (PHP 8.4), monolith server-rendered
- **PostgreSQL 16** — JSONB untuk schema dinamis, `timestamptz` semua kolom waktu
- **Blade + Tailwind CSS + Alpine.js** — NO React/Vue/Livewire/Inertia
- **Spatie Permission** untuk roles, **Laravel Breeze** untuk auth
- **Yajra DataTables** (server-side) untuk tabel backoffice
- **maatwebsite/excel** untuk export, **Chart.js** untuk analitik
- **Laravel Queue** (database driver) untuk export besar & notifikasi
- Docker + GitHub Actions untuk deploy

## UI Enhancement Stack (Opsi 1 — wajib pakai ini, jangan tambah framework lain)

| Kebutuhan | Library |
|---|---|
| Animasi show/hide field (conditional logic) | Alpine `x-transition` — field WAJIB muncul/hilang smooth, jangan jeglek |
| Keyboard nav & focus trap (modal builder) | `@alpinejs/focus` |
| Accordion section builder | `@alpinejs/collapse` |
| Drag-and-drop reorder field | **SortableJS** (ghost class + drag animation) |
| Transisi antar halaman form multi-page | **Motion One** atau CSS View Transitions — bikin form publik terasa Typeform-like |
| Toast feedback (autosave "Tersimpan ✓") | **Notyf** |
| Tooltip builder | **Tippy.js** |
| Komponen backoffice siap pakai | **Preline UI** (copy-paste, ganti token warna ke palet Heaven Scent) |

State builder yang kompleks → SATU `Alpine.store('builder')` global, bukan `x-data` tersebar. Extract ke `resources/js/builder/` bila > ~30 baris.

## Commands

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed          # seeder: roles + super admin
npm run dev                          # Vite
php artisan serve
php artisan queue:work               # wajib jalan untuk export & notifikasi
php artisan test                     # Pest
./vendor/bin/pint                    # code style, jalankan sebelum commit
```

## Arsitektur — Aturan Non-Negotiable

### 1. Dual-layer answer storage
Setiap submit menulis DUA tempat dalam SATU transaction:
- `responses.answers_snapshot` (JSONB `{field_key: value}`) → render detail respons
- `response_answers` (1 baris per field, `value_text`/`value_number`/`value_json`) → agregasi, filter, analitik

Analitik & filter SELALU query `response_answers` (indexed), JANGAN parse JSONB on-the-fly. Render detail respons SELALU dari snapshot.

### 2. Form versioning
Form yang sudah punya respons dan strukturnya diubah (field ditambah/dihapus/tipe berubah) → buat snapshot di `form_versions` dulu, baru apply perubahan. Respons terikat ke `form_version` saat submit. Jangan pernah mutate/hapus data respons lama karena perubahan struktur form.

### 3. Server adalah source of truth untuk validasi
Validasi submit dibangun DINAMIS dari definisi `form_fields` via `DynamicValidatorBuilder` (bukan FormRequest statis). Aturan:
- Field tersembunyi oleh conditional logic → skip required, BUANG jawabannya server-side
- Conditional logic dievaluasi ulang di server saat submit, jangan percaya klien
- Value checkbox/radio/dropdown divalidasi terhadap `form_field_options` yang valid

### 4. Scoring (assessment mode)
- Skor dihitung server-side saat submit: `total = Σ (nilai_field × weight)`, simpan ke `responses.score` + `score_breakdown` (JSONB)
- Bobot diubah setelah ada respons → skor lama TIDAK berubah otomatis. Rekalkulasi hanya via aksi eksplisit + audit log

### 5. Idempotent submit
Token submit sekali-pakai di form. Double submit → return halaman sukses yang sama, jangan buat respons duplikat. Kuota respons dicek dalam transaction (`SELECT ... FOR UPDATE`).

## Controller Patterns

**Thin Controller + Service Class.** Controller cuma: terima request → authorize → delegasi ke Service → return response. Logic bisnis hidup di `app/Services/`.

Tiga jenis controller berdasarkan output:
1. **Page controller** → return Blade view (resource controller biasa)
2. **DataTables controller** → return Yajra JSON, khusus endpoint `.../data`
3. **JSON action controller** → untuk Alpine (reorder, autosave, CRUD field). Return `response()->json()` polos — internal endpoint, bukan public API

Aturan:
- **Authorization via Policy**, bukan cek manual: `FormPolicy` dengan `update`, `viewResponses`, `export`, `deleteResponse`. Controller tinggal `$this->authorize(...)`. Policy adalah SATU-SATUNYA tempat logic akses (owner / shared editor / shared viewer / admin) hidup
- **FormRequest** untuk input builder (`StoreFieldRequest`, `UpdateFieldRequest`) — validasi tipe field, config sesuai tipe, field_key unik
- Validasi submit respons JANGAN pakai FormRequest statis — itu tugas `DynamicValidatorBuilder`

## Prinsip SOLID (terapkan pragmatis, bukan dogmatis)

- **S — Single Responsibility:** satu Service satu tanggung jawab. `FormSubmissionService` (orkestrasi submit), `ScoreCalculator`, `DynamicValidatorBuilder`, `ConditionalLogicEvaluator`, `ResponseExporter`, `FormVersionManager`. Jangan bikin `FormService` gado-gado 800 baris
- **O — Open/Closed via Field Type Registry:** setiap tipe field adalah class yang implement `FieldTypeInterface`:
  ```php
  interface FieldTypeInterface {
      public function rules(FormField $field): array;          // validasi
      public function normalize(mixed $input, FormField $field): mixed; // ke format snapshot
      public function toAnswerColumns(mixed $value): array;    // value_text/number/json
      public function score(mixed $value, FormField $field): ?float;
      public function aggregate(Collection $answers, FormField $field): array; // untuk analitik
  }
  ```
  Daftarkan di `FieldTypeRegistry`. Nambah tipe field baru = nambah 1 class + 1 Blade component, TANPA menyentuh service lain. Ini pola paling penting di codebase ini
- **L — Liskov:** semua FieldType class harus bisa dipakai interchangeable oleh registry — jangan ada `if ($type === 'matrix')` bertebaran di service
- **I — Interface Segregation:** interface kecil & fokus (mis. `Scorable` terpisah bila tidak semua tipe punya skor) daripada satu interface raksasa
- **D — Dependency Inversion:** Service di-inject via constructor (Laravel container), jangan `new` manual atau facade berlebihan di dalam service. Test jadi gampang di-mock

Rasional: form builder itu domain yang PASTI nambah tipe field terus — Registry pattern mencegah switch-case raksasa yang harus diedit di 6 tempat tiap nambah tipe.

## Keamanan Form Publik (kritis — endpoint tanpa auth)

- **Rate limit** submit: 10/menit/IP (`throttle` middleware, key by IP)
- **Honeypot field** tersembunyi + minimum fill time (submit < 3 detik = tolak diam-diam) — lapisan sebelum CAPTCHA
- **CAPTCHA** Cloudflare Turnstile, toggle per form
- **CSRF** semua POST; token submit sekali-pakai (idempotency)
- **Mass assignment:** JANGAN `$request->all()` → whitelist field_key dari definisi form yang valid saja; input di luar definisi dibuang
- **File upload:** validasi MIME real (finfo, bukan extension), max size dari config field, simpan di storage privat `storage/app/private/`, akses HANYA via `URL::temporarySignedRoute` (masa berlaku pendek). Nama file disimpan, path pakai hash — jangan pakai original filename di path
- **XSS:** jawaban respondent SELALU `{{ }}`, JANGAN PERNAH `{!! !!}` untuk konten user. Termasuk saat render di DataTables (escape di server, `rawColumns` hanya untuk kolom actions buatan sendiri)
- **Exposure:** URL publik pakai `uuid`/`slug`, JANGAN bigint id. Response detail publik (halaman sukses/resume) hanya via `resume_token` unguessable (64 char random)
- **IP privacy:** simpan hash SHA-256 + app salt, bukan plaintext
- **SQL injection:** query builder/Eloquent binding selalu; JSONB query pakai parameter binding (`whereRaw` dengan `?`), jangan interpolasi string
- **Enumeration:** form draft/closed → 404 (bukan 403) agar slug tidak bisa di-enumerate statusnya
- Semua aksi destruktif + export → tulis `audit_logs`

## Performa Backoffice — Data Harus Cepat

### Anti N+1 (WAJIB)
- `Model::preventLazyLoading(!app()->isProduction())` di `AppServiceProvider` — N+1 langsung throw exception saat dev. Ini non-negotiable dari Phase 1
- Eager load eksplisit di semua listing: `Form::withCount('responses')->with('creator')`, `responses()->with(['user', 'files'])`
- Yajra: SELALU `DataTables::eloquent($query)` dengan query yang sudah `with()`/`withCount()` — jangan panggil relasi di dalam `addColumn` tanpa eager load
- Render kolom dinamis tabel respons: baca dari `answers_snapshot` (sudah di row, zero query tambahan), JANGAN join/query `response_answers` per baris

### Query & Index
- Agregasi analitik: raw query ke `response_answers` dengan `GROUP BY field_id` — SATU query per chart, bukan per opsi
- Definisi form (fields + options) di halaman publik: cache `Cache::remember("form:{$id}:v{$version}", ...)`, invalidate saat form diedit. Halaman publik idealnya 1–2 query saja
- `select()` kolom yang dibutuhkan di listing besar — jangan `SELECT *` untuk tabel dengan JSONB besar (snapshot bisa puluhan KB/row); ambil snapshot hanya di detail view
- Index wajib sesuai prd.md 5.2 (termasuk GIN untuk JSONB) — cek `EXPLAIN ANALYZE` untuk query analitik sebelum merge
- Pagination server-side selalu (DataTables sudah handle) — jangan pernah load semua respons ke memory; export besar via chunk + queue

### Target
- Tabel respons 50k rows: first paint < 1 s, page DataTables < 300 ms
- Agregasi analitik 50k respons < 2 s
- Form publik: TTFB < 300 ms, JS < 200 KB

## Struktur Menu & Halaman

### Sidebar Backoffice (`/app`)

| Menu | Route | Akses | Isi |
|---|---|---|---|
| **Dashboard** | `/app/dashboard` | semua role | Ringkasan lintas form: form aktif, respons minggu ini, form segera ditutup, aktivitas terbaru |
| **Form Saya** | `/app/forms` | Creator+ | Daftar form milik sendiri (card/table): judul, status badge, jumlah respons, aksi cepat (edit, share, lihat respons, duplikat) |
| **Dibagikan ke Saya** | `/app/forms?filter=shared` | semua role | Form yang di-share sebagai viewer/editor |
| **Template** | `/app/templates` | Creator+ | Galeri template + tombol "Pakai template" |
| **Riwayat Export** | `/app/exports` | semua role | Daftar job export sendiri: status, link download, expired |
| **Administrasi** ▾ | — | Super Admin | Grup menu di bawah |
| ↳ Pengguna & Role | `/app/admin/users` | Super Admin | CRUD user, assign role Spatie, aktif/nonaktif |
| ↳ Audit Log | `/app/admin/audit` | Super Admin | Log immutable, filter by user/aksi/tanggal |
| ↳ Konfigurasi | `/app/admin/settings` | Super Admin | Max upload size, retensi export, email sender, default CAPTCHA |
| ↳ Trash | `/app/admin/trash` | Super Admin | Form soft-deleted, restore ≤ 30 hari |

Visibilitas menu dikontrol via `@can` / Spatie permission di Blade sidebar component — JANGAN hardcode cek role.

### Tab dalam Konteks Satu Form (`/app/forms/{form}/...`)

Setelah masuk sebuah form, navigasi pindah ke tab horizontal (layout `form-context`):

| Tab | Route | Akses | Isi |
|---|---|---|---|
| **Builder** | `.../build` | owner/editor | Canvas builder: daftar field drag-and-drop (kiri: palette tipe field, tengah: canvas, kanan: panel properti field aktif — label, required, config, logic) |
| **Pengaturan** | `.../settings` | owner/editor | Akses (public/internal/token), jadwal buka-tutup, kuota, one-response, pesan sukses, CAPTCHA, assessment mode + grade mapping, notifikasi |
| **Preview** | `.../preview` | owner/editor/viewer | Render form asli, toggle viewport desktop/mobile, tanpa menyimpan respons |
| **Bagikan** | `.../share` | owner/editor | URL publik + copy, QR code, kelola kolaborator (tambah user/role sebagai viewer/editor) |
| **Respons** | `.../responses` | owner/editor/viewer | DataTables respons: kolom dinamis + column picker, filter (tanggal, status, field value, versi), badge new, aksi (detail, flag, hapus). Klik baris → detail respons |
| **Analitik** | `.../analytics` | owner/editor/viewer | Ringkasan (total, hari ini, completion rate, durasi rata-rata), chart respons/hari, agregasi per pertanyaan, distribusi skor (assessment) |
| **Export** | tombol di tab Respons | owner/editor/viewer | Modal pilih format XLSX/CSV, hormati filter aktif; > 5.000 baris → queued + notifikasi |

Header konteks form (selalu tampil di atas tab): judul form, status badge (draft/published/closed), tombol Publish/Unpublish, link "← Kembali ke Form Saya".

### Halaman Publik (tanpa auth, layout terpisah `public`)

| Halaman | Route | Catatan |
|---|---|---|
| Isi form | `/f/{slug}` | Multi-page dengan progress bar bila ada page break |
| Submit sukses | redirect setelah POST | Pesan custom + skor (bila diaktifkan) + redirect URL opsional |
| Lanjutkan draft / edit | `/f/{slug}/resume/{token}` | Hanya via resume_token |
| Form ditutup | `/f/{slug}` saat closed | Pesan custom "form ditutup"; draft/archived → 404 |

Layout publik TIDAK memakai sidebar/nav backoffice — halaman standalone, branding Heaven Scent, tanpa link ke `/app`.

## Konvensi Kode

- Route backoffice prefix `/app`, middleware `auth` + permission Spatie. Route publik `/f/{slug}` tanpa auth
- Model: soft delete untuk `forms` & `form_fields`; `uuid` untuk exposure publik, `id` bigint internal
- Migration: `timestamptz`, index sesuai prd.md 5.2
- Blade components: satu component per field type untuk render publik (`x-fields.short-text`, `x-fields.radio`, dst.) — dipetakan oleh `FieldTypeRegistry`
- Enum status pakai string constant di Model (`Form::STATUS_DRAFT`) — konsisten dengan sistem internal lain
- Copy UI: Bahasa Indonesia. Timezone tampilan Asia/Jakarta, simpan UTC

## UI / Brand

- Form publik: Playfair Display (heading), Inter (body), palet warm charcoal `#2B2B2B` / gold `#C6A961` / ivory cream `#FAF7F0`. Mobile-first
- Backoffice: clean monochrome, fungsional, Preline + DataTables + Tailwind
- Definisikan palet sebagai Tailwind theme token (`charcoal`, `gold`, `ivory`) di `tailwind.config` — jangan hardcode hex di Blade

## Testing

- Pest. Prioritas coverage: `DynamicValidatorBuilder` (semua tipe field + logic), `FormSubmissionService` (transaction, idempotency, one-response rule, kuota race), `ScoreCalculator`, `FieldTypeRegistry` (tiap tipe: rules/normalize/toAnswerColumns), versioning flow
- Factory untuk form + fields semua tipe wajib ada sejak Phase 1
- Test N+1: pakai `preventLazyLoading` + assertion query count (`DB::enableQueryLog`) di test listing

## Jangan

- Jangan tambah framework JS (React/Vue/Livewire/Inertia) — stack UI hanya yang ada di tabel UI Enhancement Stack
- Jangan bikin REST API publik — internal JSON endpoint untuk Alpine/DataTables saja
- Jangan query agregasi dari JSONB snapshot
- Jangan lazy-load relasi (N+1) — preventLazyLoading aktif
- Jangan `$request->all()` untuk data submit
- Jangan hard delete form/field yang punya respons
- Jangan render jawaban user tanpa escape
- Jangan `if/switch` per tipe field di service — semua lewat `FieldTypeRegistry`
- Jangan skip queue untuk export > 5.000 baris

## Referensi Cepat

- Field types + kontrak nilai JSONB: `prd.md` section 4.1.2 & 5.3
- Skema DB lengkap + index: `prd.md` section 5.2
- Edge cases wajib: `prd.md` section 8 (double submit, kuota race, hidden required, orphan file, opsi dihapus)
- Milestones: `prd.md` section 9 — kerjakan sesuai fase, jangan lompat
