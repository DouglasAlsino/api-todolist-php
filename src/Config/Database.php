<?php

// Definimos o namespace, seguindo o padrão PSR-4
// que configuramos no composer.json
// "App\" corresponde a "src/"
namespace App\Config;

// Importamos as classes nativas do PHP (PDO e PDOException)
// para dentro do nosso namespace, para facilitar o uso.
use PDO;
use PDOException;

class Database {

    // -------------------------------------------------------------------------
    // Configurações do Banco de Dados
    // (Ajuste se o seu WAMP tiver uma senha)
    // -------------------------------------------------------------------------
    private static $host = '127.0.0.1'; // ou 'localhost'
    private static $db_name = 'todolistbe2'; // O nome do banco que criamos
    private static $username = 'root'; // Usuário padrão do WAMP
    private static $password = 'root'; // Senha padrão do WAMP (vazia)
    // -------------------------------------------------------------------------

    // A instância (conexão) PDO
    private static $conn;

    /**
     * Obtém a conexão PDO com o banco de dados (padrão Singleton).
     * * @return PDO A instância da conexão PDO.
     */
    public static function getConnection() {
        
        // Se a conexão ainda não foi criada, cria uma nova
        if (!isset(self::$conn)) {
            // DSN (Data Source Name) para o MySQL
            $dsn = 'mysql:host=' . self::$host . ';dbname=' . self::$db_name . ';charset=utf8mb4';

            try {
                // 1. Cria a instância do PDO
                self::$conn = new PDO($dsn, self::$username, self::$password);
                
                // 2. Define atributos do PDO para um melhor controle de erros
                // Lança exceções em caso de erro (em vez de warnings)
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // 3. Define o modo de "fetch" padrão
                // Retorna os resultados como arrays associativos (ex: $linha['nome'])
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                // Se a conexão falhar, exibe o erro e mata a aplicação.
                // Em uma API real, isso seria tratado pela nossa função json_response.
                // Por enquanto, 'die' é suficiente para diagnóstico.
                die('Erro de Conexão: ' . $e->getMessage());
            }
        }
        
        // Retorna a conexão (seja a nova ou a já existente)
        return self::$conn;
    }
}