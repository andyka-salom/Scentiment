# PRD — FormFlow: Dynamic Form & Assessment System

| Field | Value |
|---|---|
| **Document** | Product Requirements Document |
| **Product** | FormFlow — Dynamic Form Builder & Response Management System |
| **Version** | 1.0 |
| **Date** | 8 Juli 2026 |
| **Owner** | Team IT — Heaven Scent |
| **Status** | Draft for Development |
| **Stack** | Laravel 11 · PostgreSQL 16 · Blade + Tailwind CSS + Alpine.js · Spatie Permission |

---

## 1. Overview

### 1.1 Latar Belakang

Kebutuhan internal untuk membuat form penilaian, survey, kuisioner, dan pengumpulan data lapangan saat ini bergantung pada tools eksternal (Google Forms, Tally, dsb.) yang:

- Tidak terintegrasi dengan sistem internal dan database perusahaan.
- Tidak mendukung role-based access untuk monitoring per divisi.
- Sulit di-audit dan datanya tersebar di banyak akun.
- Tidak bisa dikustomisasi untuk kebutuhan spesifik (scoring penilaian, branching logic khusus, branding).

**FormFlow** adalah sistem form builder dinamis self-hosted, di mana admin dapat membuat form apa pun (penilaian karyawan, survey pelanggan, audit toko, checklist QC, registrasi event) tanpa menulis kode, mempublikasikannya via link publik atau internal, dan memonitor seluruh jawaban melalui backoffice terpusat dengan kemampuan filter, analitik, dan export.

### 1.2 Product Vision

> "Satu platform untuk membuat form apa pun, mengumpulkan jawaban apa pun, dan menganalisis hasilnya — tanpa menyentuh kode."

### 1.3 Goals & Success Metrics

| Goal | Metric | Target |
|---|---|---|
| Menggantikan Google Forms/Tally untuk kebutuhan internal | % form internal yang dibuat di FormFlow | ≥ 90% dalam 3 bulan |
| Form creation tanpa developer | Waktu buat form baru (10 pertanyaan) | ≤ 10 menit |
| Data terpusat & auditable | Semua respons tersimpan di 1 database | 100% |
| Monitoring real-time | Delay respons masuk → tampil di backoffice | ≤ 5 detik |
| Export mandiri oleh user | Export tanpa bantuan IT | 100% self-service |

### 1.4 Non-Goals (Out of Scope v1.0)

- Payment collection di dalam form.
- Multi-tenancy untuk perusahaan eksternal (single-org saja).
- Mobile app native (web responsive sudah cukup).
- Kolaborasi real-time multi-editor pada satu form (last-write-wins saja).
- E-signature legal-grade.

---

## 2. User Personas & Roles

### 2.1 Personas

| Persona | Deskripsi | Kebutuhan Utama |
|---|---|---|
| **Form Creator** (HR, Ops, Marketing) | Staff yang membuat form penilaian/survey | Builder intuitif, drag-and-drop, preview, share link |
| **Reviewer / Monitor** | Supervisor/manager yang memantau jawaban | Dashboard respons, filter, notifikasi, export |
| **Respondent (Internal)** | Karyawan yang mengisi form penilaian | Form cepat, mobile-friendly, bisa save draft |
| **Respondent (Publik)** | Pelanggan/eksternal yang mengisi survey | Akses tanpa login, UX bersih, ringan |
| **Super Admin (IT)** | Pengelola sistem | User management, roles, audit log, konfigurasi |

### 2.2 Roles & Permissions (Spatie Permission)

| Permission | Super Admin | Admin | Creator | Viewer |
|---|:-:|:-:|:-:|:-:|
| Manage users & roles | ✅ | ❌ | ❌ | ❌ |
| Create/edit/delete semua form | ✅ | ✅ | ❌ | ❌ |
| Create/edit/delete form milik sendiri | ✅ | ✅ | ✅ | ❌ |
| Publish/unpublish form | ✅ | ✅ | ✅ (own) | ❌ |
| View semua responses | ✅ | ✅ | ❌ | ❌ |
| View responses form milik sendiri / yang di-share | ✅ | ✅ | ✅ | ✅ |
| Export responses | ✅ | ✅ | ✅ (own/shared) | ✅ (shared) |
| Delete responses | ✅ | ✅ | ✅ (own) | ❌ |
| View audit log | ✅ | ❌ | ❌ | ❌ |

**Catatan:** Form dapat di-share ke user/role tertentu dengan level akses `viewer` atau `editor` (lihat 4.6).

---

## 3. System Architecture

### 3.1 Tech Stack

