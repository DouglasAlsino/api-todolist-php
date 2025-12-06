<?php
namespace App\Services;

use App\Repositories\UsuarioRepository;
use Exception; 


class UsuarioService {

    private $usuarioRepository;

    
    public function __construct(UsuarioRepository $repo) {
        $this->usuarioRepository = $repo;
    }

    
    public function getAllUsuarios() {
        return $this->usuarioRepository->findAll();
    }

 
    public function getUsuarioById(int $id) {
        $usuario = $this->usuarioRepository->findById($id);
        
        
        if (!$usuario) {
            
            throw new Exception("Usuário não encontrado.", 404);
        }
        return $usuario;
    }

    
    public function createUsuario(array $data) {
        // Regra de Negócio: O e-mail não pode estar duplicado.
        $usuarioExistente = $this->usuarioRepository->findByEmail($data['email']);
        
        if ($usuarioExistente) {
            
            throw new Exception("Email já cadastrado.", 409);
        }
        
        
        $novoUsuarioId = $this->usuarioRepository->create($data['nome'], $data['email']);
        
        
        return $this->usuarioRepository->findById($novoUsuarioId);
    }

    
    public function updateUsuario(int $id, array $data) {
        
        $this->getUsuarioById($id); 
        
        
        $usuarioComEmail = $this->usuarioRepository->findByEmail($data['email']);
        
        if ($usuarioComEmail && $usuarioComEmail['id'] != $id) {
            throw new Exception("Email já cadastrado por outro usuário.", 409);
        }
        
        // Se passou nas regras, atualiza
        $this->usuarioRepository->update($id, $data['nome'], $data['email']);
        
        // Retorna o usuário com os dados atualizados
        return $this->usuarioRepository->findById($id);
    }

    
    public function deleteUsuario(int $id) {
       
        $this->getUsuarioById($id); // Lança 404 se não existir
        
        // Se existir, deleta
        $this->usuarioRepository->delete($id);
    }
}
