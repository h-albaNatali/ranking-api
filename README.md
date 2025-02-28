# 📊 API de Ranking - Slim Framework 4

Esta é uma API RESTful construída com **PHP** e **Slim Framework 4** para gerenciar rankings de movimentos e usuários, utilizando **MySQL** como banco de dados.

## 📌 Tecnologias Utilizadas
- **PHP 8+**
- **Slim Framework 4 (Microframework)**
- **MySQL**
- **Apache (XAMPP)**
- **Composer**

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

### 📌 **4. Configurar `.htaccess`**
Para acessar a API, adicione este arquivo `.htaccess` na **raiz do projeto**:

```apache
RewriteEngine On
RewriteBase /ranking-api/

RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ public/$1 [L]
```

### 📌 **5. Iniciar o Servidor**
```sh
php -S localhost:8080 -t public
```
Agora, a API estará disponível em:
👉 `http://localhost:8080/ranking-api/public/ranking/`

---

## 📌 **Endpoints Disponíveis**
### 📌 **1. Obter Ranking de um Movimento**
**`GET /ranking/{movement_id}`**  
📌 Retorna o ranking de um movimento, com **usuários ordenados por recorde**.

🔹 **Exemplo de Request:**
```sh
GET http://localhost:8080/ranking-api/public/ranking/1
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
    "movement_name": "Movimento não encontrado",
    "error": "Nenhum ranking encontrado para esse movimento."
}
```

---

### 📌 **2. Rota Inexistente**
Caso o usuário tente acessar uma **rota inválida**, a API retorna:
```json
{
    "error": "Nenhum retorno disponível para esta rota.",
    "requested_route": "rota-invalida"
}
```

---

## 📌 **Segurança**
✅ **Proteção contra SQL Injection**
- Utilizamos `prepare()` e `bindParam()` com `PDO::PARAM_INT`, garantindo que os inputs do usuário sejam seguros.

✅ **Tratamento de Erros**
- Se o movimento não existir, retorna `404 Not Found`.
- Se a rota for inválida, retorna um JSON informando.

---

## 📌 **Autor**
👨‍💻 **Henrique Alba**  
💎 Contato: [natalihenrique6@gmail.com](mailto:natalihenrique6@gmail.com)  
🔗 GitHub: [github.com/h-albaNatali](https://github.com/h-albaNatali)
```

