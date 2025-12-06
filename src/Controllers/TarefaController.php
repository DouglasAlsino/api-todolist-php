<?php
namespace App\Controllers;

use App\Services\TarefaService;
use Exception;

/**
 * Classe TarefaController
 *
 * Responsável por receber as requisições HTTP para /api/tarefas,
 * chamar o TarefaService e enviar a resposta JSON.
 */
class TarefaController {

    private $tarefaService;

    public function __construct(TarefaService $service) {
        $this->tarefaService = $service;
    }

   
     // Manipula GET /api/tarefas

    public function getAll() {
        // Pega o filtro da querystring, se existir
        $status = $_GET['status'] ?? null;
        
        try {
            $tarefas = $this->tarefaService->getAllTarefas($status);
            json_response($tarefas, 200);
        } catch (Exception $e) {
            // Pega a exceção do Service (ex: "Status inválido")
            json_response(['erro' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    
     // Manipula GET /api/tarefas/{id}
     
    public function getById(int $id) {
        try {
            $tarefa = $this->tarefaService->getTarefaById($id);
            json_response($tarefa, 200);
        } catch (Exception $e) {
            // Pega a exceção 404 do Service
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }

   
     //Manipula GET /api/usuarios/{id}/tarefas
   
    public function getByUsuarioId(int $usuario_id) {
        try {
            $tarefas = $this->tarefaService->getTarefasByUsuario($usuario_id);
            json_response($tarefas, 200);
        } catch (Exception $e) {
            // Pega a exceção 404 do Service se o *usuário* não existir
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }

    
     // Manipula POST /api/tarefas
     
    public function create() {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validação (Controller): Campos obrigatórios da *requisição*
        if (empty($data['titulo']) || empty($data['usuario_id'])) {
            json_response(['erro' => 'Campos obrigatórios (titulo, usuario_id) ausentes.'], 400);
            return;
        }

        // Garante que a descrição exista (mesmo que nula) para o Service
        $data['descricao'] = $data['descricao'] ?? null;

        try {
            $novaTarefa = $this->tarefaService->createTarefa($data);
            json_response($novaTarefa, 201);
        } catch (Exception $e) {
            // Pega exceções 404 (usuário não existe) ou 400 (status inválido)
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Manipula PUT /api/tarefas/{id}
     */
    public function update(int $id) {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validação (Controller): PUT exige *todos* os campos principais
        if (empty($data['titulo']) || empty($data['usuario_id']) || empty($data['status'])) {
            json_response(['erro' => 'Campos obrigatórios (titulo, usuario_id, status) ausentes para PUT.'], 400);
            return;
        }
        
        $data['descricao'] = $data['descricao'] ?? null;

        try {
            $tarefaAtualizada = $this->tarefaService->updateTarefa($id, $data);
            json_response($tarefaAtualizada, 200);
        } catch (Exception $e) {
            
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * REQUISITO: Manipula PATCH /api/tarefas/{id}
     */
    public function updatePartial(int $id) {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validação (Controller): PATCH deve ter pelo menos *um* campo
        if (empty($data)) {
            json_response(['erro' => 'Corpo da requisição vazio para PATCH.'], 400);
            return;
        }

        try {
            $tarefaAtualizada = $this->tarefaService->updatePartialTarefa($id, $data);
            json_response($tarefaAtualizada, 200);
        } catch (Exception $e) {
            
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Manipula DELETE /api/tarefas/{id}
     */
    public function delete(int $id) {
        try {
            $this->tarefaService->deleteTarefa($id);
            json_response(null, 204); // 204 No Content
        } catch (Exception $e) {
            // Pega exceção 404
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }
}
