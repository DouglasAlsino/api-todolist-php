<?php
namespace App\Services;

// Importamos os DOIS repositórios que vamos usar
use App\Repositories\TarefaRepository;
use App\Repositories\UsuarioRepository;
use Exception;


class TarefaService {

    private $tarefaRepository;
    private $usuarioRepository; // Precisamos dele para validar o usuario_id

   
    public function __construct(TarefaRepository $tarefaRepo, UsuarioRepository $usuarioRepo) {
        $this->tarefaRepository = $tarefaRepo;
        $this->usuarioRepository = $usuarioRepo;
    }

    
    private function validarStatus(string $status) {
        if ($status !== 'pendente' && $status !== 'concluida') {
            throw new Exception("Status inválido. Use 'pendente' ou 'concluida'.", 400);
        }
    }

    
    private function validarUsuario(int $usuario_id) {
        $usuario = $this->usuarioRepository->findById($usuario_id);
        if (!$usuario) {
            throw new Exception("Usuário (usuario_id) não encontrado.", 404);
        }
    }

   
    public function getAllTarefas(?string $status) {
        if ($status) {
            $this->validarStatus($status); // Valida o status do filtro
            return $this->tarefaRepository->findAllByStatus($status);
        } else {
            return $this->tarefaRepository->findAll();
        }
    }

    
    public function getTarefaById(int $id) {
        $tarefa = $this->tarefaRepository->findById($id);
        
        // Regra: Tarefa deve existir
        if (!$tarefa) {
            throw new Exception("Tarefa não encontrada.", 404);
        }
        return $tarefa;
    }

   
    public function getTarefasByUsuario(int $usuario_id) {
        // Regra: O usuário precisa existir
        $this->validarUsuario($usuario_id);
        
        return $this->tarefaRepository->findByUsuarioId($usuario_id);
    }

    
    public function createTarefa(array $data) {
        
        $this->validarUsuario($data['usuario_id']);

        
        if (empty($data['status'])) {
            $data['status'] = 'pendente';
        } else {
           
            $this->validarStatus($data['status']);
        }
        
        $novoId = $this->tarefaRepository->create($data);
        return $this->tarefaRepository->findById($novoId);
    }

    
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

    
    public function updatePartialTarefa(int $id, array $data) {
        // Regra 1: A tarefa deve existir
        $this->getTarefaById($id);
        
       
        if (isset($data['usuario_id'])) {
            $this->validarUsuario($data['usuario_id']);
        }
        
        
        if (isset($data['status'])) {
            $this->validarStatus($data['status']);
        }

        $this->tarefaRepository->updatePartial($id, $data);
        return $this->tarefaRepository->findById($id);
    }

    
    public function deleteTarefa(int $id) {
        
        $this->getTarefaById($id); // Lança 404 se não existir
        
        $this->tarefaRepository->delete($id);
    }
}