| Layer | Teknologi | Catatan |
|---|---|---|
| Backend | Laravel 11 (PHP 8.3) | Monolith, server-rendered |
| Database | PostgreSQL 16 | JSONB untuk schema form & jawaban dinamis |
| Frontend | Blade + Tailwind CSS + Alpine.js | Konsisten dengan stack internal |
| Interaktivitas builder | Alpine.js + SortableJS | Drag-and-drop reorder pertanyaan |
| Tables backoffice | Yajra DataTables (server-side) | Konsisten dengan sistem internal lain |
| Auth internal | Laravel Breeze (session) + Spatie Permission | |
| Queue | Laravel Queue (database driver, upgrade ke Redis bila perlu) | Export besar & notifikasi |
| Export | `maatwebsite/excel` (XLSX/CSV) | Queued export untuk >5.000 baris |
| File upload | Laravel Storage (local → S3-compatible bila perlu) | Untuk question type file upload |
| Charts | Chart.js | Dashboard analitik respons |
| Deployment | Docker + GitHub Actions CI/CD | Pola sama dengan Harumnya |

### 3.2 Prinsip Arsitektur Kunci: Dynamic Schema via JSONB

Form bersifat dinamis — jumlah, tipe, dan urutan pertanyaan berbeda tiap form dan dapat berubah kapan saja. Karena itu:

1. **Definisi form** disimpan sebagai baris-baris `form_fields` (relational, agar bisa di-query, di-index, dan divalidasi per field).
2. **Jawaban respons** disimpan dua lapis:
   - `responses.answers_snapshot` (JSONB) — snapshot lengkap `{field_key: value}` untuk render cepat & integritas historis.
   - `response_answers` (relational, satu baris per field per respons) — untuk agregasi, filter, dan analitik per pertanyaan.
3. **Versioning:** setiap kali form yang sudah punya respons diedit strukturnya, sistem membuat `form_versions` snapshot. Respons selalu terikat ke versi form saat submit, sehingga perubahan form tidak merusak data historis.

### 3.3 High-Level Flow

```
┌────────────┐   build    ┌─────────────┐   publish   ┌──────────────┐
│  Creator   │──────────▶│ Form Builder │───────────▶│ Public Form   │
│ (backoffice│           │  (draft)     │             │ /f/{slug}     │
└────────────┘           └─────────────┘             └──────┬───────┘
                                                            │ submit
                                                            ▼
┌────────────┐  monitor  ┌──────────────┐  store   ┌──────────────┐
│  Reviewer  │◀──────────│  Backoffice  │◀─────────│  Responses   │
│            │  export   │  Dashboard   │          │  (PostgreSQL)│
└────────────┘           └──────────────┘          └──────────────┘
```

---

## 4. Functional Requirements

### 4.1 Module: Form Builder

#### 4.1.1 Form CRUD

| ID | Requirement | Priority |
|---|---|---|
| FB-01 | Creator dapat membuat form baru dengan judul, deskripsi, dan cover (opsional). | Must |
| FB-02 | Form memiliki status: `draft`, `published`, `closed`, `archived`. | Must |
| FB-03 | Form memiliki slug unik auto-generated (editable saat draft) untuk URL publik `/f/{slug}`. | Must |
| FB-04 | Creator dapat menduplikasi form (beserta seluruh fields, tanpa responses). | Should |
| FB-05 | Creator dapat membuat form dari template (lihat 4.7). | Should |
| FB-06 | Soft delete untuk form; hard delete hanya oleh Super Admin. | Must |

#### 4.1.2 Question / Field Types

Semua tipe field disimpan dengan struktur seragam (lihat data model 5.3). Tipe yang didukung v1.0:

| Tipe | Kode | Konfigurasi Khusus |
|---|---|---|
| Short text | `short_text` | min/max length, placeholder |
| Long text | `long_text` | min/max length, rows |
| Email | `email` | validasi format |
| Number | `number` | min/max, step, satuan |
| Phone | `phone` | format Indonesia default (08xx / +62) |
| Single choice (radio) | `radio` | opsi, opsi "Lainnya" dengan input teks |
| Multiple choice (checkbox) | `checkbox` | opsi, min/max pilihan, opsi "Lainnya" |
| Dropdown | `dropdown` | opsi, searchable jika > 10 opsi |
| Linear scale | `scale` | min–max (mis. 1–5, 1–10), label ujung kiri/kanan |
| Rating (bintang) | `rating` | jumlah bintang (3–10) |
| Matrix / grid | `matrix` | baris × kolom, single/multi per baris |
| Date | `date` | min/max date |
| Time | `time` | — |
| File upload | `file` | tipe file diizinkan, max size (default 5 MB), max jumlah file |
| Section / header | `section` | judul + deskripsi, bukan input (pemisah halaman/bagian) |
| Statement / info | `statement` | teks informasi read-only, mendukung markdown ringan |
| Signature (canvas) | `signature` | disimpan sebagai PNG | 

**Requirement:**

| ID | Requirement | Priority |
|---|---|---|
| FB-10 | Setiap field dapat diberi label, deskripsi/help text, dan flag `required`. | Must |
| FB-11 | Field dapat di-reorder via drag-and-drop (SortableJS). Urutan disimpan sebagai `sort_order`. | Must |
| FB-12 | Field dapat diduplikasi dan dihapus. Penghapusan field pada form yang sudah punya respons memicu form version baru (data lama tetap utuh). | Must |
| FB-13 | Setiap field memiliki `field_key` unik per form (auto-generated dari label, editable) — dipakai sebagai key jawaban di JSONB. | Must |
| FB-14 | Opsi pilihan (radio/checkbox/dropdown/matrix) dapat ditambah, diedit, di-reorder, dan diberi bobot skor (untuk mode penilaian, lihat 4.4). | Must |

