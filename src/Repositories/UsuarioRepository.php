<?php
namespace App\Repositories;

use PDO; // Importa a classe PDO nativa


class UsuarioRepository {

   
    private $pdo;

    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    
    public function findAll() {
        $stmt = $this->pdo->query("SELECT * FROM usuarios ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function findById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // fetch() pois esperamos um resultado
    }

    
    public function findByEmail(string $email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

   
    public function create(string $nome, string $email) {
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email) VALUES (?, ?)");
        $stmt->execute([$nome, $email]);
        // Retorna o ID do último registro inserido
        return $this->pdo->lastInsertId();
    }

    
    public function update(int $id, string $nome, string $email) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
        return $stmt->execute([$nome, $email, $id]);
    }

    
    public function delete(int $id) {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
