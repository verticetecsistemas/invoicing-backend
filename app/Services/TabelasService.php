<?php

namespace App\Services;

use App\Models\Tabelas;
use App\Repositories\Contracts\TabelasRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TabelasService
{
    /**
     * @var TabelasRepositoryInterface
     */
    protected $tabelasRepository;

    /**
     * TabelasService constructor.
     *
     * @param TabelasRepositoryInterface $tabelasRepository
     */
    public function __construct(TabelasRepositoryInterface $tabelasRepository)
    {
        $this->tabelasRepository = $tabelasRepository;
    }

    /**
     * Criar uma nova tabela
     *
     * @param array $data
     * @return Tabelas
     * @throws ValidationException
     */
    public function createTabela(array $data): Tabelas
    {
        // Validar dados
        $validator = Validator::make($data, [
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string|max:100',
            'ans' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Criar tabela
        return $this->tabelasRepository->create($data);
    }

    /**
     * Buscar tabela por ID
     *
     * @param int $id
     * @return Tabelas|null
     */
    public function getTabelaById(int $id): ?Tabelas
    {
        return $this->tabelasRepository->findById($id);
    }

    /**
     * Listar todas as tabelas
     *
     * @return Collection
     */
    public function getAllTabelas(): Collection
    {
        return $this->tabelasRepository->all();
    }

    /**
     * Atualizar tabela
     *
     * @param int $id
     * @param array $data
     * @return Tabelas
     * @throws ValidationException
     */
    public function updateTabela(int $id, array $data): Tabelas
    {
        // Validar dados
        $validator = Validator::make($data, [
            'nome' => 'sometimes|required|string|max:255',
            'tipo' => 'sometimes|required|string|max:100',
            'ans' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Verificar se a tabela existe
        $tabela = $this->tabelasRepository->findById($id);
        if (!$tabela) {
            throw ValidationException::withMessages([
                'id' => ['Tabela não encontrada.']
            ]);
        }

        // Atualizar tabela
        $this->tabelasRepository->update($id, $data);

        // Retornar tabela atualizada
        return $this->tabelasRepository->findById($id);
    }

    /**
     * Deletar tabela
     *
     * @param int $id
     * @return void
     * @throws ValidationException
     */
    public function deleteTabela(int $id): void
    {
        $tabela = $this->tabelasRepository->findById($id);
        if (!$tabela) {
            throw ValidationException::withMessages([
                'id' => ['Tabela não encontrada.']
            ]);
        }

        $this->tabelasRepository->delete($id);
    }

    /**
     * Restaurar tabela deletada
     *
     * @param int $id
     * @return Tabelas
     * @throws ValidationException
     */
    public function restoreTabela(int $id): Tabelas
    {
        // Verificar se a tabela deletada existe
        $tabela = $this->tabelasRepository->findTrashedById($id);
        if (!$tabela) {
            throw ValidationException::withMessages([
                'id' => ['Tabela deletada não encontrada.']
            ]);
        }

        // Restaurar tabela
        $this->tabelasRepository->restore($id);

        // Retornar tabela restaurada
        return $this->tabelasRepository->findById($id);
    }
}
