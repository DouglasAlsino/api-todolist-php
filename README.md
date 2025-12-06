

---

# API de Tarefas (ToDo List) - Backend 2

Projeto de uma API RESTful desenvolvida em **PHP puro** (sem frameworks), utilizando o padrão de arquitetura **Controller-Service-Repository** e banco de dados **MySQL via PDO**.

---

## 🚀 Tecnologias Utilizadas

* **Linguagem:** PHP 7.4+ (ou 8.x)
* **Banco de Dados:** MySQL / MariaDB
* **Acesso a Dados:** PDO (PHP Data Objects)
* **Servidor:** Apache (via WAMP/XAMPP) com `mod_rewrite` ativado
* **Gerenciador de Dependências:** Composer (utilizado apenas para Autoload PSR-4)

---

## ⚙️ Instalação e Configuração

### 1. Clonar ou Baixar o Projeto

Clone este repositório ou baixe os arquivos para a pasta pública do seu servidor web (ex.: `www` do WampServer ou `htdocs` do XAMPP).

### 2. Instalar Dependências

Na raiz do projeto, execute:

composer install

### 3. Configuração do Banco de Dados

1. Crie um banco de dados MySQL chamado **todolist_db** (ou outro nome de sua preferência).
2. Configure as credenciais (`host`, `dbname`, `user`, `password`) no arquivo:
   **src/Config/Database.php**
3. Utilize o conteúdo do arquivo **setup.db** (na raiz do projeto) para criar as tabelas **usuarios** e **tarefas**, e inserir os dados iniciais.

---

## 🧪 Como Testar a API

O projeto inclui um arquivo chamado **teste.http**, configurado para uso com a extensão **REST Client** do Visual Studio Code.

1. Instale a extensão *REST Client* no VS Code.
2. Abra o arquivo `teste.http`.
3. Certifique-se de que o servidor está rodando (ex.: WAMP/XAMPP).
4. Clique em **Send Request** acima de cada requisição para testar as rotas.

---

## 📂 Arquitetura (Controller-Service-Repository)

O projeto segue rigorosamente a separação de responsabilidades:

### 📁 src/Config

Configuração da conexão com o banco de dados (Padrão Singleton).

### 📁 src/Controllers

Recebe as requisições HTTP, valida entradas básicas e retorna respostas em JSON.

### 📁 src/Services

Contém regras de negócio e validações (ex.: verificar se o usuário existe antes de criar uma tarefa).

### 📁 src/Repositories

Comunicação direta com o banco de dados (SQL).

### 📄 index.php

Atua como *Front Controller* e roteador, despachando requisições para os controladores apropriados.

---



