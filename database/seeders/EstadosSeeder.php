<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $estados = [
            ['codigo_ibge' => 11, 'nome' => 'Rondônia', 'sigla' => 'RO'],
            ['codigo_ibge' => 12, 'nome' => 'Acre', 'sigla' => 'AC',],
            ['codigo_ibge' => 13, 'nome' => 'Amazonas', 'sigla' => 'AM',],
            ['codigo_ibge' => 14, 'nome' => 'Roraima', 'sigla' => 'RR',],
            ['codigo_ibge' => 15, 'nome' => 'Pará', 'sigla' => 'PA',],
            ['codigo_ibge' => 16, 'nome' => 'Amapá', 'sigla' => 'AM',],
            ['codigo_ibge' => 17, 'nome' => 'Tocantins', 'sigla' => 'TO',],
            ['codigo_ibge' => 21, 'nome' => 'Maranhão', 'sigla' => 'MA',],
            ['codigo_ibge' => 22, 'nome' => 'Piauí', 'sigla' => 'PI',],
            ['codigo_ibge' => 23, 'nome' => 'Ceará', 'sigla' => 'CE',],
            ['codigo_ibge' => 24, 'nome' => 'Rio Grande do Norte', 'sigla' => 'RN',],
            ['codigo_ibge' => 25, 'nome' => 'Paraíba', 'sigla' => 'PB',],
            ['codigo_ibge' => 26, 'nome' => 'Pernambuco', 'sigla' => 'PE',],
            ['codigo_ibge' => 27, 'nome' => 'Alagoas', 'sigla' => 'AL',],
            ['codigo_ibge' => 28, 'nome' => 'Sergipe', 'sigla' => 'SE',],
            ['codigo_ibge' => 29, 'nome' => 'Bahia', 'sigla' => 'BA',],
            ['codigo_ibge' => 31, 'nome' => 'Minas Gerais', 'sigla' => 'MG',],
            ['codigo_ibge' => 32, 'nome' => 'Espírito Santo', 'sigla' => 'ES',],
            ['codigo_ibge' => 33, 'nome' => 'Rio de Janeiro', 'sigla' => 'RJ',],
            ['codigo_ibge' => 35, 'nome' => 'São Paulo', 'sigla' => 'SP',],
            ['codigo_ibge' => 41, 'nome' => 'Paraná', 'sigla' => 'PR',],
            ['codigo_ibge' => 42, 'nome' => 'Santa Catarina', 'sigla' => 'SC',],
            ['codigo_ibge' => 43, 'nome' => 'Rio Grande do Sul', 'sigla' => 'RS',],
            ['codigo_ibge' => 50, 'nome' => 'Mato Grosso do Sul', 'sigla' => 'MS',],
            ['codigo_ibge' => 51, 'nome' => 'Mato Grosso', 'sigla' => 'MG',],
            ['codigo_ibge' => 52, 'nome' => 'Goiás', 'sigla' => 'GO',],
            ['codigo_ibge' => 53, 'nome' => 'Distrito Federal', 'sigla' => 'DF',]
        ];

        foreach ($estados as $estado) {
            DB::table('estados')->insert($estado);
        }

    }
}
