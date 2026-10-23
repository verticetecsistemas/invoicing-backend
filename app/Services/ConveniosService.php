<?php

namespace App\Services;

use App\Models\Convenios;
use App\Repositories\Contracts\ConveniosRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConveniosService
{
    /**
     * @var ConveniosRepositoryInterface
     */
    protected $conveniosRepository;

    /**
     * ConveniosService constructor.
     *
     * @param ConveniosRepositoryInterface $conveniosRepository
     */
    public function __construct(ConveniosRepositoryInterface $conveniosRepository)
    {
        $this->conveniosRepository = $conveniosRepository;
    }

    /**
     * Criar um novo convênio
     *
     * @param array $data
     * @return Convenios
     * @throws ValidationException
     */
    public function createConvenio(array $data): Convenios
    {
        // Validar dados
        $validator = Validator::make($data, [
            'cnpj' => 'required|string|max:18|unique:convenios,cnpj',
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'endereco' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:9',
            'telefone' => 'nullable|string|max:20',
            'telefone2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'observacao' => 'nullable|string',
            'ans' => 'nullable|string|max:50',
            'digitos_matricula' => 'nullable|integer',
            'retorno_dias' => 'nullable|integer',
            'avatar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Verificar se CNPJ já existe
        if ($this->conveniosRepository->findByCnpj($data['cnpj'])) {
            throw ValidationException::withMessages([
                'cnpj' => ['O CNPJ informado já está cadastrado.']
            ]);
        }

        if (array_key_exists('avatar', $data)) {
            $data['avatar'] = $this->processAvatar($data['avatar']);
        }

        // Criar convênio
        return $this->conveniosRepository->create($data);
    }

    /**
     * Buscar convênio por ID
     *
     * @param int $id
     * @return Convenios|null
     */
    public function getConvenioById(int $id): ?Convenios
    {
        return $this->conveniosRepository->findById($id);
    }

    /**
     * Listar todos os convênios
     *
     * @return Collection
     */
    public function getAllConvenios(): Collection
    {
        return $this->conveniosRepository->all();
    }

    /**
     * Atualizar convênio
     *
     * @param int $id
     * @param array $data
     * @return Convenios
     * @throws ValidationException
     */
    public function updateConvenio(int $id, array $data): Convenios
    {
        // Validar dados
        $validator = Validator::make($data, [
            'cnpj' => 'sometimes|required|string|max:18|unique:convenios,cnpj,' . $id,
            'razao_social' => 'sometimes|required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'endereco' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:9',
            'telefone' => 'nullable|string|max:20',
            'telefone2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'observacao' => 'nullable|string',
            'ans' => 'nullable|string|max:50',
            'digitos_matricula' => 'nullable|integer',
            'retorno_dias' => 'nullable|integer',
            'avatar' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Verificar se convênio existe
        $convenio = $this->conveniosRepository->findById($id);
        if (!$convenio) {
            throw ValidationException::withMessages([
                'id' => ['Convênio não encontrado.']
            ]);
        }

        if (array_key_exists('avatar', $data)) {
            $data['avatar'] = $this->processAvatar($data['avatar']);
        }

        // Atualizar convênio
        $this->conveniosRepository->update($id, $data);
        
        return $this->conveniosRepository->findById($id);
    }

    /**
     * Deletar convênio
     *
     * @param int $id
     * @return bool
     * @throws ValidationException
     */
    public function deleteConvenio(int $id): bool
    {
        $convenio = $this->conveniosRepository->findById($id);
        
        if (!$convenio) {
            throw ValidationException::withMessages([
                'id' => ['Convênio não encontrado.']
            ]);
        }

        return $this->conveniosRepository->delete($id);
    }

    private function processAvatar(?string $avatar): ?string
    {
        if ($avatar === null || $avatar === '') {
            return null;
        }

        if (strpos($avatar, 'data:image/') !== 0) {
            return $avatar;
        }

        if (!preg_match('/^data:image\/(jpeg|jpg|png|gif|webp);base64,([A-Za-z0-9+\/]+={0,2})$/i', $avatar, $matches)) {
            throw ValidationException::withMessages([
                'avatar' => ['O avatar deve ser uma imagem base64 válida.']
            ]);
        }

        $image = base64_decode($matches[2], true);
        if ($image === false) {
            throw ValidationException::withMessages([
                'avatar' => ['O avatar deve ser uma imagem base64 válida.']
            ]);
        }

        $extension = strtolower($matches[1]) === 'jpg' ? 'jpeg' : strtolower($matches[1]);
        $path = 'avatar/avatar_' . Str::uuid() . '.' . $extension;

        if (!Storage::disk('public')->put($path, $image)) {
            throw new \RuntimeException('Não foi possível salvar o avatar do convênio.');
        }

        return $path;
    }
}
