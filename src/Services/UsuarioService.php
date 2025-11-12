<?php
namespace App\Services;

use App\Repositories\UsuarioRepository;
use Exception; // Importa a classe Exception nativa

/**
 * Classe UsuarioService
 *
 * Contém as regras de negócio para a entidade Usuário.
 * Ela usa o UsuarioRepository para interagir com o banco.
 */
class UsuarioService {

    private $usuarioRepository;

    /**
     * Construtor. Recebe o repositório por injeção de dependência.
     *
     * @param UsuarioRepository $repo O repositório de usuários.
     */
    public function __construct(UsuarioRepository $repo) {
        $this->usuarioRepository = $repo;
    }

    /**
     * Obtém todos os usuários.
     *
     * @return array
     */
    public function getAllUsuarios() {
        return $this->usuarioRepository->findAll();
    }

    /**
     * Obtém um usuário pelo ID.
     *
     * @param int $id
     * @return array Os dados do usuário.
     * @throws Exception Se o usuário não for encontrado (para ser pego pelo Controller).
     */
    public function getUsuarioById(int $id) {
        $usuario = $this->usuarioRepository->findById($id);
        
        // Regra de Negócio: O usuário deve existir.
        if (!$usuario) {
            // Lançamos uma exceção com o código de status HTTP
            throw new Exception("Usuário não encontrado.", 404);
        }
        return $usuario;
    }

    /**
     * Cria um novo usuário.
     *
     * @param array $data Dados do usuário (nome, email).
     * @return array O usuário recém-criado.
     * @throws Exception Se o e-mail já estiver em uso (para ser pego pelo Controller).
     */
    public function createUsuario(array $data) {
        // Regra de Negócio: O e-mail não pode estar duplicado.
        $usuarioExistente = $this->usuarioRepository->findByEmail($data['email']);
        
        if ($usuarioExistente) {
            // 409 Conflict é o código HTTP ideal para "conflito de recurso existente"
            throw new Exception("Email já cadastrado.", 409);
        }
        
        // Se passou nas regras, cria o usuário
        $novoUsuarioId = $this->usuarioRepository->create($data['nome'], $data['email']);
        
        // Retorna o usuário recém-criado (para a resposta JSON)
        return $this->usuarioRepository->findById($novoUsuarioId);
    }

    /**
     * Atualiza um usuário existente.
     *
     * @param int $id O ID do usuário a atualizar.
     * @param array $data Os novos dados (nome, email).
     * @return array O usuário atualizado.
     * @throws Exception Se o usuário não for encontrado ou o e-mail duplicar.
     */
    public function updateUsuario(int $id, array $data) {
        // Regra 1: Garante que o usuário que queremos atualizar existe.
        $this->getUsuarioById($id); // Reutiliza o método que já tem a exceção 404
        
        // Regra 2: Verifica se o NOVO e-mail já está em uso por OUTRO usuário.
        $usuarioComEmail = $this->usuarioRepository->findByEmail($data['email']);
        
        if ($usuarioComEmail && $usuarioComEmail['id'] != $id) {
            throw new Exception("Email já cadastrado por outro usuário.", 409);
        }
        
        // Se passou nas regras, atualiza
        $this->usuarioRepository->update($id, $data['nome'], $data['email']);
        
        // Retorna o usuário com os dados atualizados
        return $this->usuarioRepository->findById($id);
    }

    /**
     * Deleta um usuário.
     *
     * @param int $id O ID do usuário a deletar.
     * @throws Exception Se o usuário não for encontrado.
     */
    public function deleteUsuario(int $id) {
        // Regra: Garante que o usuário existe antes de tentar deletar.
        $this->getUsuarioById($id); // Lança 404 se não existir
        
        // Se existir, deleta
        $this->usuarioRepository->delete($id);
    }
}