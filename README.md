# 📌 Ranking API

## 📖 Visão Geral
A **Ranking API** é uma API REST desenvolvida para armazenar e consultar recordes pessoais dos usuários em diferentes movimentos esportivos. A API foi construída utilizando **PHP com Slim Framework**, e utiliza **JWT (JSON Web Token)** para autenticação.

Esta documentação contém todas as informações necessárias para a instalação, configuração e uso da API, bem como todas as respostas possíveis e a estrutura do banco de dados.

---

## 📌 Tecnologias Utilizadas

- **PHP 8.x**
- **Slim Framework 4**
- **Composer** (gerenciador de dependências do PHP)
- **MySQL** (Banco de dados relacional)
- **JWT** (Autenticação via token)
- **cURL/Postman** (Para testes de requisições)

---

## 🛠️ Instalação e Configuração

### 1️⃣ **Clonar o Repositório**
```sh
 git clone https://github.com/seu-repositorio/ranking-api.git
 cd ranking-api
```

### 2️⃣ **Instalar Dependências**
```sh
 composer install
```

### 3️⃣ **Configurar o Arquivo `.env`**
Copie o arquivo `.env.example` para `.env` e edite as configurações do banco de dados:
```sh
cp .env.example .env
```

Edite o arquivo `.env` com as credenciais corretas:
```
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ranking_db
DB_USERNAME=root
DB_PASSWORD=
JWT_SECRET=chave_secreta_super_segura
JWT_EXPIRATION=3600
```

### 4️⃣ **Configurar Banco de Dados**
Crie o banco de dados `ranking_db` e execute o seguinte SQL para criar as tabelas necessárias:

```sql
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE movement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE personal_record (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movement_id INT NOT NULL,
    value INT NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (movement_id) REFERENCES movement(id)
);

CREATE TABLE user_api (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);
```

### 5️⃣ **Rodar o Servidor**
```sh
php -S localhost:8080 -t public
```

---

## 📌 Endpoints Disponíveis

### 🔹 **Registro de Usuário**
**Rota:** `POST /register`
```json
{
  "name": "João Silva",
  "email": "joao@email.com",
  "password": "senha123"
}
```
**Respostas:**
✅ `201 Created` → `{"message": "Usuário registrado com sucesso."}`
❌ `400 Bad Request` → `{"error": "Email já cadastrado."}`

---
### 🔹 **Login e Obtenção de Token**
**Rota:** `POST /login`
```json
{
  "email": "joao@email.com",
  "password": "senha123"
}
```
**Respostas:**
✅ `200 OK` → `{"token": "seu-token-jwt"}`
❌ `401 Unauthorized` → `{"error": "Credenciais inválidas."}`

---
### 🔹 **Consultar Ranking de um Movimento** (Requer Token JWT)
**Rota:** `GET /ranking/{movement_id}`
**Cabeçalho:**
```sh
-H "Authorization: Bearer seu-token-jwt"
```

**Respostas:**
✅ `200 OK`
```json
[
  {"name": "José", "movement_id": 1, "score": 190, "date": "2021-01-06"},
  {"name": "João", "movement_id": 1, "score": 180, "date": "2021-01-02"}
]
```
❌ `401 Unauthorized` → `{"error": "Acesso não autorizado."}`
❌ `404 Not Found` → `{"error": "Movimento não encontrado."}`
❌ `500 Internal Server Error` → `{"error": "Erro ao buscar ranking."}`

---

## 🚀 Testando a API com `cURL`

### **📌 Criar Usuário**
```sh
curl -X POST "http://localhost:8080/register" -H "Content-Type: application/json" -d "{ \"name\": \"João Silva\", \"email\": \"joao@email.com\", \"password\": \"senha123\" }"
```

### **📌 Fazer Login**
```sh
curl -X POST "http://localhost:8080/login" -H "Content-Type: application/json" -d "{ \"email\": \"joao@email.com\", \"password\": \"senha123\" }"
```

### **📌 Consultar Ranking**
```sh
curl -X GET "http://localhost:8080/ranking/1" -H "Authorization: Bearer SEU_TOKEN"
```

---

## ❌ Possíveis Erros e Soluções

| **Erro** | **Causa** | **Solução** |
|----------|----------|-------------|
| `{"error": "Email já cadastrado."}` | O email já existe no banco. | Tente outro email ou faça login. |
| `{"error": "Credenciais inválidas."}` | Email ou senha errados. | Verifique os dados e tente novamente. |
| `{"error": "Acesso não autorizado."}` | Token JWT inválido ou ausente. | Faça login e passe o token corretamente. |
| `404 Not Found` | URL incorreta. | Verifique a URL e tente novamente. |
| `500 Internal Server Error` | Erro na API. | Verifique os logs do servidor. |

---

## 📌 Conclusão
Agora você tem a **Ranking API** totalmente configurada! 🚀

Se precisar de suporte, entre em contato com o responsável pelo projeto. Boa codificação! 🔥


---

## 📌 **Autor**
👨‍💻 **Henrique Alba**  
💎 Contato: [natalihenrique6@gmail.com](mailto:natalihenrique6@gmail.com)  
🔗 GitHub: [github.com/h-albaNatali](https://github.com/h-albaNatali)

