<?php
namespace App\Services;

// Importamos os DOIS repositórios que vamos usar
use App\Repositories\TarefaRepository;
use App\Repositories\UsuarioRepository;
use Exception;

/**
 * Classe TarefaService
 *
 * Contém as regras de negócio para a entidade Tarefa.
 */
class TarefaService {

    private $tarefaRepository;
    private $usuarioRepository; // Precisamos dele para validar o usuario_id

    /**
     * Construtor com Injeção de Dependência de AMBOS repositórios.
     */
    public function __construct(TarefaRepository $tarefaRepo, UsuarioRepository $usuarioRepo) {
        $this->tarefaRepository = $tarefaRepo;
        $this->usuarioRepository = $usuarioRepo;
    }

    /**
     * Valida se um status é 'pendente' ou 'concluida'.
     *
     * @param string $status
     * @throws Exception se o status for inválido (400 Bad Request)
     */
    private function validarStatus(string $status) {
        if ($status !== 'pendente' && $status !== 'concluida') {
            throw new Exception("Status inválido. Use 'pendente' ou 'concluida'.", 400);
        }
    }

    /**
     * Regra de Negócio: Verifica se um usuário existe.
     *
     * @param int $usuario_id
     * @throws Exception se o usuário não for encontrado (404 Not Found)
     */
    private function validarUsuario(int $usuario_id) {
        $usuario = $this->usuarioRepository->findById($usuario_id);
        if (!$usuario) {
            throw new Exception("Usuário (usuario_id) não encontrado.", 404);
        }
    }

    /**
     * Obtém todas as tarefas, com filtro opcional por status.
     *
     * @param string|null $status
     * @return array
     */
    public function getAllTarefas(?string $status) {
        if ($status) {
            $this->validarStatus($status); // Valida o status do filtro
            return $this->tarefaRepository->findAllByStatus($status);
        } else {
            return $this->tarefaRepository->findAll();
        }
    }

    /**
     * Obtém uma tarefa pelo ID.
     *
     * @param int $id
     * @return array
     * @throws Exception se a tarefa não for encontrada (404)
     */
    public function getTarefaById(int $id) {
        $tarefa = $this->tarefaRepository->findById($id);
        
        // Regra: Tarefa deve existir
        if (!$tarefa) {
            throw new Exception("Tarefa não encontrada.", 404);
        }
        return $tarefa;
    }

    /**
     * Obtém todas as tarefas de um usuário específico.
     *
     * @param int $usuario_id
     * @return array
     * @throws Exception se o usuário não for encontrado (404)
     */
    public function getTarefasByUsuario(int $usuario_id) {
        // Regra: O usuário precisa existir
        $this->validarUsuario($usuario_id);
        
        return $this->tarefaRepository->findByUsuarioId($usuario_id);
    }

    /**
     * Cria uma nova tarefa.
     *
     * @param array $data (titulo, descricao, usuario_id, status opcional)
     * @return array A tarefa criada
     */
    public function createTarefa(array $data) {
        // Regra: O usuário (dono da tarefa) deve existir
        $this->validarUsuario($data['usuario_id']);

        // Regra: Se o status não for enviado, define 'pendente'
        if (empty($data['status'])) {
            $data['status'] = 'pendente';
        } else {
            // Regra: Se o status foi enviado, ele deve ser válido
            $this->validarStatus($data['status']);
        }
        
        $novoId = $this->tarefaRepository->create($data);
        return $this->tarefaRepository->findById($novoId);
    }

    /**
     * Atualiza uma tarefa (PUT).
     *
     * @param int $id
     * @param array $data (titulo, descricao, usuario_id, status)
     * @return array A tarefa atualizada
     */
    public function updateTarefa(int $id, array $data) {
        // Regra 1: A tarefa deve existir
        $this->getTarefaById($id); // Lança 404 se não existir

        // Regra 2: O (novo) usuário deve existir
        $this->validarUsuario($data['usuario_id']);
        
        // Regra 3: O (novo) status deve ser válido
        $this->validarStatus($data['status']);

        $this->tarefaRepository->update($id, $data);
        return $this->tarefaRepository->findById($id);
    }

    /**
     * Atualiza parcialmente uma tarefa (PATCH).
     *
     * @param int $id
     * @param array $data Dados parciais (ex: ['status' => 'concluida'])
     * @return array A tarefa atualizada
     */
    public function updatePartialTarefa(int $id, array $data) {
        // Regra 1: A tarefa deve existir
        $this->getTarefaById($id);
        
        // Regra 2: Se o 'usuario_id' foi enviado, valida ele
        if (isset($data['usuario_id'])) {
            $this->validarUsuario($data['usuario_id']);
        }
        
        // Regra 3: Se o 'status' foi enviado, valida ele
        if (isset($data['status'])) {
            $this->validarStatus($data['status']);
        }

        $this->tarefaRepository->updatePartial($id, $data);
        return $this->tarefaRepository->findById($id);
    }

    /**
     * Deleta uma tarefa.
     *
     * @param int $id
     * @throws Exception se a tarefa não for encontrada (404)
     */
    public function deleteTarefa(int $id) {
        // Regra: A tarefa deve existir para ser deletada
        $this->getTarefaById($id); // Lança 404 se não existir
        
        $this->tarefaRepository->delete($id);
    }
}