#### 4.1.3 Conditional Logic (Branching)

| ID | Requirement | Priority |
|---|---|---|
| FB-20 | Field dapat memiliki aturan visibilitas: tampil/sembunyi jika field lain memenuhi kondisi (`equals`, `not_equals`, `contains`, `greater_than`, `less_than`, `is_answered`, `is_empty`). | Must |
| FB-21 | Aturan mendukung kombinasi AND/OR sederhana (satu grup kondisi per field). | Should |
| FB-22 | Logic dievaluasi real-time di sisi klien (Alpine.js) saat pengisian, dan divalidasi ulang di server saat submit (field tersembunyi tidak boleh required-fail dan jawabannya diabaikan). | Must |
| FB-23 | Builder mencegah circular dependency antar aturan (validasi saat save). | Should |

#### 4.1.4 Multi-Page / Sections

| ID | Requirement | Priority |
|---|---|---|
| FB-30 | Field `section` dengan flag `page_break = true` memecah form menjadi beberapa halaman dengan tombol Next/Back dan progress bar. | Should |
| FB-31 | Validasi dijalankan per halaman saat Next. | Should |

#### 4.1.5 Form Settings

| ID | Requirement | Priority |
|---|---|---|
| FB-40 | **Akses:** publik (tanpa login) / internal (wajib login) / token-only (link dengan token unik per undangan). | Must |
| FB-41 | **Batas respons:** satu respons per user (internal, by user_id) atau per browser (publik, by cookie/fingerprint best-effort) — dapat diaktifkan/nonaktifkan. | Must |
| FB-42 | **Jadwal:** tanggal buka dan tutup otomatis (scheduler menutup form saat melewati `closes_at`). | Should |
| FB-43 | **Kuota:** tutup otomatis setelah N respons. | Should |
| FB-44 | **Pesan sukses** custom setelah submit + optional redirect URL. | Must |
| FB-45 | **Save draft (internal respondent):** respondent login dapat menyimpan draft dan melanjutkan nanti. | Should |
| FB-46 | **Edit response:** admin dapat mengizinkan respondent mengedit jawaban via link resume (token). | Could |
| FB-47 | **Notifikasi:** email/notifikasi in-app ke creator/watcher setiap respons baru, atau digest harian. | Should |
| FB-48 | **CAPTCHA** (Cloudflare Turnstile) untuk form publik — toggle per form. | Should |

#### 4.1.6 Preview & Publish

| ID | Requirement | Priority |
|---|---|---|
| FB-50 | Preview mode (desktop & mobile viewport toggle) tanpa menyimpan respons. | Must |
| FB-51 | Publish memvalidasi: minimal 1 field input, semua field punya label, tidak ada circular logic. | Must |
| FB-52 | Setelah publish, tampilkan share panel: URL publik, tombol copy, dan QR code (generate server-side). | Must |
| FB-53 | Unpublish/close menampilkan halaman "Form ditutup" dengan pesan custom. | Must |

### 4.2 Module: Public Form Rendering (Respondent)

| ID | Requirement | Priority |
|---|---|---|
| PF-01 | Halaman publik `/f/{slug}` server-rendered, ringan (< 200 KB JS), mobile-first, tanpa framework berat. | Must |
| PF-02 | Render mengikuti urutan field, sections, page breaks, dan conditional logic. | Must |
| PF-03 | Validasi klien (Alpine.js) + validasi server (FormRequest yang dibangun dinamis dari definisi field). Server adalah source of truth. | Must |
| PF-04 | File upload dengan progress indicator; file disimpan di storage privat, hanya bisa diakses via signed URL dari backoffice. | Must |
| PF-05 | Submit bersifat idempotent: token submit sekali-pakai di form untuk mencegah double-submit. | Must |
| PF-06 | Autosave lokal (localStorage) untuk mencegah kehilangan jawaban saat refresh — hanya form publik; dibersihkan setelah submit sukses. | Should |
| PF-07 | Metadata respons dicatat: submitted_at, durasi pengisian, user agent, IP (hash untuk publik demi privasi), user_id (jika internal). | Must |
| PF-08 | Halaman sukses menampilkan pesan custom + skor (jika mode penilaian dan setting "tampilkan skor" aktif). | Must |
| PF-09 | Aksesibilitas: label ter-asosiasi, keyboard navigable, kontras WCAG AA. | Should |

### 4.3 Module: Backoffice — Response Monitoring

#### 4.3.1 Response List

