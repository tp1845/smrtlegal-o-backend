<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DocumentType::truncate();
        $data = [
            'NDA',
            'DPA',
            'Job offer latter',
            'MSA'
        ];
        collect($data)->each(function($type) {
            DocumentType::create([
                'name' => $type
            ]);
        });
    }
}
