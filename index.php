<?php
// Exibe erros (bom para desenvolvimento)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// -------------------------------------------------------------------------
// AUTOLOAD (Carrega todas as nossas classes 'App\...')
// -------------------------------------------------------------------------
require __DIR__ . '/vendor/autoload.php';

// -------------------------------------------------------------------------
// --- NOVO: IMPORTAÇÃO DAS CLASSES (Use statements) ---
// -------------------------------------------------------------------------
// Usamos 'use' para não precisar escrever o caminho completo da classe
use App\Config\Database;
use App\Repositories\UsuarioRepository;
use App\Services\UsuarioService;
use App\Controllers\UsuarioController;

// -------------------------------------------------------------------------
// FUNÇÃO HELPER PARA RESPOSTAS JSON
// -------------------------------------------------------------------------
function json_response($data = null, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    if ($data) {
        echo json_encode($data);
    }
    exit;
}

// -------------------------------------------------------------------------
// --- NOVO: INJEÇÃO DE DEPENDÊNCIA (C-S-R) ---
// -------------------------------------------------------------------------
// Aqui nós "montamos" a aplicação, injetando as dependências
// da camada mais interna para a mais externa (Repo -> Service -> Controller)

// 1. Pega a conexão PDO (Camada de Config)
$pdo = Database::getConnection();

// 2. Cria o Repository, injetando o PDO
$usuarioRepository = new UsuarioRepository($pdo);

// 3. Cria o Service, injetando o Repository
$usuarioService = new UsuarioService($usuarioRepository);

// 4. Cria o Controller, injetando o Service
$usuarioController = new UsuarioController($usuarioService);

// (Quando fizermos as Tarefas, faremos a mesma coisa para elas aqui)
// $tarefaRepository = ...
// $tarefaService = ...
// $tarefaController = ...

// -------------------------------------------------------------------------
// CAPTURA DA REQUISIÇÃO
// -------------------------------------------------------------------------
$method = $_SERVER['REQUEST_METHOD'];
$path_info = $_GET['path'] ?? '';
$path = explode('/', trim($path_info, '/'));
$resource = $path[0];

// -------------------------------------------------------------------------
// ROTEAMENTO
// -------------------------------------------------------------------------
switch ($resource) {

    // Rota: / (Apresentação)
    case '':
        if ($method === 'GET') {
            $apiInfo = [
                'autores' => [
                    ['nome' => 'Seu Nome Aqui', 'email' => 'seu.email@aqui.com']
                ],
                'descricao' => 'API de Sistema de Tarefas (ToDo List) - Trabalho de Backend 2.',
                'rotas' => [
                    // ... (copie a lista de rotas do Passo 3 aqui) ...
                    ['metodo' => 'GET', 'caminho' => '/'],
                    ['metodo' => 'GET', 'caminho' => '/api/usuarios'],
                    // ... etc ...
                ]
            ];
            json_response($apiInfo, 200);
        } else {
            json_response(['erro' => 'Método não permitido para a raiz'], 405);
        }
        break;

    // Rota: /api/...
    case 'api':
        $apiResource = $path[1] ?? null;

        switch ($apiResource) {
            
            // --- ATUALIZADO: Rota /api/usuarios ---
            case 'usuarios':
                // Pega o ID da URL, se existir (ex: /api/usuarios/1)
                // (int) converte o ID para inteiro por segurança
                $id = isset($path[2]) ? (int)$path[2] : null;

                // Direciona para o método correto do Controller baseado no Método HTTP
                switch ($method) {
                    case 'GET':
                        if ($id) {
                            $usuarioController->getById($id);
                        } else {
                            $usuarioController->getAll();
                        }
                        break;
                    case 'POST':
                        // POST só pode ser na coleção (sem ID)
                        if ($id) {
                            json_response(['erro' => 'Não é possível criar com ID.'], 400);
                        } else {
                            $usuarioController->create();
                        }
                        break;
                    case 'PUT':
                        // PUT deve ter um ID
                        if ($id) {
                            $usuarioController->update($id);
                        } else {
                            json_response(['erro' => 'ID do usuário não fornecido para atualização.'], 400);
                        }
                        break;
                    case 'DELETE':
                        // DELETE deve ter um ID
                        if ($id) {
                            $usuarioController->delete($id);
                        } else {
                            json_response(['erro' => 'ID do usuário não fornecido para deleção.'], 400);
                        }
                        break;
                    default:
                        // Se for PATCH, OPTIONS, etc.
                        json_response(['erro' => 'Método não permitido.'], 405);
                }
                break; // Fim do 'case usuarios'

            case 'tarefas':
                // (Futuramente, aqui chamará o TarefaController)
                json_response(['mensagem' => 'Você acessou a rota de TAREFAS (' . $method . ')'], 200);
                break;

            default:
                json_response(['erro' => 'Recurso de API não encontrado'], 404);
                break;
        }
        break; // Fim do 'case api'

    default:
        json_response(['erro' => 'Rota não encontrada'], 404);
        break;
}