| ID | Requirement | Priority |
|---|---|---|
| BO-01 | Tabel respons per form (Yajra DataTables server-side): kolom dinamis mengikuti field form (max 8 kolom pertama + kolom meta; kolom dapat dipilih user via column picker). | Must |
| BO-02 | Filter: rentang tanggal submit, status (complete/draft), respondent (internal), nilai field tertentu (mis. "rating < 3"), form version. | Must |
| BO-03 | Full-text search sederhana pada jawaban teks (PostgreSQL `ILIKE` / `tsvector` bila diperlukan). | Should |
| BO-04 | Detail respons: tampilan satu respons penuh (Q&A berurutan), metadata, file terlampir (signed URL), skor (jika ada). | Must |
| BO-05 | Aksi: delete respons (dengan konfirmasi + audit log), tandai flag/bintang, tambah catatan internal per respons. | Should |
| BO-06 | Badge "new" untuk respons yang belum dilihat oleh user saat ini. | Could |

#### 4.3.2 Dashboard & Analytics per Form

| ID | Requirement | Priority |
|---|---|---|
| BO-10 | Ringkasan: total respons, respons hari ini, completion rate (mulai vs submit — untuk multi-page), rata-rata durasi pengisian. | Must |
| BO-11 | Grafik respons per hari (line chart, 30 hari terakhir). | Must |
| BO-12 | Agregasi per pertanyaan: pie/bar untuk choice, histogram + mean untuk scale/rating/number, word list sederhana untuk teks pendek, tabel untuk matrix. | Must |
| BO-13 | Untuk mode penilaian: distribusi skor, rata-rata, min/max, dan leaderboard (jika identitas dikumpulkan). | Should |
| BO-14 | Semua agregasi dihitung dari `response_answers` (bukan parsing JSONB on-the-fly) dengan index yang sesuai. | Must |

#### 4.3.3 Global Dashboard (Home Backoffice)

| ID | Requirement | Priority |
|---|---|---|
| BO-20 | Ringkasan lintas form yang bisa diakses user: form aktif, total respons minggu ini, form yang segera ditutup, aktivitas terbaru. | Should |

### 4.4 Module: Scoring / Assessment Mode (Form Penilaian)

Mode opsional per form yang mengubah form menjadi instrumen penilaian.

| ID | Requirement | Priority |
|---|---|---|
| SC-01 | Toggle "Assessment mode" per form. | Must |
| SC-02 | Opsi pada radio/checkbox/dropdown dapat diberi bobot skor (integer/decimal). Scale & rating otomatis bernilai = angka pilihan (dapat dikali bobot field). | Must |
| SC-03 | Setiap field dapat diberi bobot (weight) untuk perhitungan skor akhir: `total = Σ (nilai_field × weight)`. | Must |
| SC-04 | Skor dihitung server-side saat submit dan disimpan di `responses.score` + rincian di `responses.score_breakdown` (JSONB). | Must |
| SC-05 | Grade mapping opsional: rentang skor → label (mis. 0–59 = "Perlu Perbaikan", 60–79 = "Baik", 80–100 = "Sangat Baik"). | Should |
| SC-06 | Setting: tampilkan/sembunyikan skor ke respondent setelah submit. | Must |
| SC-07 | Rekalkukasi skor massal jika bobot diubah — hanya untuk respons versi form yang sama, dengan konfirmasi eksplisit dan dicatat di audit log. | Could |

### 4.5 Module: Export

| ID | Requirement | Priority |
|---|---|---|
| EX-01 | Export respons per form ke **XLSX** dan **CSV**. Kolom = meta (submitted_at, respondent, durasi, skor) + satu kolom per field (urut sesuai form). | Must |
| EX-02 | Jawaban checkbox digabung dengan separator `; `. Matrix di-flatten menjadi kolom `{field} — {row}`. File upload di-export sebagai nama file + link signed URL (masa berlaku 7 hari). | Must |
| EX-03 | Export menghormati filter aktif di tabel respons. | Must |
| EX-04 | Export > 5.000 baris diproses via queue; user mendapat notifikasi in-app + link download saat selesai (file kedaluwarsa 24 jam). | Must |
| EX-05 | Export ringkasan analitik per form ke XLSX (satu sheet per pertanyaan agregat). | Could |
| EX-06 | Semua aktivitas export dicatat di audit log (siapa, form apa, filter apa, kapan). | Must |

### 4.6 Module: Sharing & Kolaborasi Internal

| ID | Requirement | Priority |
|---|---|---|
| SH-01 | Creator dapat share form ke user lain dengan level: `viewer` (lihat respons + export) atau `editor` (edit form + kelola respons). | Must |
| SH-02 | Share dapat diberikan ke role (mis. semua "HR Manager") selain per-user. | Should |
| SH-03 | Daftar "Shared with me" di sidebar backoffice. | Must |

### 4.7 Module: Templates

| ID | Requirement | Priority |
|---|---|---|
| TP-01 | Admin dapat menyimpan form sebagai template (struktur tanpa responses). | Should |
| TP-02 | Seed bawaan: Form Penilaian Karyawan, Survey Kepuasan Pelanggan, Audit Toko/Checklist, Registrasi Event, Feedback Produk. | Should |

