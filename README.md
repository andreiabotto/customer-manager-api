## Manager Customer API

Uma API RESTful desenvolvida em Laravel para gerenciamento de clientes, produtos e favoritos, com autenticação via Sanctum e documentação Swagger integrada.
📋 Descrição do Projeto

O **Manager Customer** é uma aplicação backend que oferece:

    🔐 Autenticação JWT - Registro, login e gestão de sessões

    👥 Gestão de Clientes - CRUD completo de clientes

    🛍️ Catálogo de Produtos - Listagem, busca e categorização

    ❤️ Sistema de Favoritos - Adicionar/remover produtos dos favoritos

    📚 Documentação Interativa - Swagger UI para teste de endpoints

🚀 Tecnologias Utilizadas

    Laravel 11 - Framework PHP

    Sanctum - Autenticação API

    SQLite - Banco de dados (testes)

    PostgreSQL - Banco de dados (desenvolvimento)

    Docker - Containerização

    Swagger/OpenAPI - Documentação

    PHPUnit - Testes automatizados

🐳 Execução com Docker
Pré-requisitos

    - Docker

    - Docker Compose

### 1. Clone o repositório

```
git clone <seu-repositorio>
cd manager-customer-api
```

### 2. Configure as variáveis de ambiente

```
cp .env.example .env
```

### 3. Suba os containers

```
docker-compose up -d
```

### 4. Execute as migrações e seeders

```
docker-compose exec app php artisan migrate --seed
```

### 5. Gere a chave da aplicação

```
docker-compose exec app php artisan key:generate
```

### 6. Gere a documentação Swagger

```
docker-compose exec app php artisan l5-swagger:generate
```

## 🛠️ Comandos Úteis

### Desenvolvimento

```
# Acessar o container da aplicação
docker-compose exec app bash

# Executar migrações
docker-compose exec app php artisan migrate

# Popular banco de dados
docker-compose exec app php artisan db:seed

# Limpar cache
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
```

### Testes

```
# Executar todos os testes
docker-compose exec app php artisan test
```

### Manutenção

```
# Parar os containers
docker-compose down

# Reconstruir containers
docker-compose up -d --build

# Ver logs da aplicação
docker-compose logs app

# Ver logs do servidor web
docker-compose logs nginx
```


## 📊 Acessando a Aplicação

### 🌐 Endpoints da API

    API Base URL: http://localhost:8000/api

    Swagger UI: http://localhost:8000/api/docs

###  🔐 Fluxo de Autenticação

    Registrar usuário: POST /api/auth/register

    Login: POST /api/auth/login

    Usar token: Incluir Authorization: Bearer {token} nos headers

## 📋 Endpoints Principais

**Autenticação**

    POST /api/auth/register - Registrar novo usuário

    POST /api/auth/login - Fazer login

    POST /api/auth/logout - Fazer logout

    GET /api/auth/me - Obter dados do usuário logado

**Clientes**

    GET /api/customer/ - Perfil do cliente

    PUT /api/customer - Atualizar perfil

    GET /api/customer/all - Listar todos os clientes (admin)

    DELETE /api/customer/{id} - Excluir cliente

**Favoritos**

    POST /api/favorites - Adicionar produto aos favoritos

    DELETE /api/favorites/{id} - Remover dos favoritos

    GET /api/favorites/ - Listar favoritos

    GET /api/favorites/check/{id} - Verificar se produto está nos favoritos