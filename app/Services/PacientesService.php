<?php

namespace App\Services;

use App\Models\Pacientes;
use App\Repositories\Contracts\PacientesRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PacientesService
{
    public function __construct(protected PacientesRepositoryInterface $pacientesRepository)
    {
    }

    public function getAllPacientes(): Collection
    {
        return $this->pacientesRepository->all();
    }

    public function createPaciente(array $data): Pacientes
    {
        $this->validate($data);

        return $this->pacientesRepository->create($data);
    }

    public function updatePaciente(int $id, array $data): Pacientes
    {
        if (!$this->pacientesRepository->findById($id)) {
            throw ValidationException::withMessages(['id' => ['Paciente não encontrado.']]);
        }

        $this->validate($data, true);
        $this->pacientesRepository->update($id, $data);

        return $this->pacientesRepository->findById($id);
    }

    public function deletePaciente(int $id): void
    {
        if (!$this->pacientesRepository->findById($id)) {
            throw ValidationException::withMessages(['id' => ['Paciente não encontrado.']]);
        }

        $this->pacientesRepository->delete($id);
    }

    public function restorePaciente(int $id): Pacientes
    {
        if (!$this->pacientesRepository->findTrashedById($id)) {
            throw ValidationException::withMessages(['id' => ['Paciente excluído não encontrado.']]);
        }

        $this->pacientesRepository->restore($id);

        return $this->pacientesRepository->findById($id);
    }

    private function validate(array $data, bool $updating = false): void
    {
        $required = $updating ? 'sometimes|required' : 'required';

        $validator = Validator::make($data, [
            'nome' => "{$required}|string|max:255",
            'nome_social' => 'nullable|string|max:255',
            'convenio_id' => "{$required}|integer|exists:convenios,id",
            'matricula' => "{$required}|string|max:255",
            'numero_sus' => 'nullable|string|max:255',
            'data_nascimento' => 'nullable|date',
            'titular' => 'nullable|integer|boolean',
            'observacoes' => 'nullable|string',
            'nome_titular' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}