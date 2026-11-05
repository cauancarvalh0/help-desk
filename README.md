<div align="center">

# HelpDesk

**Sistema de controle de chamados de suporte, com perfis de acesso e autenticação segura, desenvolvido em PHP puro.**

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-PDO-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=flat-square&logo=javascript&logoColor=black)](https://developer.mozilla.org/docs/Web/JavaScript)
[![Git](https://img.shields.io/badge/Git-GitHub-181717?style=flat-square&logo=github&logoColor=white)](https://github.com/)

[Acessar a demonstração online](https://help-desk.infinityfree.io)

</div>

---

## Sumário

1. [Resumo executivo](#resumo-executivo)
2. [Demonstração](#demonstração)
3. [Funcionalidades](#funcionalidades)
4. [Perfis de acesso](#perfis-de-acesso)
5. [Segurança](#segurança)
6. [Tecnologias](#tecnologias)
7. [Estrutura do projeto](#estrutura-do-projeto)
8. [Modelo de dados](#modelo-de-dados)
9. [Instalação e execução local](#instalação-e-execução-local)
10. [Como criar o primeiro atendente](#como-criar-o-primeiro-atendente)
11. [Publicação na InfinityFree](#publicação-na-infinityfree)
12. [Limitações conhecidas](#limitações-conhecidas)
13. [Próximos passos](#próximos-passos)
14. [Autor](#autor)

---

## Resumo executivo

O **HelpDesk** organiza o atendimento de suporte em um único lugar. Usuários abrem chamados descrevendo o problema, e atendentes assumem, respondem e encerram cada solicitação, com histórico de comentários e indicadores por status.

O projeto foi construído sem frameworks, para demonstrar domínio dos fundamentos do desenvolvimento back-end: autenticação, sessões, controle de acesso por perfil, banco de dados relacional com PDO, validação de dados e práticas de segurança.

**Problemas que o sistema resolve**

- Centraliza as solicitações de suporte, que antes ficariam espalhadas em mensagens e e-mails.
- Mostra, em cartões, quantos chamados estão abertos, em andamento e resolvidos.
- Garante que cada pessoa veja apenas o que lhe cabe: o usuário vê os próprios chamados, e o atendente vê todos.
- Mantém o histórico da conversa em cada chamado, com autor e data.

## Demonstração

| Item | Informação |
|---|---|
| Aplicação online |(https://help-desk.infinityfree.io) |
| Hospedagem | InfinityFree, com HTTPS ativo |
| Acesso | Cadastro livre. Use a opção **Cadastre-se** para criar uma conta de usuário de teste. |

## Funcionalidades

- Cadastro, login e logout, com bloqueio temporário após tentativas incorretas.
- Painel com cartões de resumo (abertos, em andamento, resolvidos e total) e lista de chamados.
- Abertura de chamado com título, descrição, categoria (dúvida, problema ou solicitação) e prioridade (baixa, média ou alta).
- Filtros combinados por status e por prioridade.
- Página de detalhe com descrição, dados do chamado e histórico de comentários.
- Comentários do solicitante e dos atendentes, com destaque visual para as respostas do atendimento.
- Atendente assume o chamado, que passa automaticamente para "em andamento".
- Atendente altera o status: aberto, em andamento, resolvido ou fechado.
- Chamados fechados não recebem novos comentários.
- Mensagens de sucesso e de erro após cada ação.
- Interface corporativa em tons de azul, branco e cinza, responsiva e pensada primeiro para o celular.

## Perfis de acesso

| Ação | Usuário | Atendente |
|---|:---:|:---:|
| Abrir chamado | Sim | Sim |
| Ver chamados | Somente os que abriu | Todos |
| Comentar | Nos próprios chamados | Em qualquer chamado |
| Assumir chamado | Não | Sim |
| Alterar status | Não | Sim |

O perfil é conferido no servidor em cada ação. Esconder um botão na tela não é a proteção: mesmo um envio manual de formulário por um usuário comum é recusado.

## Segurança

| Ameaça | Proteção aplicada |
|---|---|
| SQL Injection | Todas as consultas usam prepared statements do PDO |
| Exposição de senhas | `password_hash()` no cadastro e `password_verify()` no login; nenhuma senha é salva em texto puro |
| Session fixation | `session_regenerate_id(true)` a cada login |
| Roubo de sessão | Cookie com `HttpOnly`, `SameSite=Lax` e `Secure` quando o site usa HTTPS |
| CSRF | Token único por sessão em todos os formulários POST, validado com `hash_equals()` |
| XSS | Toda saída dinâmica é escapada com `htmlspecialchars()` |
| Acesso indevido a chamados | Consultas filtram pelo `user_id` da sessão; para o usuário, um chamado alheio é tratado como "não encontrado" |
| Elevação de privilégio | O perfil vem do banco e fica na sessão; o cadastro sempre cria o perfil `usuario`, mesmo que o formulário envie outro valor |
| Falsificação de autoria | `user_id` e `atendente_id` vêm da sessão, nunca de campos do formulário |
| Tentativas de login em excesso | Após 3 senhas incorretas, o login é bloqueado por 5 minutos (contador na sessão) |
| Enumeração de contas | Mensagem de login genérica, que não revela se o e-mail existe |
| Vazamento de credenciais | O arquivo `config/database.php` fica fora do repositório por meio do `.gitignore` |

A validação dos formulários ocorre no navegador e, novamente, no servidor.

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Back-end | PHP 8+ (sem frameworks) |
| Banco de dados | MySQL com PDO |
| Front-end | HTML5, CSS3 (variáveis CSS) e Bootstrap 5.3 |
| Interatividade | JavaScript puro (validação dos formulários e botão de mostrar a senha) |
| Ícones e fonte | Bootstrap Icons e Open Sans |
| Versionamento | Git e GitHub |
| Hospedagem | InfinityFree |

## Estrutura do projeto

```
helpdesk/
├── assets/
│   ├── css/style.css            # Tema visual do projeto
│   └── js/script.js             # Validação dos formulários e botão de senha
├── config/
│   └── database.example.php     # Modelo da conexão (copie para database.php)
├── includes/
│   ├── auth.php                 # Sessão, CSRF, mensagens, perfis e proteção de rotas
│   ├── header.php               # Topo, faixa azul e navegação
│   └── footer.php               # Rodapé e scripts
├── tickets/
│   ├── create.php               # Abrir chamado
│   ├── view.php                 # Detalhe, comentários e ações
│   ├── comment.php              # Enviar comentário
│   ├── status.php               # Alterar status (atendente)
│   └── assign.php               # Assumir chamado (atendente)
├── dashboard.php                # Painel com resumo, filtros e lista
├── login.php                    # Login
├── register.php                 # Cadastro
├── logout.php                   # Encerrar sessão
├── index.php                    # Redireciona para login ou painel
├── database.sql                 # Estrutura das tabelas
├── colinha-entrevista.md        # Conceitos do projeto explicados de forma simples
└── README.md
```

## Modelo de dados

```
usuarios (1) ───────< (N) chamados (1) ───────< (N) comentarios
  id (PK)                  id (PK)                   id (PK)
  nome                     user_id (FK, CASCADE)     chamado_id (FK, CASCADE)
  email (UNIQUE)           atendente_id (FK, SET NULL)  user_id (FK, CASCADE)
  senha_hash               titulo                    mensagem
  perfil (usuario |        descricao                 data_criacao
          atendente)       categoria (duvida | problema | solicitacao)
  data_criacao             prioridade (baixa | media | alta)
                           status (aberto | em_andamento | resolvido | fechado)
                           data_criacao
```

## Instalação e execução local

### Pré-requisitos

- [XAMPP](https://www.apachefriends.org/) (ou Laragon/WAMP), com PHP 8+, Apache e MySQL
- [Git](https://git-scm.com/)

### Passo a passo

**1. Clone o repositório** dentro da pasta `htdocs` do XAMPP:

```bash
cd C:/xampp/htdocs
git clone https://github.com/cauancarvalh0/helpdesk.git
```

**2. Inicie o Apache e o MySQL** pelo painel do XAMPP.

**3. Crie o banco de dados.** Acesse `http://localhost/phpmyadmin`, clique em **Novo** e crie um banco chamado `db_chamados`, com collation `utf8mb4_unicode_ci`.

**4. Importe as tabelas.** Selecione o banco `db_chamados`, abra a aba **Importar** e envie o arquivo `database.sql`.

**5. Configure a conexão.** Copie o arquivo de exemplo e preencha com os seus dados:

```bash
cp config/database.example.php config/database.php
```

No XAMPP, os valores padrão costumam ser:

```php
$host    = 'localhost';
$banco   = 'db_chamados';
$usuario = 'root';
$senha   = '';
```

**6. Acesse** `http://localhost/helpdesk` e crie sua conta.

> **Importante:** o arquivo `config/database.php` contém credenciais e nunca deve ser enviado ao GitHub. Ele já está protegido pelo `.gitignore`.

## Como criar o primeiro atendente

Por segurança, todo cadastro cria um usuário comum, e o sistema não traz nenhum atendente com senha pronta. Para criar o primeiro:

1. Cadastre uma conta normalmente pela tela **Cadastre-se**.
2. No phpMyAdmin, abra o banco do projeto, vá na aba **SQL** e execute, trocando pelo e-mail cadastrado:

```sql
UPDATE usuarios SET perfil = 'atendente' WHERE email = 'seu-email@exemplo.com';
```

3. Saia do sistema e entre de novo. O perfil é lido no login, então a mudança só vale na próxima sessão.

## Publicação na InfinityFree

1. Crie um novo site na InfinityFree para o HelpDesk (de preferência em um subdomínio próprio). Dois sistemas no mesmo endereço compartilhariam o cookie de sessão.
2. Em **MySQL Databases**, crie um banco e anote host, nome, usuário e senha.
3. No phpMyAdmin desse banco, importe o arquivo `database.sql`.
4. Envie os arquivos do projeto para a pasta `htdocs` da hospedagem.
5. Crie `htdocs/config/database.php` com os dados do passo 2.
6. Remova o arquivo de boas-vindas da hospedagem (`index2.html`), caso exista.
7. Para forçar HTTPS, crie um arquivo `.htaccess` dentro de `htdocs` com o conteúdo:

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## Limitações conhecidas

- O bloqueio de tentativas de login usa a sessão do visitante. Quem apaga os cookies zera a contagem.
- Os horários exibidos seguem o fuso do servidor do banco de dados.
- A lista de chamados não tem paginação.
- Apenas atendentes alteram o status, inclusive para fechar um chamado.

## Próximos passos

- [ ] Limite de tentativas de login por e-mail ou IP, salvo no banco
- [ ] Paginação e busca por texto na lista de chamados
- [ ] Notificação por e-mail a cada resposta
- [ ] Anexos nos chamados
- [ ] Tela de administração para gerenciar perfis
- [ ] Testes automatizados

## Autor

**Cauan Carvalho**
Desenvolvedor web em formação, Caxias, MA

- GitHub: [github.com/cauancarvalh0](https://github.com/cauancarvalh0)
- LinkedIn: [linkedin.com/in/cauancarvalh0](https://www.linkedin.com/in/cauancarvalh0)
- Portfólio: [cauancarvalho.vercel.app](https://cauancarvalho.vercel.app)
- E-mail: [cauancarvalho004@gmail.com](mailto:cauancarvalho004@gmail.com)

---

Projeto desenvolvido para fins de estudo e portfólio.
