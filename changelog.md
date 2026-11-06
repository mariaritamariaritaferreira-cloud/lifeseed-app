# Changelog

## 2026-11-06
- Criação do repositório GitHub `lifeseed-app`
- Estrutura inicial do projeto LifeSeed
- Implementação do backend em PHP com PDO
- Cadastro e login de usuários
- Gestão de objetivos e progresso
- Criação do painel principal com layout inicial
- Documentação de setup, ambiente e deploy
- Inclusão de variáveis de ambiente para não expor credenciais
- Geração do schema SQL de banco
- Adição de arquivos de deploy compatíveis com Render/Docker

## Revisões e decisões
- Uso de PHP + MySQL para cumprir a stack informada
- Separação de config e autenticação para facilitar a manutenção
- Estruturas de sessão para autenticação simples e segura
- Validação de campos básicos para cadastro e login
- Regras de estágio da planta definidas por progresso percentual
- Tema claro/escuro implementado via CSS com preferências visuais
