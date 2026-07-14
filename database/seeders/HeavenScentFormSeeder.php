<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormFieldOption;
use Illuminate\Support\Str;

class HeavenScentFormSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Clean up previous seeded data
        Form::where('slug', 'heaven-scent-feedback')->forceDelete();

        // 1. Create the Form
        $form = Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => 1, // Assuming admin exists
            'title' => 'Heaven Scent Customer Feedback',
            'description' => "Terima kasih telah berbelanja di Heaven Scent. 🤍\nPendapat Anda sangat berarti bagi kami. Luangkan waktu sekitar 1 menit untuk memberikan penilaian dan masukan agar pengalaman berbelanja menjadi semakin baik.",
            'slug' => 'heaven-scent-feedback',
            'status' => 'published',
            'access_type' => 'public',
        ]);

        // 2. Form Fields
        // Field 1: Store Kunjungan
        $storeField = FormField::create([
            'form_id' => $form->id,
            'field_key' => 'store',
            'type' => 'dropdown',
            'label' => 'Store Kunjungan',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        
        $stores = [
            'HS ROYAL', 'HS KEDIRI TOWN SQUARE', 'HS MALANG TOWN SQUARE', 'HS GRESSMALL',
            'HS JOGJA CITY MALL', 'HS PLAZA MALIOBORO', 'HS PAKUWON SOLO', 'HS SOLO SQUARE',
            'HS SLEMAN CITY HALL', 'HS LIPPO JEMBER', 'HS PASIFIC TEGAL', 'TPR',
            'GUDANG JOGJA & SCH', 'GUDANG PASIFIC', 'GUDANG JEMBER', 'HS SUNCITY',
            'HS JATOS', 'HS CIWALK', 'THE KING', 'GRAGE MALL', 'CSB', 'TSM',
            'RITA PURWOKERTO', 'GUDANG RITA', 'CITYLINK', 'BTC', 'TP', 'PLAZA ASIA',
            'ARTOS', 'SCM', 'PARAGON', 'THE PARK SMRG', 'SUNRISE', 'BIP', 'KEDIRI MALL',
            'MCP', 'KCP', 'GPS', 'GGP', 'BASSURA UNIT', 'BASSURA ISLAND', 'LIPPO BOGOR',
            'CIPUTRA CIBUBUR', 'GANDARIA CITY', 'LW GRAND WISATA', 'LW KOTA WISATA',
            'HS CIPUTRA SEMARANG', 'FX SUDIRMAN', 'MARGO CITY', 'PTC', 'SUNCITY SDA',
            'LIPPO BATU', 'RESINDA', 'MALANG OLYMPIC GARDEN', 'THE PARK SOLO',
            'TSM CIBUBUR', 'HS TANGCITY'
        ];
        foreach ($stores as $i => $store) {
            FormFieldOption::create([
                'field_id' => $storeField->id,
                'value' => Str::slug($store),
                'label' => $store,
                'sort_order' => $i + 1,
            ]);
        }

        // Field 2: Rating
        $ratingField = FormField::create([
            'form_id' => $form->id,
            'field_key' => 'rating',
            'type' => 'rating',
            'label' => 'Rating pengalaman berbelanja anda?',
            'is_required' => true,
            'sort_order' => 2,
            'config' => ['stars' => 5],
        ]);

        // Field 3: Apology & WA Button (Custom HTML/Statement)
        $apologyField = FormField::create([
            'form_id' => $form->id,
            'field_key' => 'apology_wa',
            'type' => 'statement',
            'label' => 'Mohon maaf atas pengalaman kurang menyenangkan ini',
            'sort_order' => 3,
            'logic' => [
                'action' => 'show',
                'condition' => [
                    'field' => 'rating',
                    'operator' => '<=',
                    'value' => 2
                ]
            ],
            'config' => [
                'button_text' => 'Hubungi customer care via Whatsapp',
                'button_url' => 'https://wa.me/1234567890'
            ]
        ]);

        // Field 4: Feedback Type
        $feedbackTypeField = FormField::create([
            'form_id' => $form->id,
            'field_key' => 'feedback_type',
            'type' => 'radio',
            'label' => 'Apa yang paling ingin anda berikan feedback?',
            'is_required' => false,
            'sort_order' => 4,
            'config' => ['allow_other' => true],
        ]);

        $feedbackTypes = ['Produk', 'Kondisi Toko', 'Pelayanan', 'Lainnya'];
        foreach ($feedbackTypes as $i => $type) {
            FormFieldOption::create([
                'field_id' => $feedbackTypeField->id,
                'value' => Str::slug($type),
                'label' => $type,
                'sort_order' => $i + 1,
            ]);
        }

        // Field 5: Feedback Text
        $feedbackTextField = FormField::create([
            'form_id' => $form->id,
            'field_key' => 'feedback_text',
            'type' => 'long_text',
            'label' => 'Setiap cerita Anda penting. Bagikan pengalaman dan masukan Anda di sini.',
            'is_required' => false,
            'sort_order' => 5,
        ]);

        // 3. Create Form Version Snapshot
        $schema = [
            'title' => $form->title,
            'description' => $form->description,
            'fields' => $form->fields()->with('options')->get()->toArray(),
        ];

        $form->versions()->create([
            'version' => 1,
            'schema' => $schema,
        ]);
        
        $this->command->info("Heaven Scent Form created! Slug: heaven-scent-feedback");
    }
}
