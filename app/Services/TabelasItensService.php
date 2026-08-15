<?php

namespace App\Services;

use App\Models\TabelasItens;
use App\Repositories\Contracts\TabelasItensRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TabelasItensService
{
    public function __construct(protected TabelasItensRepositoryInterface $tabelasItensRepository)
    {
    }

    public function createTabelaItem(array $data): TabelasItens
    {
        $this->validate($data);

        return $this->tabelasItensRepository->create($data);
    }

    public function getTabelaItemById(int $id): ?TabelasItens
    {
        return $this->tabelasItensRepository->findById($id);
    }

    public function getAllTabelasItens(int $tabelaId): Collection
    {
        return $this->tabelasItensRepository->findByTabelaId($tabelaId);
    }

    public function updateTabelaItem(int $id, array $data): TabelasItens
    {
        $this->validate($data, true);

        if (!$this->tabelasItensRepository->findById($id)) {
            throw ValidationException::withMessages(['id' => ['Item da tabela não encontrado.']]);
        }

        $this->tabelasItensRepository->update($id, $data);

        return $this->tabelasItensRepository->findById($id);
    }

    public function deleteTabelaItem(int $id): void
    {
        if (!$this->tabelasItensRepository->findById($id)) {
            throw ValidationException::withMessages(['id' => ['Item da tabela não encontrado.']]);
        }

        $this->tabelasItensRepository->delete($id);
    }

    public function restoreTabelaItem(int $id): TabelasItens
    {
        if (!$this->tabelasItensRepository->findTrashedById($id)) {
            throw ValidationException::withMessages(['id' => ['Item da tabela excluído não encontrado.']]);
        }

        $this->tabelasItensRepository->restore($id);

        return $this->tabelasItensRepository->findById($id);
    }

    private function validate(array $data, bool $updating = false): void
    {
        $required = $updating ? 'sometimes|required' : 'required';
        $validator = Validator::make($data, [
            'tabelas_id' => "$required|integer|exists:tabelas,id",
            'codigo' => "$required|string|max:10",
            'descricao' => "$required|string|max:65",
            'operacional' => 'nullable|numeric|decimal:0,4',
            'honorario' => 'nullable|numeric|decimal:0,4',
            'valorsadt' => 'nullable|numeric|decimal:0,4',
            'anestesia' => 'nullable|numeric|decimal:0,4',
            'pontosato' => 'nullable|numeric|decimal:0',
            'porteanestesico' => 'nullable|numeric|decimal:0',
            'auxiliares' => 'nullable|numeric|decimal:0',
            'incidencia' => 'nullable|numeric|decimal:0',
            'filme' => 'nullable|numeric|decimal:0,4',
            'qtdmax' => 'nullable|numeric|decimal:0',
            'grupo' => 'nullable|string|max:8',
            'portesala' => 'nullable|numeric|decimal:0',
            'autorizacao' => 'nullable|string|size:1',
            'titgrupo' => 'nullable|string|size:1',
            'profissional2' => 'nullable|numeric|decimal:0,4',
            'pofissional3' => 'nullable|numeric|decimal:0,4',
            'auxiliar' => 'nullable|string|max:8',
            'porteprof' => 'nullable|string|size:3',
            'multporte' => 'nullable|numeric|decimal:0,2',
            'grupos_id' => "$required|integer|exists:grupos,id",
            'unidades_id' => "$required|integer|exists:unidades,id",
            'pacote' => 'nullable|string|size:3',
            'pacotimp' => 'nullable|integer|boolean',
            'codigoans' => 'nullable|string|size:2',
            'porte' => 'nullable|integer',
            'equipeimp' => 'nullable|integer|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}