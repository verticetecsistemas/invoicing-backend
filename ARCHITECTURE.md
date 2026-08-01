# Arquitetura SOLID - Padrão de Projeto

## Estrutura Implementada

Esta aplicação segue os princípios SOLID com uma arquitetura em camadas, separando responsabilidades e facilitando manutenção e testes.

### Camadas da Arquitetura

```
┌─────────────────┐
│   Controller    │  → Recebe requisições HTTP
└────────┬────────┘
         │
┌────────▼────────┐
│    Service      │  → Lógica de negócio e validações
└────────┬────────┘
         │
┌────────▼────────┐
│   Repository    │  → Acesso aos dados (Model)
└────────┬────────┘
         │
┌────────▼────────┐
│     Model       │  → Eloquent ORM
└─────────────────┘
```

### 1. **Repository Pattern** (`app/Repositories/`)

Responsável por abstrair a lógica de acesso aos dados.

#### Interface (`Contracts/UserRepositoryInterface.php`)
- Define o contrato dos métodos que o repositório deve implementar
- Permite trocar a implementação sem afetar outras camadas
- Princípio: **Dependency Inversion Principle (DIP)**

#### Implementação (`UserRepository.php`)
- Implementa a interface definida
- Contém apenas operações de banco de dados
- Não contém lógica de negócio
- Princípio: **Single Responsibility Principle (SRP)**

**Métodos disponíveis:**
- `create(array $data): User`
- `findById(int $id): ?User`
- `findByEmail(string $email): ?User`
- `all(): Collection`
- `update(int $id, array $data): bool`
- `delete(int $id): bool`

### 2. **Service Layer** (`app/Services/`)

Responsável pela lógica de negócio da aplicação.

#### UserService (`UserService.php`)
- Contém a lógica de negócio
- Realiza validações
- Coordena operações entre múltiplos repositórios (se necessário)
- Não conhece como os dados são persistidos
- Princípios: **SRP** e **DIP**

**Métodos disponíveis:**
- `createUser(array $data): User`
- `getUserById(int $id): ?User`
- `getUserByEmail(string $email): ?User`
- `getAllUsers(): Collection`
- `updateUser(int $id, array $data): bool`
- `deleteUser(int $id): bool`

### 3. **Controller Layer** (`app/Http/Controllers/`)

Responsável por lidar com requisições HTTP.

#### UserController (`UserController.php`)
- Recebe requisições HTTP
- Delega lógica de negócio para o Service
- Formata respostas JSON
- Trata exceções
- Princípios: **SRP** e **DIP**

**Endpoints disponíveis:**
- `POST /api/users/create` - Criar usuário
- `GET /api/users/index` - Listar todos os usuários
- `GET /api/users/{id}` - Buscar usuário por ID
- `PUT /api/users/{id}` - Atualizar usuário
- `DELETE /api/users/{id}` - Deletar usuário

### 4. **Dependency Injection** (`app/Providers/AppServiceProvider.php`)

O Laravel faz a injeção de dependências automaticamente através do Service Container.

```php
$this->app->bind(UserRepositoryInterface::class, UserRepository::class);
```

Isso permite que:
- O Service receba automaticamente uma instância do Repository
- O Controller receba automaticamente uma instância do Service
- Possamos trocar implementações facilmente
- Facilite a criação de testes unitários

## Princípios SOLID Aplicados

### 1. **Single Responsibility Principle (SRP)**
Cada classe tem uma única responsabilidade:
- **Repository**: Acesso aos dados
- **Service**: Lógica de negócio
- **Controller**: Manipulação de requisições HTTP

### 2. **Open/Closed Principle (OCP)**
As classes estão abertas para extensão, mas fechadas para modificação:
- Podemos adicionar novos repositórios sem modificar os existentes
- Podemos adicionar novos services sem afetar outros

### 3. **Liskov Substitution Principle (LSP)**
Qualquer implementação de `UserRepositoryInterface` pode substituir outra:
- Podemos criar um `CacheUserRepository` ou `ApiUserRepository`
- O Service continuará funcionando sem mudanças

### 4. **Interface Segregation Principle (ISP)**
Interfaces específicas e coesas:
- `UserRepositoryInterface` contém apenas métodos relacionados a User
- Evita interfaces "gordas" com métodos não utilizados

### 5. **Dependency Inversion Principle (DIP)**
Dependemos de abstrações (interfaces), não de implementações concretas:
- `UserService` depende de `UserRepositoryInterface`, não de `UserRepository`
- `UserController` depende de `UserService`, não de detalhes de implementação

## Como Usar

### Criar um novo usuário

```bash
POST /api/users/create
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "senha123",
  "tipo": "admin"
}
```

### Listar usuários

```bash
GET /api/users/index
Authorization: Bearer {token}
```

### Buscar usuário

```bash
GET /api/users/{id}
Authorization: Bearer {token}
```

### Atualizar usuário

```bash
PUT /api/users/{id}
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "João Silva Atualizado",
  "email": "joao.novo@example.com"
}
```

### Deletar usuário

```bash
DELETE /api/users/{id}
Authorization: Bearer {token}
```

## Extensibilidade

Para adicionar novas funcionalidades (ex: Posts):

1. **Criar Interface do Repository**
   ```php
   // app/Repositories/Contracts/PostRepositoryInterface.php
   ```

2. **Implementar Repository**
   ```php
   // app/Repositories/PostRepository.php
   ```

3. **Criar Service**
   ```php
   // app/Services/PostService.php
   ```

4. **Criar Controller**
   ```php
   // app/Http/Controllers/PostController.php
   ```

5. **Registrar no ServiceProvider**
   ```php
   $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
   ```

6. **Adicionar rotas**
   ```php
   // routes/api.php
   ```

## Benefícios

✅ **Testabilidade**: Fácil criar mocks e testes unitários  
✅ **Manutenibilidade**: Código organizado e fácil de manter  
✅ **Reutilização**: Services e Repositories podem ser reutilizados  
✅ **Escalabilidade**: Fácil adicionar novas funcionalidades  
✅ **Separação de responsabilidades**: Cada camada tem sua função específica  
✅ **Flexibilidade**: Fácil trocar implementações (ex: mudar de BD)
