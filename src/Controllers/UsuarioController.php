<?php
namespace App\Controllers;

use App\Services\UsuarioService;
use Exception; // Importa a classe Exception nativa

/**
 * Classe UsuarioController
 *
 * Responsável por receber as requisições HTTP (vindas do index.php),
 * validar os dados da requisição, chamar o Service correto
 * e enviar a resposta JSON.
 */
class UsuarioController {

    private $usuarioService;

    /**
     * Construtor. Recebe o serviço por injeção de dependência.
     *
     * @param UsuarioService $service O serviço de usuários.
     */
    public function __construct(UsuarioService $service) {
        $this->usuarioService = $service;
    }

    /**
     * Manipula a requisição para GET /api/usuarios
     */
    public function getAll() {
        try {
            $usuarios = $this->usuarioService->getAllUsuarios();
            json_response($usuarios, 200);
        } catch (Exception $e) {
            // Erros inesperados
            json_response(['erro' => $e->getMessage()], 500);
        }
    }

    /**
     * Manipula a requisição para GET /api/usuarios/{id}
     *
     * @param int $id O ID do usuário vindo da URL.
     */
    public function getById(int $id) {
        try {
            $usuario = $this->usuarioService->getUsuarioById($id);
            json_response($usuario, 200);
        } catch (Exception $e) {
            // Pega a exceção do Service (ex: "Usuário não encontrado")
            // Usa o código da exceção (404) como código de status HTTP
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Manipula a requisição para POST /api/usuarios
     */
    public function create() {
        // Pega os dados brutos do corpo da requisição (JSON)
        $data = json_decode(file_get_contents('php://input'), true);

        // Validação (Controller): Verifica se os campos obrigatórios vieram.
        // O Service validará as *regras de negócio* (ex: e-mail duplicado).
        if (empty($data['nome']) || empty($data['email'])) {
            // 400 Bad Request - Requisição mal formatada ou campos faltando
            json_response(['erro' => 'Campos obrigatórios (nome, email) ausentes.'], 400);
            return;
        }

        try {
            $novoUsuario = $this->usuarioService->createUsuario($data);
            // 201 Created - Padrão para POST bem-sucedido
            json_response($novoUsuario, 201);
        } catch (Exception $e) {
            // Pega a exceção do Service (ex: "Email já cadastrado")
            // Usa o código da exceção (409) como código de status HTTP
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Manipula a requisição para PUT /api/usuarios/{id}
     *
     * @param int $id O ID do usuário vindo da URL.
     */
    public function update(int $id) {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validação (Controller): Verifica campos obrigatórios
        if (empty($data['nome']) || empty($data['email'])) {
            json_response(['erro' => 'Campos obrigatórios (nome, email) ausentes.'], 400);
            return;
        }

        try {
            $usuarioAtualizado = $this->usuarioService->updateUsuario($id, $data);
            json_response($usuarioAtualizado, 200); // 200 OK
        } catch (Exception $e) {
            // Pega exceções do Service (404, 409)
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Manipula a requisição para DELETE /api/usuarios/{id}
     *
     * @param int $id O ID do usuário vindo da URL.
     */
    public function delete(int $id) {
        try {
            $this->usuarioService->deleteUsuario($id);
            // 204 No Content - Padrão para DELETE bem-sucedido (não retorna corpo)
            json_response(null, 204);
        } catch (Exception $e) {
            // Pega exceção 404 do Service
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }
}