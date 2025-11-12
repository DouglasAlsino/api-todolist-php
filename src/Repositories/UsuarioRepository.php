<?php
namespace App\Repositories;

use PDO; // Importa a classe PDO nativa

/**
 * Classe UsuarioRepository
 * * Esta classe é responsável por toda a interação (queries SQL)
 * com a tabela 'usuarios' no banco de dados.
 */
class UsuarioRepository {

    /**
     * A instância de conexão PDO.
     * @var PDO
     */
    private $pdo;

    /**
     * Construtor. Recebe a conexão PDO por injeção de dependência.
     *
     * @param PDO $pdo A instância da conexão PDO.
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Busca todos os usuários no banco de dados.
     *
     * @return array Uma lista de todos os usuários.
     */
    public function findAll() {
        $stmt = $this->pdo->query("SELECT * FROM usuarios ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um usuário específico pelo seu ID.
     *
     * @param int $id O ID do usuário.
     * @return mixed Os dados do usuário (array) ou false se não for encontrado.
     */
    public function findById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // fetch() pois esperamos um resultado
    }

    /**
     * Busca um usuário pelo seu e-mail.
     * (Útil para validação de e-mail duplicado)
     *
     * @param string $email O e-mail a ser verificado.
     * @return mixed Os dados do usuário (array) ou false se não for encontrado.
     */
    public function findByEmail(string $email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Insere um novo usuário no banco de dados.
     *
     * @param string $nome O nome do usuário.
     * @param string $email O e-mail do usuário.
     * @return string O ID do usuário recém-criado.
     */
    public function create(string $nome, string $email) {
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email) VALUES (?, ?)");
        $stmt->execute([$nome, $email]);
        // Retorna o ID do último registro inserido
        return $this->pdo->lastInsertId();
    }

    /**
     * Atualiza um usuário existente no banco de dados.
     *
     * @param int $id O ID do usuário a ser atualizado.
     * @param string $nome O novo nome.
     * @param string $email O novo e-mail.
     * @return bool True se a atualização foi bem-sucedida, false caso contrário.
     */
    public function update(int $id, string $nome, string $email) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
        return $stmt->execute([$nome, $email, $id]);
    }

    /**
     * Deleta um usuário do banco de dados.
     *
     * @param int $id O ID do usuário a ser deletado.
     * @return bool True se a deleção foi bem-sucedida, false caso contrário.
     */
    public function delete(int $id) {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}