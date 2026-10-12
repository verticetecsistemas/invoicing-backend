<?php

namespace App\Services;

use App\Models\CredenciadosConvenios;
use App\Models\CredenciadosConveniosTabelas;
use App\Repositories\Contracts\CredenciadosConveniosRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CredenciadosConveniosService
{
    public function __construct(
        protected CredenciadosConveniosRepositoryInterface $credenciadosConveniosRepository,
        protected CredenciadosConveniosTabelas $credenciadosConveniosTabelasModel
    ) {
    }

    public function createCredenciadoConvenio(array $data): CredenciadosConvenios
    {
        $this->validate($data);

        return DB::transaction(function () use ($data) {
            $credenciadoConvenio = $this->credenciadosConveniosRepository->create($data);

            $this->credenciadosConveniosTabelasModel->create([
                'credenciado_convenio_id' => $credenciadoConvenio->id,
                'tabela_padrao_id' => $data['tabela_padrao_id'],
                'tabela_medicamentos_id' => $data['tabela_medicamentos_id'],
                'tabela_materiais_id' => $data['tabela_materiais_id'],
            ]);

            return $this->credenciadosConveniosRepository->findById($credenciadoConvenio->id);
        });
    }

    public function getCredenciadoConvenioById(int $id): ?CredenciadosConvenios
    {
        return $this->credenciadosConveniosRepository->findById($id);
    }

    public function getAllCredenciadosConvenios(int $credenciadoId): Collection
    {
        return $this->credenciadosConveniosRepository->findByCredenciadoId($credenciadoId);
    }

    public function getCredenciadoConvenioByCredenciadoAndConvenio(int $credenciadoId, int $convenioId): ?CredenciadosConvenios
    {
        return $this->credenciadosConveniosRepository->findByCredenciadoAndConvenio($credenciadoId, $convenioId);
    }

    public function updateCredenciadoConvenio(int $id, array $data): CredenciadosConvenios
    {
        $this->validate($data, true);

        $credenciadoConvenio = $this->credenciadosConveniosRepository->findById($id);
        if (!$credenciadoConvenio) {
            throw ValidationException::withMessages(['id' => ['Convênio do credenciado não encontrado.']]);
        }

        return DB::transaction(function () use ($id, $data, $credenciadoConvenio) {
            $this->credenciadosConveniosRepository->update($id, $data);

            $tabelasData = array_filter([
                'tabela_padrao_id' => $data['tabela_padrao_id'] ?? null,
                'tabela_medicamentos_id' => $data['tabela_medicamentos_id'] ?? null,
                'tabela_materiais_id' => $data['tabela_materiais_id'] ?? null,
            ], fn ($value) => $value !== null);

            if (!empty($tabelasData)) {
                $this->credenciadosConveniosTabelasModel->updateOrCreate(
                    ['credenciado_convenio_id' => $id],
                    $tabelasData
                );
            }

            return $this->credenciadosConveniosRepository->findById($id);
        });
    }

    public function deleteCredenciadoConvenio(int $id): void
    {
        if (!$this->credenciadosConveniosRepository->findById($id)) {
            throw ValidationException::withMessages(['id' => ['Convênio do credenciado não encontrado.']]);
        }

        DB::transaction(function () use ($id) {
            $this->credenciadosConveniosTabelasModel->where('credenciado_convenio_id', $id)->delete();
            $this->credenciadosConveniosRepository->delete($id);
        });
    }

    public function restoreCredenciadoConvenio(int $id): CredenciadosConvenios
    {
        if (!$this->credenciadosConveniosRepository->findTrashedById($id)) {
            throw ValidationException::withMessages(['id' => ['Convênio do credenciado excluído não encontrado.']]);
        }

        return DB::transaction(function () use ($id) {
            $this->credenciadosConveniosRepository->restore($id);
            $this->credenciadosConveniosTabelasModel->onlyTrashed()
                ->where('credenciado_convenio_id', $id)
                ->restore();

            return $this->credenciadosConveniosRepository->findById($id);
        });
    }

    private function validate(array $data, bool $updating = false): void
    {
        $required = $updating ? 'sometimes|required' : 'required';

        $validator = Validator::make($data, [
            'credenciado_id' => "$required|integer|exists:credenciados,id",
            'convenio_id' => "$required|integer|exists:convenios,id",
            'codigo_convenio' => 'nullable|string|max:255',
            'ch_consulta' => 'nullable|numeric',
            'ch_exame' => 'nullable|numeric',
            'ch_procedimento' => 'nullable|numeric',
            'valor_consulta' => 'nullable|numeric',
            'autonumeracaoguia' => 'nullable|boolean',
            'entrega_consulta' => 'nullable|date',
            'entrega_sadt' => 'nullable|date',
            'entrega_honorario' => 'nullable|date',
            'tabela_padrao_id' => "$required|integer|exists:tabelas,id",
            'tabela_medicamentos_id' => "$required|integer|exists:tabelas,id",
            'tabela_materiais_id' => "$required|integer|exists:tabelas,id",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
