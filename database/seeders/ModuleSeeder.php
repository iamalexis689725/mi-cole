<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        $modules = [
            [
                'codigo' => 'agenda',
                'nombre' => 'Agenda Académica',
            ],
            [
                'codigo' => 'asistencia',
                'nombre' => 'Asistencia',
            ],
            [
                'codigo' => 'circulares',
                'nombre' => 'Circulares',
            ],
            [
                'codigo' => 'anecdotario',
                'nombre' => 'Anecdotario',
            ],
            [
                'codigo' => 'biblioteca',
                'nombre' => 'Biblioteca',
            ],
            [
                'codigo' => 'estudiantes',
                'nombre' => 'Estudiantes',
            ],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(
                ['codigo' => $module['codigo']],
                $module
            );
        }
    }
}
