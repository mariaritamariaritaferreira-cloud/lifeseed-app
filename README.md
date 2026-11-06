# LifeSeed

LifeSeed é uma aplicação web para transformar objetivos em plantas virtuais.

## Funcionalidades
- Cadastro e login de usuários
- Criação de objetivos com categoria
- Evolução visual em 4 estágios: Broto, Planta, Flor e Árvore
- Registro de progresso por dia e por tarefa
- Calendário e lembretes em estrutura pronta para extensão
- Barra de evolução, conquistas e frases motivacionais
- Tema claro/escuro

## Stack
- PHP 8+
- MySQL 8+
- HTML/CSS/JS

## Requisitos
- PHP 8.1+
- MySQL 8+
- Extensão PDO MySQL habilitada

## Configuração rápida
1. Crie um banco MySQL e importe `schema.sql`.
2. Copie `.env.example` para `.env` e ajuste as variáveis.
3. Rode em modo local:

```bash
php -S localhost:8000 -t public
```

4. Acesse http://localhost:8000

## Variáveis de ambiente

```env
APP_NAME="LifeSeed"
APP_URL="http://localhost:8000"
DB_HOST="localhost"
DB_NAME="lifeseed"
DB_USER="root"
DB_PASS=""
```

## Deploy em nuvem
O projeto está preparado para ser publicado em Render ou em qualquer provedor PHP com MySQL. Também inclui `render.yaml` e `Dockerfile` para facilitar a publicação.

## Segurança
- Não exponha credenciais no repositório
- Use variáveis de ambiente
- Senhas armazenadas com `password_hash()`
- Sessões iniciadas com medidas mínimas de segurança

## Observação
Esta entrega foi criada para funcionar como aplicação web funcional, com base em PHP + MySQL e pronta para publicação em ambientes compatíveis.
