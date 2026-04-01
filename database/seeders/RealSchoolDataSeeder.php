<?php

namespace Database\Seeders;

use App\Services\SchoolData\RealSchoolDataImporter;
use Illuminate\Database\Seeder;

class RealSchoolDataSeeder extends Seeder
{
    public function run(): void
    {
        $teacherPath = env('REAL_TEACHER_XLSX_PATH', 'C:/Users/jovia/Downloads/daftar-guru-SMP TERAMIA-2026 yang dikirim.xlsx');
        $studentPath = env('REAL_STUDENT_XLSX_PATH', 'C:/Users/jovia/Downloads/daftar_pd-SMP TERAMIA-2026 yang di kirim.xlsx');

        /** @var RealSchoolDataImporter $importer */
        $importer = app(RealSchoolDataImporter::class);
        $result = $importer->import($teacherPath, $studentPath);

        if ($this->command) {
            $this->command->info('Real school data import completed.');
            foreach ($result as $key => $value) {
                $this->command->line(sprintf('- %s: %s', $key, (string) $value));
            }
        }
    }
}