### 4.8 Module: Administration

| ID | Requirement | Priority |
|---|---|---|
| AD-01 | User management: CRUD user, assign roles (Spatie), aktif/nonaktif. | Must |
| AD-02 | Audit log: create/update/delete form, publish/unpublish, delete respons, export, perubahan bobot skor, login. Immutable, filterable, retensi 1 tahun. | Must |
| AD-03 | Konfigurasi global: max file upload size, retensi file export, default CAPTCHA, email sender. | Should |
| AD-04 | Trash: form terhapus (soft delete) dapat direstore dalam 30 hari. | Should |

---

## 5. Data Model (PostgreSQL)

### 5.1 ERD Ringkas

```
users ──< forms ──< form_versions
              │ ──< form_fields ──< form_field_options
              │ ──< form_shares >── users / roles
              │ ──< responses ──< response_answers
              │             └──< response_files
              └──< form_watchers
audit_logs (polymorphic)
exports
```

### 5.2 Tabel Inti

```sql
-- =========================================================
-- forms
-- =========================================================
CREATE TABLE forms (
    id              BIGSERIAL PRIMARY KEY,
    uuid            UUID NOT NULL DEFAULT gen_random_uuid() UNIQUE,
    user_id         BIGINT NOT NULL REFERENCES users(id),      -- creator
    title           VARCHAR(255) NOT NULL,
    description     TEXT,
    slug            VARCHAR(100) NOT NULL UNIQUE,
    status          VARCHAR(20) NOT NULL DEFAULT 'draft',
                    -- draft | published | closed | archived
    access_type     VARCHAR(20) NOT NULL DEFAULT 'public',
                    -- public | internal | token
    is_assessment   BOOLEAN NOT NULL DEFAULT FALSE,
    settings        JSONB NOT NULL DEFAULT '{}',
                    -- { one_response_per_user, show_progress, success_message,
                    --   redirect_url, captcha_enabled, show_score, grade_map,
                    --   response_limit, notify_on_response, allow_draft }
    current_version INT NOT NULL DEFAULT 1,
    opens_at        TIMESTAMPTZ,
    closes_at       TIMESTAMPTZ,
    published_at    TIMESTAMPTZ,
    deleted_at      TIMESTAMPTZ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX idx_forms_status ON forms(status) WHERE deleted_at IS NULL;
CREATE INDEX idx_forms_user   ON forms(user_id);

-- =========================================================
-- form_versions : snapshot struktur saat ada respons & struktur berubah
-- =========================================================
CREATE TABLE form_versions (
    id          BIGSERIAL PRIMARY KEY,
    form_id     BIGINT NOT NULL REFERENCES forms(id) ON DELETE CASCADE,
    version     INT NOT NULL,
    schema      JSONB NOT NULL,   -- snapshot penuh fields + options + logic
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (form_id, version)
);

-- =========================================================
-- form_fields : definisi pertanyaan (versi aktif / editable)
-- =========================================================
CREATE TABLE form_fields (
    id           BIGSERIAL PRIMARY KEY,
    form_id      BIGINT NOT NULL REFERENCES forms(id) ON DELETE CASCADE,
    field_key    VARCHAR(100) NOT NULL,   -- unik per form, key jawaban JSONB
    type         VARCHAR(30) NOT NULL,    -- short_text | radio | scale | ...
    label        VARCHAR(500) NOT NULL,
    description  TEXT,
    is_required  BOOLEAN NOT NULL DEFAULT FALSE,
    sort_order   INT NOT NULL DEFAULT 0,
    config       JSONB NOT NULL DEFAULT '{}',
                 -- per-type: { min, max, step, placeholder, rows, scale_min,
                 --   scale_max, label_left, label_right, allow_other,
                 --   max_files, allowed_types, matrix_rows, matrix_cols,
                 --   page_break, weight }
    logic        JSONB,   -- { operator: 'and'|'or', rules: [{field_key, op, value}] }
    deleted_at   TIMESTAMPTZ,
    created_at   TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at   TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (form_id, field_key)
);
CREATE INDEX idx_fields_form ON form_fields(form_id, sort_order)
    WHERE deleted_at IS NULL;

-- =========================================================
-- form_field_options : opsi untuk radio/checkbox/dropdown/matrix
-- =========================================================
CREATE TABLE form_field_options (
    id          BIGSERIAL PRIMARY KEY,
    field_id    BIGINT NOT NULL REFERENCES form_fields(id) ON DELETE CASCADE,
    value       VARCHAR(255) NOT NULL,   -- value tersimpan
    label       VARCHAR(500) NOT NULL,   -- teks tampil
    score       NUMERIC(10,2),           -- bobot skor (assessment mode)
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX idx_options_field ON form_field_options(field_id, sort_order);

-- =========================================================
-- responses : satu submission
-- =========================================================
CREATE TABLE responses (
    id               BIGSERIAL PRIMARY KEY,
    uuid             UUID NOT NULL DEFAULT gen_random_uuid() UNIQUE,
    form_id          BIGINT NOT NULL REFERENCES forms(id) ON DELETE CASCADE,
    form_version     INT NOT NULL DEFAULT 1,
    user_id          BIGINT REFERENCES users(id),   -- NULL untuk publik
    status           VARCHAR(20) NOT NULL DEFAULT 'complete',
                     -- draft | complete
    answers_snapshot JSONB NOT NULL DEFAULT '{}',   -- {field_key: value}
    score            NUMERIC(10,2),
    score_breakdown  JSONB,                          -- {field_key: {value, score, weight}}
    grade            VARCHAR(50),
    duration_seconds INT,
    ip_hash          VARCHAR(64),
    user_agent       VARCHAR(500),
    resume_token     VARCHAR(64) UNIQUE,             -- draft/edit link
    is_flagged       BOOLEAN NOT NULL DEFAULT FALSE,
    internal_note    TEXT,
    submitted_at     TIMESTAMPTZ,
    created_at       TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at       TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX idx_responses_form      ON responses(form_id, submitted_at DESC);
CREATE INDEX idx_responses_form_user ON responses(form_id, user_id);
CREATE INDEX idx_responses_answers   ON responses USING GIN (answers_snapshot);

-- =========================================================
-- response_answers : satu baris per field per respons (analitik)
-- =========================================================
CREATE TABLE response_answers (
    id            BIGSERIAL PRIMARY KEY,
    response_id   BIGINT NOT NULL REFERENCES responses(id) ON DELETE CASCADE,
    form_id       BIGINT NOT NULL,                 -- denormalized untuk agregasi cepat
    field_id      BIGINT NOT NULL REFERENCES form_fields(id),
    field_key     VARCHAR(100) NOT NULL,
    value_text    TEXT,            -- teks / pilihan tunggal / value gabungan
    value_number  NUMERIC(14,4),   -- number / scale / rating
    value_json    JSONB,           -- checkbox array / matrix object
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX idx_answers_agg   ON response_answers(form_id, field_id);
CREATE INDEX idx_answers_num   ON response_answers(field_id, value_number);
CREATE INDEX idx_answers_resp  ON response_answers(response_id);

-- =========================================================
-- response_files : lampiran file upload
-- =========================================================
CREATE TABLE response_files (
    id            BIGSERIAL PRIMARY KEY,
    response_id   BIGINT NOT NULL REFERENCES responses(id) ON DELETE CASCADE,
    field_id      BIGINT NOT NULL REFERENCES form_fields(id),
    original_name VARCHAR(255) NOT NULL,
    path          VARCHAR(500) NOT NULL,   -- storage privat
    mime_type     VARCHAR(100),
    size_bytes    BIGINT,
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- =========================================================
-- form_shares : kolaborasi internal
-- =========================================================
CREATE TABLE form_shares (
    id          BIGSERIAL PRIMARY KEY,
    form_id     BIGINT NOT NULL REFERENCES forms(id) ON DELETE CASCADE,
    user_id     BIGINT REFERENCES users(id),
    role_name   VARCHAR(100),               -- share ke role (Spatie), nullable
    level       VARCHAR(20) NOT NULL,       -- viewer | editor
    created_by  BIGINT NOT NULL REFERENCES users(id),
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    CHECK (user_id IS NOT NULL OR role_name IS NOT NULL)
);

-- =========================================================
-- exports : job export ter-queue
-- =========================================================
CREATE TABLE exports (
    id           BIGSERIAL PRIMARY KEY,
    form_id      BIGINT NOT NULL REFERENCES forms(id),
    user_id      BIGINT NOT NULL REFERENCES users(id),
    format       VARCHAR(10) NOT NULL,      -- xlsx | csv
    filters      JSONB,
    status       VARCHAR(20) NOT NULL DEFAULT 'pending',
                 -- pending | processing | done | failed
    file_path    VARCHAR(500),
    row_count    INT,
    expires_at   TIMESTAMPTZ,
    created_at   TIMESTAMPTZ NOT NULL DEFAULT now(),
    completed_at TIMESTAMPTZ
);

-- =========================================================
-- audit_logs
-- =========================================================
CREATE TABLE audit_logs (
    id            BIGSERIAL PRIMARY KEY,
    user_id       BIGINT REFERENCES users(id),
    action        VARCHAR(50) NOT NULL,     -- form.created, response.deleted, export.run, ...
    subject_type  VARCHAR(100) NOT NULL,
    subject_id    BIGINT NOT NULL,
    meta          JSONB,
    ip            VARCHAR(45),
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX idx_audit_subject ON audit_logs(subject_type, subject_id);
CREATE INDEX idx_audit_created ON audit_logs(created_at DESC);
```

