<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

// -------------------------------------------------------------------------
// IMPORTAÇÃO DAS CLASSES (Use statements)

use App\Config\Database;

use App\Repositories\UsuarioRepository;
use App\Services\UsuarioService;
use App\Controllers\UsuarioController;

use App\Repositories\TarefaRepository;
use App\Services\TarefaService;
use App\Controllers\TarefaController;

// -------------------------------------------------------------------------
// FUNÇÃO HELPER

function json_response($data = null, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    if ($data) {
        echo json_encode($data);
    }
    exit;
}

// -------------------------------------------------------------------------
// INJEÇÃO DE DEPENDÊNCIA (C-S-R)


// 1. Pega a conexão PDO
$pdo = Database::getConnection();

// 2. Monta as dependências de Usuario
$usuarioRepository = new UsuarioRepository($pdo);
$usuarioService = new UsuarioService($usuarioRepository);
$usuarioController = new UsuarioController($usuarioService);

// 3. Monta as dependências de Tarefa (NOVO)
$tarefaRepository = new TarefaRepository($pdo);
$tarefaService = new TarefaService($tarefaRepository, $usuarioRepository);
$tarefaController = new TarefaController($tarefaService);


// -------------------------------------------------------------------------
// CAPTURA DA REQUISIÇÃO

$method = $_SERVER['REQUEST_METHOD'];
$path_info = $_GET['path'] ?? '';
$path = explode('/', trim($path_info, '/'));
$resource = $path[0];

// -------------------------------------------------------------------------
// ROTEAMENTO


// Tratamento especial para /api/usuarios/{id}/tarefas
if ($resource === 'api' && 
    isset($path[1]) && $path[1] === 'usuarios' && 
    isset($path[2]) && is_numeric($path[2]) && // Garante que o {id} é numérico
    isset($path[3]) && $path[3] === 'tarefas') 
{
    if ($method === 'GET') {
        $usuarioId = (int)$path[2];
        $tarefaController->getByUsuarioId($usuarioId); // Chama o método do TarefaController
    } else {
        json_response(['erro' => 'Método não permitido para este endpoint.'], 405);
    }
    exit;
}


// --- ROTEADOR PRINCIPAL ---
switch ($resource) {

    // Rota: / (Apresentação)
    case '':
        if ($method === 'GET') {
            json_response(['mensagem' => 'API de Tarefas. Acesse a rota / para ver a documentação.'], 200);
        } else {
            json_response(['erro' => 'Método não permitido'], 405);
        }
        break;

    // Rota: /api/...
    case 'api':
        $apiResource = $path[1] ?? null;

        switch ($apiResource) {
            
            // Rota /api/usuarios
            case 'usuarios':
                $id = isset($path[2]) ? (int)$path[2] : null;

                if (isset($path[3])) { 
                    json_response(['erro' => 'Rota não encontrada.'], 404);
                    break;
                }

                switch ($method) {
                    case 'GET':
                        $id ? $usuarioController->getById($id) : $usuarioController->getAll();
                        break;
                    case 'POST':
                        $id ? json_response(['erro' => 'POST não permitido com ID'], 400) : $usuarioController->create();
                        break;
                    case 'PUT':
                        $id ? $usuarioController->update($id) : json_response(['erro' => 'ID não fornecido para PUT'], 400);
                        break;
                    case 'DELETE':
                        $id ? $usuarioController->delete($id) : json_response(['erro' => 'ID não fornecido para DELETE'], 400);
                        break;
                    default:
                        json_response(['erro' => 'Método não permitido'], 405);
                }
                break; // Fim do 'case usuarios'

            //  Rota /api/tarefas ---
            case 'tarefas':
                $id = isset($path[2]) ? (int)$path[2] : null;

                switch ($method) {
                    case 'GET':
                        // GET /api/tarefas/{id} ou GET /api/tarefas
                        $id ? $tarefaController->getById($id) : $tarefaController->getAll();
                        break;
                    case 'POST':
                        // POST /api/tarefas
                        $id ? json_response(['erro' => 'POST não permitido com ID'], 400) : $tarefaController->create();
                        break;
                    case 'PUT':
                        // PUT /api/tarefas/{id}
                        $id ? $tarefaController->update($id) : json_response(['erro' => 'ID não fornecido para PUT'], 400);
                        break;
                    case 'PATCH':
                        // PATCH /api/tarefas/{id}
                        $id ? $tarefaController->updatePartial($id) : json_response(['erro' => 'ID não fornecido para PATCH'], 400);
                        break;
                    case 'DELETE':
                        // DELETE /api/tarefas/{id}
                        $id ? $tarefaController->delete($id) : json_response(['erro' => 'ID não fornecido para DELETE'], 400);
                        break;
                    default:
                        json_response(['erro' => 'Método não permitido'], 405);
                }
                break; // Fim do 'case tarefas'

            default:
                json_response(['erro' => 'Recurso de API não encontrado'], 404);
                break;
        }
        break; // Fim do 'case api'

    default:
        json_response(['erro' => 'Rota não encontrada'], 404);
        break;
}
