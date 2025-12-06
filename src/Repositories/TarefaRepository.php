<?php
namespace App\Repositories;

use PDO;


class TarefaRepository {

    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    
    public function findAll() {
        $stmt = $this->pdo->query("SELECT * FROM tarefas ORDER BY data_criacao DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function findById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM tarefas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
    public function findByUsuarioId(int $usuario_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM tarefas WHERE usuario_id = ? ORDER BY data_criacao DESC");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function findAllByStatus(string $status) {
        $stmt = $this->pdo->prepare("SELECT * FROM tarefas WHERE status = ? ORDER BY data_criacao DESC");
        $stmt->execute([$status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   
    public function create(array $data) {
        $sql = "INSERT INTO tarefas (titulo, descricao, status, usuario_id) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['titulo'],
            $data['descricao'],
            $data['status'],
            $data['usuario_id']
        ]);
        return $this->pdo->lastInsertId();
    }

   
    public function update(int $id, array $data) {
        $sql = "UPDATE tarefas 
                SET titulo = ?, descricao = ?, status = ?, usuario_id = ? 
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['titulo'],
            $data['descricao'],
            $data['status'],
            $data['usuario_id'],
            $id
        ]);
    }

    
    public function updatePartial(int $id, array $data) {
        
        $sets = []; 
        $params = []; 
        

        foreach ($data as $key => $value) {
        
            $sets[] = "$key = ?"; 
            
            $params[] = $value;
        }

       
        if (count($sets) === 0) {
            return false;
        }

       
        $setString = implode(', ', $sets);

       
        $params[] = $id;

        
        $sql = "UPDATE tarefas SET $setString WHERE id = ?";
        
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

  
    public function delete(int $id) {
        $stmt = $this->pdo->prepare("DELETE FROM tarefas WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