### 5.3 Kontrak Nilai Jawaban per Tipe (answers_snapshot)

| Tipe | Format di JSONB | Contoh |
|---|---|---|
| short_text, long_text, email, phone | string | `"Budi Santoso"` |
| number, scale, rating | number | `4` |
| radio, dropdown | string (option value) | `"sangat_baik"` |
| radio + Lainnya | `{"value": "__other__", "other": "teks"}` | |
| checkbox | array of string | `["aroma", "kemasan"]` |
| matrix | object `{row_key: value}` | `{"kebersihan": "baik"}` |
| date / time | ISO string | `"2026-07-08"` / `"14:30"` |
| file | array of response_files.id | `[12, 13]` |
| signature | response_files.id | `15` |

---

## 6. Routes & Halaman

### 6.1 Public

| Route | Halaman |
|---|---|
| `GET /f/{slug}` | Render form (cek status, jadwal, akses) |
| `POST /f/{slug}` | Submit respons |
| `GET /f/{slug}/resume/{token}` | Lanjutkan draft / edit respons |
| `GET /f/{slug}/closed` | Halaman form ditutup |

### 6.2 Backoffice (prefix `/app`, middleware auth + permission)

| Route | Halaman |
|---|---|
| `GET /app/dashboard` | Global dashboard |
| `GET /app/forms` | Daftar form (milik sendiri + shared) |
| `GET /app/forms/create` | Buat form |
| `GET /app/forms/{form}/build` | Form builder (fields, logic, reorder) |
| `GET /app/forms/{form}/settings` | Settings, akses, jadwal, scoring |
| `GET /app/forms/{form}/preview` | Preview |
| `GET /app/forms/{form}/share` | Share panel (link, QR, kolaborator) |
| `GET /app/forms/{form}/responses` | Tabel respons (DataTables) |
| `GET /app/forms/{form}/responses/{response}` | Detail respons |
| `GET /app/forms/{form}/analytics` | Dashboard analitik per form |
| `POST /app/forms/{form}/export` | Trigger export |
| `GET /app/exports` | Riwayat export & download |
| `GET /app/templates` | Galeri template |
| `GET /app/admin/users` | User management (Super Admin) |
| `GET /app/admin/audit` | Audit log (Super Admin) |

