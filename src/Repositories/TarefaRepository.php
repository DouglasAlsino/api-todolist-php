<?php
namespace App\Repositories;

use PDO;

/**
 * Classe TarefaRepository
 * * Responsável por todas as interações SQL com a tabela 'tarefas'.
 */
class TarefaRepository {

    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Busca todas as tarefas.
     *
     * @return array
     */
    public function findAll() {
        $stmt = $this->pdo->query("SELECT * FROM tarefas ORDER BY data_criacao DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca uma tarefa específica pelo seu ID.
     *
     * @param int $id
     * @return mixed Array com dados da tarefa ou false se não encontrar.
     */
    public function findById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM tarefas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * REQUISITO: Busca todas as tarefas de um usuário específico.
     * (Usado para a rota /api/usuarios/{id}/tarefas)
     *
     * @param int $usuario_id
     * @return array
     */
    public function findByUsuarioId(int $usuario_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM tarefas WHERE usuario_id = ? ORDER BY data_criacao DESC");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * REQUISITO: Busca todas as tarefas filtrando por status.
     * (Usado para a rota /api/tarefas?status=...)
     *
     * @param string $status 'pendente' ou 'concluida'.
     * @return array
     */
    public function findAllByStatus(string $status) {
        $stmt = $this->pdo->prepare("SELECT * FROM tarefas WHERE status = ? ORDER BY data_criacao DESC");
        $stmt->execute([$status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insere uma nova tarefa no banco.
     *
     * @param array $data Dados da tarefa (titulo, descricao, usuario_id, status)
     * @return string O ID da tarefa recém-criada.
     */
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

    /**
     * Atualiza uma tarefa (método PUT - completo).
     *
     * @param int $id ID da tarefa
     * @param array $data Dados completos (titulo, descricao, status, usuario_id)
     * @return bool
     */
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

    /**
     * REQUISITO: Atualiza parcialmente uma tarefa (método PATCH).
     * * Este é o método mais complexo, pois constrói a query SQL dinamicamente
     * baseado nos campos que vieram no JSON.
     *
     * @param int $id ID da tarefa
     * @param array $data Dados parciais (ex: ['status' => 'concluida'])
     * @return bool
     */
    public function updatePartial(int $id, array $data) {
        // 1. Monta a parte "SET" da query
        $sets = []; // Armazena "campo = ?"
        $params = []; // Armazena os valores para o execute()
        
        // Itera sobre os dados que recebemos (ex: 'status' => 'concluida')
        foreach ($data as $key => $value) {
            // Adiciona "status = ?" ao array $sets
            $sets[] = "$key = ?"; 
            // Adiciona "concluida" ao array $params
            $params[] = $value;
        }

        // Se não houver dados, não há o que atualizar
        if (count($sets) === 0) {
            return false;
        }

        // 2. Transforma o array $sets em string: "status = ?, titulo = ?"
        $setString = implode(', ', $sets);

        // 3. Adiciona o ID ao final do array de parâmetros (para o WHERE)
        $params[] = $id;

        // 4. Monta a query final
        $sql = "UPDATE tarefas SET $setString WHERE id = ?";
        
        // 5. Prepara e executa
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Deleta uma tarefa.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id) {
        $stmt = $this->pdo->prepare("DELETE FROM tarefas WHERE id = ?");
        return $stmt->execute([$id]);
    }
}