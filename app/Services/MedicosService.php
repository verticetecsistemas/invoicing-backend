<?php

namespace App\Services;

use App\Models\Medicos;
use App\Repositories\Contracts\MedicosRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MedicosService
{
    public function __construct(protected MedicosRepositoryInterface $medicosRepository)
    {
    }

    public function getAllMedicos(): Collection
    {
        return $this->medicosRepository->all();
    }

    public function createMedico(array $data): Medicos
    {
        $this->validate($data);

        return $this->medicosRepository->create($data);
    }

    public function updateMedico(int $id, array $data): Medicos
    {
        if (!$this->medicosRepository->findById($id)) {
            throw ValidationException::withMessages(['id' => ['Médico não encontrado.']]);
        }

        $this->validate($data, $id, true);
        $this->medicosRepository->update($id, $data);

        return $this->medicosRepository->findById($id);
    }

    public function deleteMedico(int $id): void
    {
        if (!$this->medicosRepository->findById($id)) {
            throw ValidationException::withMessages(['id' => ['Médico não encontrado.']]);
        }

        $this->medicosRepository->delete($id);
    }

    public function restoreMedico(int $id): Medicos
    {
        if (!$this->medicosRepository->findTrashedById($id)) {
            throw ValidationException::withMessages(['id' => ['Médico excluído não encontrado.']]);
        }

        $this->medicosRepository->restore($id);

        return $this->medicosRepository->findById($id);
    }

    private function validate(array $data, ?int $id = null, bool $updating = false): void
    {
        $required = $updating ? 'sometimes|required' : 'required';
        $cpfRule = $id
            ? "{$required}|string|max:14|unique:medicos,cpf,{$id}"
            : "{$required}|string|max:14|unique:medicos,cpf";

        $validator = Validator::make($data, [
            'nome' => "{$required}|string|max:255",
            'cpf' => $cpfRule,
            'especialidade_id' => "{$required}|integer|exists:especialidades,id",
            'conselho_id' => "{$required}|integer|exists:conselhos,id",
            'numero_conselho' => "{$required}|string|max:255",
            'uf_conselho' => "{$required}|string|size:2",
            'telefone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}