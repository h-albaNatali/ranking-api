# 📊 API de Ranking - Slim Framework 4

Esta é uma API RESTful construída com **PHP** e **Slim Framework 4** para gerenciar rankings de movimentos e usuários, utilizando **MySQL** como banco de dados.

## 📌 Tecnologias Utilizadas
- **PHP 8+**
- **Slim Framework 4 (Microframework)**
- **MySQL**
- **Apache (XAMPP)**
- **Composer**
- **JWT (JSON Web Token) para Autenticação**
- **Dotenv para Variáveis de Ambiente**

---

## 🚀 Instalação e Configuração

### 📌 **1. Clonar o Repositório**
```sh
git clone git@github.com:h-albaNatali/ranking-api.git
cd ranking-api
```

### 📌 **2. Instalar Dependências**
Certifique-se de que o **Composer** está instalado e execute:
```sh
composer install
```

### 📌 **3. Configurar Banco de Dados**
- Acesse o **phpMyAdmin**:  
  👉 `http://localhost:8080/phpmyadmin/`
- Crie o banco de dados:
```sql
CREATE DATABASE ranking_db;
```
- Execute as tabelas no **SQL**:
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
    value FLOAT NOT NULL,
    date DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (movement_id) REFERENCES movement(id)
);
```

### 📌 **4. Configurar Variáveis de Ambiente**

Crie um arquivo **`.env`** na raiz do projeto e adicione as configurações do banco de dados:

```ini
DB_HOST=localhost
DB_NAME=ranking_db
DB_USER=root
DB_PASS=senha_segura
JWT_SECRET=chave_secreta_super_segura
```

⚠️ **Importante**: Nunca suba o arquivo `.env` para o Git. Adicione a linha abaixo no `.gitignore`:
```sh
.env
```

### 📌 **5. Iniciar o Servidor**
```sh
php -S localhost:8080 -t public
```
Agora, a API estará disponível em:
👉 `http://localhost:8080/ranking-api/public/ranking/`

---

## 📌 **Endpoints Disponíveis**

### 📌 **1. Autenticação**
**`POST /login`**

📌 Gera um token JWT para acesso aos endpoints protegidos.

🔹 **Exemplo de Request:**
```sh
POST http://localhost:8080/ranking-api/public/login
```

🔹 **Exemplo de Response (`200 OK`):**
```json
{
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

---

### 📌 **2. Obter Ranking de um Movimento (Autenticado)**
**`GET /ranking/{movement_id}`**

📌 Retorna o ranking de um movimento, com **usuários ordenados por recorde**.

🔹 **Exemplo de Request:**
```sh
GET http://localhost:8080/ranking-api/public/ranking/1
Authorization: Bearer SEU_TOKEN_AQUI
```

🔹 **Exemplo de Response (`200 OK`):**
```json
{
    "movement_name": "Back Squat",
    "ranking": [
        {
            "user_name": "João",
            "personal_record": 130,
            "date": "2021-01-01 00:00:00",
            "position": 1
        },
        {
            "user_name": "José",
            "personal_record": 130,
            "date": "2021-01-01 00:00:00",
            "position": 1
        }
    ]
}
```

🟥 **Se o movimento não existir (`404 Not Found`):**
```json
{
    "error": "Movimento não encontrado."
}
```

---

## 📌 **Segurança**
✅ **Proteção contra SQL Injection**
- Todas as consultas SQL utilizam **prepared statements** e `bindParam()` para evitar injeção de código malicioso.

✅ **Autenticação JWT**
- Implementada autenticação JWT, garantindo que apenas usuários autenticados possam acessar endpoints protegidos.

✅ **Tratamento de Erros Aprimorado**
- Logs de erro são registrados para monitoramento.
- Mensagens de erro genéricas são retornadas ao cliente para evitar exposição de informações sensíveis.

✅ **Uso de Variáveis de Ambiente**
- As credenciais do banco de dados foram movidas para um arquivo **`.env`**, garantindo que informações sensíveis não fiquem hardcoded.

✅ **Melhoria de Desempenho**
- Implementação de **cache** para consultas frequentes, reduzindo carga no banco de dados.
- Indexação de colunas críticas para otimizar buscas no banco de dados.

---

## 📌 **Autor**
👨‍💻 **Henrique Alba**  
💎 Contato: [natalihenrique6@gmail.com](mailto:natalihenrique6@gmail.com)  
🔗 GitHub: [github.com/h-albaNatali](https://github.com/h-albaNatali)