### 6.3 Internal JSON Endpoints (untuk Alpine/DataTables, bukan public API)

| Endpoint | Fungsi |
|---|---|
| `PUT /app/forms/{form}/fields/reorder` | Simpan urutan drag-and-drop |
| `POST /app/forms/{form}/fields` · `PUT/DELETE .../fields/{field}` | CRUD field |
| `GET /app/forms/{form}/responses/data` | DataTables server-side source |
| `GET /app/forms/{form}/analytics/data` | Data agregasi untuk Chart.js |

---

## 7. Non-Functional Requirements

| Kategori | Requirement |
|---|---|
| **Performa** | Form publik TTFB < 300 ms; submit < 500 ms (p95). Agregasi analitik untuk 50k respons < 2 s (index-backed). |
| **Skala** | Target v1: 500 form aktif, 100k respons total, 200 respons/menit burst (event/QR di toko). |
| **Keamanan** | CSRF semua POST; rate limit submit publik (10/menit/IP); validasi server-side dibangun dari definisi field (jangan percaya klien); file upload divalidasi MIME real + disimpan di storage privat; signed URL untuk akses file; XSS-safe rendering jawaban (escape semua output); IP publik disimpan sebagai hash. |
| **Privasi** | Respons berisi data pribadi — akses hanya via permission; export tercatat di audit log; retensi file export 24 jam. |
| **Integritas data** | Submit dibungkus DB transaction (responses + response_answers + files). Versioning form menjamin respons lama tetap konsisten. |
| **Reliabilitas** | Queue worker di-supervise (Supervisor/Docker healthcheck); job export retry 3×. |
| **Auditabilitas** | Semua aksi destruktif & export tercatat immutable. |
| **UX/Brand** | Form publik mengikuti design system Heaven Scent: Playfair Display untuk judul, Inter untuk body, palet warm charcoal / gold / ivory cream; backoffice bertema clean monochrome. |
| **Browser support** | 2 versi terakhir Chrome/Safari/Firefox/Edge; Android WebView (form dibuka dari QR di toko). |
| **Timezone** | Simpan UTC (`timestamptz`), tampilkan Asia/Jakarta. |

---

## 8. Edge Cases & Aturan Bisnis Penting

1. **Form diedit saat sudah ada respons** → sistem membuat `form_versions` snapshot baru; respons lama tetap merujuk versi lamanya; tabel respons & export menampilkan kolom sesuai versi (union kolom lintas versi, sel kosong bila field tidak ada di versi tersebut).
2. **Field required tapi tersembunyi oleh logic** → tidak divalidasi required; jawaban yang terkirim untuk field tersembunyi dibuang server-side.
3. **Double submit** → token submit sekali-pakai; request kedua mengembalikan halaman sukses yang sama (idempotent).
4. **Form ditutup di tengah pengisian** → submit setelah `closes_at` ditolak dengan pesan ramah; grace period 5 menit untuk yang sudah membuka form sebelum tutup.
5. **Kuota tercapai bersamaan (race)** → cek kuota di dalam transaction dengan `SELECT ... FOR UPDATE` pada counter; yang kalah mendapat halaman "kuota penuh".
6. **Respondent internal submit dua kali saat one-response aktif** → respons kedua ditolak; tampilkan link lihat/edit respons pertama (jika edit diizinkan).
7. **Opsi pilihan dihapus setelah ada respons** → soft delete opsi; jawaban lama tetap ter-render dengan label historis dari version snapshot.
8. **File upload orphan** (upload sukses, submit gagal) → file sementara di direktori temp, cleanup scheduler harian untuk file temp > 24 jam.
9. **Perubahan bobot skor setelah ada respons** → skor lama TIDAK berubah otomatis; rekalkulasi hanya via aksi eksplisit (SC-07) dan tercatat di audit.

