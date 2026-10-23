<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrauParticipacaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $graus = [
            ['codigo' => '00', 'descricao' => 'Cirurgião', 'ativo' => true],
            ['codigo' => '01', 'descricao' => 'Primeiro Auxiliar', 'ativo' => true],
            ['codigo' => '02', 'descricao' => 'Segundo Auxiliar', 'ativo' => true],
            ['codigo' => '03', 'descricao' => 'Terceiro Auxiliar', 'ativo' => true],
            ['codigo' => '04', 'descricao' => 'Quarto Auxiliar', 'ativo' => true],
            ['codigo' => '05', 'descricao' => 'Instrumentador', 'ativo' => true],
            ['codigo' => '06', 'descricao' => 'Anestesista', 'ativo' => true],
            ['codigo' => '07', 'descricao' => 'Auxiliar de Anestesista', 'ativo' => true],
            ['codigo' => '08', 'descricao' => 'Consultor', 'ativo' => true],
            ['codigo' => '09', 'descricao' => 'Perfusionista', 'ativo' => true],
            ['codigo' => '10', 'descricao' => 'Pediatra na sala de parto', 'ativo' => true],
            ['codigo' => '11', 'descricao' => 'Auxiliar SADT', 'ativo' => true],
            ['codigo' => '12', 'descricao' => 'Clínico', 'ativo' => true],
            ['codigo' => '13', 'descricao' => 'Intensivista', 'ativo' => true],
        ];

        foreach ($graus as $grau) {
            DB::table('grau_participacao')->insert($grau);
        }

    }
}
