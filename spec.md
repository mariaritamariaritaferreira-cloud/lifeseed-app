# Spec final - LifeSeed

## Visão geral
LifeSeed é um sistema web que transforma objetivos pessoais em plantas virtuais. Cada objetivo nasce como broto e cresce conforme o usuário registra progresso.

## Usuários
- Usuário comum com cadastro e login
- Cada usuário possui vários objetivos

## Fluxos principais
1. Registro de usuário
2. Login com email e senha
3. Criação de objetivo
4. Escolha de categoria
5. Registro de progresso
6. Visualização do progresso em barra
7. Atualização automática do estágio da planta

## Regras de negócio
- Objetivos recebem categoria entre Saúde, Financeiro, Estudos, Trabalho e Pessoal
- Progresso é representado em porcentagem de 0 a 100
- Estágios:
  - 0-24: Broto
  - 25-49: Planta
  - 50-74: Flor
  - 75-100: Árvore
- O usuário pode registrar progresso em blocos de porcentagem
- Cada progresso pode ter uma observação opcional
- A data é armazenada automaticamente

## Segurança
- Senhas com hash seguro
- Banco acessado por variáveis de ambiente
- Sessão para autenticação
- Interação com dados por PDO

## Status da implementação
A base funcional foi entregue em PHP + MySQL com:
- autenticação
- cadastro
- criação de objetivos
- atualização de progresso
- painel com resumo e frases motivacionais

## Publicação
O projeto está pronto para ser publicado em Render ou em infraestrutura PHP + MySQL compatível, com a configuração via `render.yaml` e `Dockerfile`.