---

## 9. Development Milestones

### Phase 1 — Foundation (Week 1–2)
- Setup project: Laravel 11, PostgreSQL, Breeze, Spatie Permission, Tailwind, Alpine, Docker.
- Migrations semua tabel + seeder roles & admin.
- CRUD form (tanpa builder), layout backoffice, auth & permission middleware.

**Exit criteria:** login, buat form kosong, daftar form dengan permission bekerja.

### Phase 2 — Form Builder Core (Week 3–4)
- Builder UI: tambah/edit/hapus/duplikat field semua tipe teks, choice, scale, rating.
- Drag-and-drop reorder (SortableJS), field options CRUD, autosave builder.
- Preview mode, publish flow + slug + QR + share panel.

**Exit criteria:** form 10 pertanyaan bisa dibuat dan dipublish dalam ≤ 10 menit.

### Phase 3 — Public Form & Submission (Week 5–6)
- Render publik semua tipe field, validasi klien + server dinamis.
- Submit pipeline (transaction, snapshot + response_answers, meta, idempotency).
- One-response rules, jadwal buka/tutup, kuota, halaman sukses/closed.
- File upload + signature + storage privat + signed URL.

**Exit criteria:** end-to-end submit stabil, data konsisten di kedua lapisan penyimpanan.

### Phase 4 — Backoffice Monitoring & Export (Week 7–8)
- Tabel respons DataTables kolom dinamis + filter + detail respons.
- Export XLSX/CSV sinkron & queued, riwayat export, audit log.
- Notifikasi respons baru (in-app + email).

**Exit criteria:** reviewer bisa monitor & export mandiri tanpa IT.

### Phase 5 — Logic, Multi-page, Scoring, Analytics (Week 9–10)
- Conditional logic (builder + runtime + server revalidation).
- Multi-page/section + progress bar.
- Assessment mode: bobot opsi & field, skor, grade, tampil skor ke respondent.
- Dashboard analitik per form (Chart.js) + agregasi per pertanyaan.

**Exit criteria:** form penilaian karyawan ber-skor berjalan penuh.

### Phase 6 — Polish & Hardening (Week 11–12)
- Templates + seed bawaan, sharing/kolaborasi, form versioning penuh.
- CAPTCHA, rate limiting, security review, WCAG pass, load test 200 submit/menit.
- Deployment Docker + GitHub Actions ke VPS produksi.

**Exit criteria:** UAT lolos, go-live.

---

## 10. Acceptance Criteria (Ringkasan UAT)

1. Creator membuat "Form Penilaian Store Audit" 15 pertanyaan (mix tipe, 2 halaman, 3 aturan logic, scoring) dalam ≤ 15 menit tanpa bantuan IT.
2. Respondent mengisi via QR di HP, termasuk upload 2 foto, submit < 3 detik, data lengkap di backoffice ≤ 5 detik kemudian.
3. Reviewer memfilter respons "skor < 60, bulan ini", export XLSX, membuka file dengan kolom lengkap dan link foto berfungsi.
4. Form diedit (1 field dihapus, 1 ditambah) setelah 50 respons → respons lama tetap ter-render benar, export lintas versi konsisten.
5. Semua aksi delete & export terlihat di audit log Super Admin.
6. User tanpa akses tidak dapat membuka respons form yang tidak di-share kepadanya (403).

---

## 11. Open Questions

| # | Pertanyaan | Dampak |
|---|---|---|
| 1 | Apakah perlu SSO/integrasi dengan sistem user internal yang sudah ada, atau user management berdiri sendiri? | Phase 1 auth design |
| 2 | Apakah respondent internal diambil dari data karyawan (HR system) untuk auto-fill identitas? | Integrasi tambahan |
| 3 | Perlukah webhook keluar (respons baru → n8n/Slack/sistem lain) di v1? | Bisa jadi v1.1 |
| 4 | Bahasa UI form publik: Indonesia saja, atau bilingual ID/EN per form? | Field i18n di settings |
| 5 | Estimasi volume file upload — perlukah langsung S3-compatible storage dari awal? | Infra decision |

---

*Dokumen ini adalah living document. Perubahan requirement dicatat pada tabel revisi di bawah.*

| Versi | Tanggal | Perubahan | Oleh |
|---|---|---|---|
| 1.0 | 2026-07-08 | Draft awal | Team IT |
