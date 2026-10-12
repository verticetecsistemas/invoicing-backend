<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Credenciados;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\UsersCredenciados;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CredenciadosController extends Controller
{
    //
    public function index()
    {
        Log::info('Buscando credenciados para usuário', ['user_id' => Auth::id()]);
        $credenciados = User::where('id', Auth::id())->with('credenciados')->get();
        return response()->json(['success' => true, 'data' => $credenciados], 201);
    }
    public function destroy($id)
    {
        $credenciado = Credenciados::find($id);
        if (!$credenciado) {
            return response()->json(['success' => false, 'message' => 'Credenciado não encontrado.'], 404);
        }
        $credenciado->delete();
        return response()->json(['success' => true, 'message' => 'Credenciado excluído com sucesso.']);
    }

    public function restore($id)
    {
        $credenciado = Credenciados::find($id);
        if (!$credenciado) {
            return response()->json(['success' => false, 'message' => 'Credenciado não encontrado.'], 404);
        }
        $credenciado->restore();
        return response()->json(['success' => true, 'data' => $credenciado]);
    }
    public function create(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'nullable|string|max:50',
            'cnpj' => 'nullable|string|max:30',
            'razaoSocial' => 'nullable|string|max:255',
            'nomeFantasia' => 'nullable|string|max:255',
            'inscricaoEstadual' => 'nullable|string|max:100',
            'inscricaoMunicipal' => 'nullable|string|max:100',
            'nome' => 'nullable|string|max:255',
            'apelido' => 'nullable|string|max:255',
            'cpf' => 'nullable|string|max:30',
            'cnes' => 'nullable|string|max:50',
            'cep' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:50',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:10',
            'telefone' => 'nullable|string|max:50',
            'celular' => 'nullable|string|max:50',
            'contato' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'observacoes' => 'nullable|string',
            'avatar' => 'nullable|string'
        ]);

        // Processar avatar se enviado
        $avatarPath = null;
        if (isset($validated['avatar']) && !empty($validated['avatar'])) {
            \Log::info('Avatar recebido para processamento', ['tamanho' => strlen($validated['avatar'])]);
            $avatarPath = $this->processAvatarBase64($validated['avatar']);
            \Log::info('Avatar processado', ['resultado' => $avatarPath]);
        } else {
            \Log::info('Nenhum avatar recebido ou avatar vazio');
        }

        // Mapear campos do request para os nomes das colunas do model
        $data = [
            'tipo' => $validated['tipo'] ?? null,
            'cnpj_cpf' => $validated['cnpj'] ?? $validated['cpf'] ?? null,
            'razao_social_nome' => $validated['razaoSocial'] ?? $validated['nome'] ?? null,
            'nome_fantasia_apelido' => $validated['nomeFantasia'] ?? $validated['apelido'] ?? null,
            'inscricao_estadual' => $validated['inscricaoEstadual'] ?? null,
            'inscricao_municipal' => $validated['inscricaoMunicipal'] ?? null,
            'cnes' => $validated['cnes'] ?? null,
            'cep' => $validated['cep'] ?? null,
            'logradouro' => $validated['endereco'] ?? null,
            'numero' => $validated['numero'] ?? null,
            'complemento' => $validated['contato'] ?? null,
            'bairro' => $validated['bairro'] ?? null,
            'cidade' => $validated['cidade'] ?? null,
            'uf' => $validated['estado'] ?? null,
            'telefone' => $validated['telefone'] ?? null,
            'celular' => $validated['celular'] ?? null,
            'email' => $validated['email'] ?? null,
            'observacoes' => $validated['observacoes'] ?? null,
            'avatar' => $avatarPath,
        ];

        $credenciado = Credenciados::create($data);

        $credenciadoUser = UsersCredenciados::create([
            'user_id' => Auth::id(),
            'credenciado_id' => $credenciado->id,
            'permissao' => 'A'
        ]);

        return response()->json(['success' => true, 'data' => $credenciado], 201);
    }

    public function update($id, Request $request){
        $credenciado = Credenciados::find($id);
        if (!$credenciado) {
            return response()->json(['success' => false, 'message' => 'Credenciado não encontrado.'], 404);
        }

        $validated = $request->validate([
            'tipo' => 'nullable|string|max:50',
            'cnpj' => 'nullable|string|max:30',
            'razaoSocial' => 'nullable|string|max:255',
            'nomeFantasia' => 'nullable|string|max:255',
            'inscricaoEstadual' => 'nullable|string|max:100',
            'inscricaoMunicipal' => 'nullable|string|max:100',
            'nome' => 'nullable|string|max:255',
            'apelido' => 'nullable|string|max:255',
            'cpf' => 'nullable|string|max:30',
            'cnes' => 'nullable|string|max:50',
            'cep' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:50',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:10',
            'telefone' => 'nullable|string|max:50',
            'celular' => 'nullable|string|max:50',
            'contato' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'observacoes' => 'nullable|string',
            'avatar' => 'nullable|string'
        ]);

        // Processar avatar se enviado: aceitar base64 (data:image/...) ou URL/caminho existente
        $avatarPath = $credenciado->avatar; // padrão: mantém atual
        if (array_key_exists('avatar', $validated)) {
            $incomingAvatar = $validated['avatar'];

            // Se foi enviado vazio/null/'null' explicitamente => remover avatar
            if ($incomingAvatar === null || $incomingAvatar === 'null' || $incomingAvatar === '') {
                $this->deleteAvatarFileIfExists($credenciado->avatar);
                $avatarPath = null;
            }
            // Se veio como base64 (data:image/...)
            elseif (is_string($incomingAvatar) && strpos($incomingAvatar, 'data:image') === 0) {
                $newPath = $this->processAvatarBase64($incomingAvatar);
                if ($newPath) {
                    if (!empty($credenciado->avatar) && $credenciado->avatar !== $newPath) {
                        $this->deleteAvatarFileIfExists($credenciado->avatar);
                    }
                    $avatarPath = $newPath;
                } else {
                    \Log::warning('Avatar base64 inválido no update, mantendo o atual');
                }
            }
            // Caso contrário, assume-se que seja URL/caminho já existente
            elseif (is_string($incomingAvatar)) {
                $normalized = $this->normalizeAvatarPath($incomingAvatar);
                $avatarPath = $normalized; // mantém como foi enviado (normalizado)
            }
        }

        // Mapear campos do request para os nomes das colunas do model
        $data = [
            'tipo' => $validated['tipo'] ?? null,
            'cnpj_cpf' => $validated['cnpj'] ?? $validated['cpf'] ?? null,
            'razao_social_nome' => $validated['razaoSocial'] ?? $validated['nome'] ?? null,
            'nome_fantasia_apelido' => $validated['nomeFantasia'] ?? $validated['apelido'] ?? null,
            'inscricao_estadual' => $validated['inscricaoEstadual'] ?? null,
            'inscricao_municipal' => $validated['inscricaoMunicipal'] ?? null,
            'cnes' => $validated['cnes'] ?? null,
            'cep' => $validated['cep'] ?? null,
            'logradouro' => $validated['endereco'] ?? null,
            'numero' => $validated['numero'] ?? null,
            'complemento' => $validated['contato'] ?? null,
            'bairro' => $validated['bairro'] ?? null,
            'cidade' => $validated['cidade'] ?? null,
            'uf' => $validated['estado'] ?? null,
            'telefone' => $validated['telefone'] ?? null,
            'celular' => $validated['celular'] ?? null,
            'email' => $validated['email'] ?? null,
            'observacoes' => $validated['observacoes'] ?? null,
            'avatar' => $avatarPath,
        ];

        $credenciado->update($data);

        return response()->json(['success' => true, 'data' => $credenciado], 200);
    }

    private function processAvatarBase64($base64String)
    {
        try {
            \Log::info('Processando avatar base64', ['tamanho' => strlen($base64String)]);
            
            // Verificar se é uma string base64 válida com header
            if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64String, $matches)) {
                $imageExtension = $matches[1]; // jpeg, png, etc.
                $imageData = $matches[2];
                
                \Log::info('Avatar match encontrado', ['extensao' => $imageExtension]);
                
                // Decodificar base64
                $decodedImage = base64_decode($imageData);
                
                if ($decodedImage === false) {
                    \Log::error('Falha na decodificação base64');
                    return null; // Falha na decodificação
                }
                
                // Gerar nome único para o arquivo
                $fileName = 'avatar_' . Str::uuid() . '.' . $imageExtension;
                
                // Salvar no storage/app/public/avatar
                $path = 'avatar/' . $fileName;
                
                \Log::info('Tentando salvar arquivo', ['path' => $path, 'tamanho_decodificado' => strlen($decodedImage)]);
                
                $result = Storage::disk('public')->put($path, $decodedImage);
                
                \Log::info('Resultado do salvamento', ['result' => $result, 'path' => $path]);
                
                // Retornar o caminho relativo para salvar no banco
                return $path;
            }
            
            \Log::warning('Formato de avatar inválido', ['inicio' => substr($base64String, 0, 50)]);
            return null; // Formato inválido
            
        } catch (\Exception $e) {
            \Log::error('Erro ao processar avatar', ['erro' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Normaliza uma URL/caminho de avatar para o formato relativo salvo no banco.
     * Exemplos aceitos:
     *  - http(s)://dominio/storage/avatar/arquivo.jpg -> avatar/arquivo.jpg
     *  - /storage/avatar/arquivo.jpg -> avatar/arquivo.jpg
     *  - storage/avatar/arquivo.jpg -> avatar/arquivo.jpg
     *  - avatar/arquivo.jpg -> avatar/arquivo.jpg
     */
    private function normalizeAvatarPath(string $path): ?string
    {
        try {
            $trimmed = trim($path);
            if ($trimmed === '') {
                return null;
            }

            // Se contiver o segmento /storage/ pegue o restante após ele
            if (preg_match('#/storage/(.+)$#', $trimmed, $m)) {
                return $m[1];
            }
            if (strpos($trimmed, 'storage/') === 0) {
                return substr($trimmed, strlen('storage/'));
            }
            if (strpos($trimmed, '/storage/') === 0) {
                return substr($trimmed, strlen('/storage/'));
            }
            if (strpos($trimmed, 'avatar/') === 0) {
                return $trimmed;
            }

            // Se for uma URL completa sem /storage/, mantém como veio
            return $trimmed;
        } catch (\Exception $e) {
            \Log::warning('Falha ao normalizar avatar', ['erro' => $e->getMessage(), 'path' => $path]);
            return $path;
        }
    }

    /**
     * Exclui o arquivo de avatar antigo no disco public, se existir e for um caminho relativo esperado.
     */
    private function deleteAvatarFileIfExists(?string $relativePath): void
    {
        if (!empty($relativePath) && strpos($relativePath, 'avatar/') === 0) {
            try {
                Storage::disk('public')->delete($relativePath);
            } catch (\Exception $e) {
                \Log::warning('Erro ao excluir avatar antigo', ['erro' => $e->getMessage(), 'path' => $relativePath]);
            }
        }
    }

    public function createUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'permissao' => 'required|string|min:1|confirmed',
            // Adicione outras validações conforme necessário
        ]);

        $data['password'] = bcrypt($data['password']);
        \App\Models\Users::create($data);

        return redirect()->back()->with('success', 'Usuário criado com sucesso.');
    }
}
