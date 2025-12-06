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

    
     // Manipula a requisição para GET /api/usuarios
    
    public function getAll() {
        try {
            $usuarios = $this->usuarioService->getAllUsuarios();
            json_response($usuarios, 200);
        } catch (Exception $e) {
            // Erros inesperados
            json_response(['erro' => $e->getMessage()], 500);
        }
    }

    
    // Manipula a requisição para GET /api/usuarios/{id}
    
    public function getById(int $id) {
        try {
            $usuario = $this->usuarioService->getUsuarioById($id);
            json_response($usuario, 200);
        } catch (Exception $e) {
           
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Manipula a requisição para POST /api/usuarios
     */
    public function create() {
       
        $data = json_decode(file_get_contents('php://input'), true);

    
        if (empty($data['nome']) || empty($data['email'])) {
            json_response(['erro' => 'Campos obrigatórios (nome, email) ausentes.'], 400);
            return;
        }

        try {
            $novoUsuario = $this->usuarioService->createUsuario($data);
            
            json_response($novoUsuario, 201);
        } catch (Exception $e) {
         
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }

   
    public function update(int $id) {
        $data = json_decode(file_get_contents('php://input'), true);

       
        if (empty($data['nome']) || empty($data['email'])) {
            json_response(['erro' => 'Campos obrigatórios (nome, email) ausentes.'], 400);
            return;
        }

        try {
            $usuarioAtualizado = $this->usuarioService->updateUsuario($id, $data);
            json_response($usuarioAtualizado, 200); 
        } catch (Exception $e) {
            
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }

    
    public function delete(int $id) {
        try {
            $this->usuarioService->deleteUsuario($id);
           
            json_response(null, 204);
        } catch (Exception $e) {
            
            json_response(['erro' => $e->getMessage()], $e->getCode());
        }
    }
}
