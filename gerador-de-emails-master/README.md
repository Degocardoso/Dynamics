# Sistema de Emails Dynamics 365 🚀

Sistema completo de geração de emails profissionais com integração ao Microsoft Dynamics 365 via Power Automate, agora com **sistema de autenticação e controle de acesso por usuários**.

## 📋 Características

### Funcionalidades Principais
- ✉️ **Geração de Emails**: Editor visual com TinyMCE para criar emails profissionais
- 📊 **Visualização e Relatórios**: Visualize emails gerados e envie para o Dynamics 365
- 🔐 **Sistema de Autenticação**: Login seguro com controle de sessão
- 👥 **Gerenciamento de Usuários**: CRUD completo para administradores
- 🔒 **Controle de Acesso**: Três níveis de permissão diferentes

### Níveis de Acesso

| Nível | Permissões |
|-------|-----------|
| **Administrador** | ✅ Acesso total ao sistema<br>✅ Gerenciar usuários<br>✅ Gerar emails<br>✅ Visualizar e enviar relatórios |
| **Gerador** | ✅ Gerar emails<br>❌ Visualizar e enviar relatórios<br>❌ Gerenciar usuários |
| **Report** | ✅ Visualizar e enviar relatórios<br>❌ Gerar emails<br>❌ Gerenciar usuários |

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP 7.4+ (sem framework)
- **Banco de Dados**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **CSS Framework**: Bootstrap 5.3.3
- **Editor**: TinyMCE
- **Autenticação**: Sessões PHP com password_hash()
- **Integração**: Microsoft Dynamics 365 via Power Automate

## 📦 Requisitos do Sistema

- PHP 7.4 ou superior
- MySQL 5.7+ ou MariaDB 10.3+
- Extensões PHP necessárias:
  - PDO
  - pdo_mysql
  - mbstring
- Servidor web (Apache, Nginx, etc.)

## 🚀 Instalação

### Opção 1: Instalação Automática (Recomendado)

1. **Clone o repositório**
   ```bash
   git clone https://github.com/seu-usuario/dynamics-emails.git
   cd dynamics-emails
   ```

2. **Configure seu servidor web**
   - Aponte o DocumentRoot para a pasta do projeto
   - Certifique-se de que o MySQL está rodando

3. **Execute o instalador**
   - Acesse: `http://localhost/setup/install.php`
   - Preencha os dados de conexão do MySQL
   - Clique em "Instalar Sistema"

4. **Pronto!** 🎉
   - Acesse: `http://localhost/login.php`
   - Use as credenciais padrão:
     - **Usuário**: `admin`
     - **Senha**: `admin123`

⚠️ **IMPORTANTE**: Altere a senha do administrador imediatamente após o primeiro login!

### Opção 2: Instalação Manual

1. **Clone o repositório**
   ```bash
   git clone https://github.com/seu-usuario/dynamics-emails.git
   cd dynamics-emails
   ```

2. **Configure o banco de dados**
   ```bash
   mysql -u root -p < setup/install.sql
   ```

3. **Configure as credenciais**
   - Edite o arquivo `config/database.php`
   - Ajuste as constantes de conexão:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'dynamics_emails');
     define('DB_USER', 'root');
     define('DB_PASS', 'sua_senha');
     ```

4. **Acesse o sistema**
   - URL: `http://localhost/login.php`
   - Usuário: `admin`
   - Senha: `admin123`

## 📖 Como Usar

### 1. Primeiro Acesso

1. Acesse `http://localhost/login.php`
2. Faça login com as credenciais padrão
3. **IMPORTANTE**: Vá em "Gerenciar Usuários" e altere sua senha

### 2. Criando Usuários (Admin)

1. Acesse o Dashboard
2. Clique em "Gerenciar Usuários"
3. Clique em "Novo Usuário"
4. Preencha os dados:
   - Username (login)
   - Senha
   - Nome completo
   - Email
   - Nível de acesso
5. Clique em "Criar Usuário"

### 3. Gerando Emails (Admin ou Gerador)

1. No Dashboard, clique em "Criar Novo Email"
2. Escolha o modelo de email
3. Preencha os campos:
   - Nome do arquivo
   - Diretório de destino
   - Conteúdo (use o editor visual)
   - Adicione botões, tabelas e imagens conforme necessário
4. Clique em "Gerar Email Agora!"

### 4. Visualizando e Enviando (Admin ou Report)

1. No Dashboard, clique em "Visualizar Emails"
2. Selecione o email gerado
3. Clique em "Enviar para o Dynamics 365"
4. Preencha os dados da campanha:
   - Nome da campanha
   - Assunto do email
   - Data de envio
5. Confirme o envio

## 🔧 Configuração do Power Automate

Para integrar com o Dynamics 365:

1. Acesse o Power Automate
2. Crie um novo fluxo com gatilho "Quando uma solicitação HTTP é recebida"
3. Configure as ações desejadas no Dynamics 365
4. Copie a URL do webhook gerada
5. Edite o arquivo `core/enviarParaDynamics.php`
6. Atualize a constante `$webhookUrl` com sua URL

## 🗂️ Estrutura de Diretórios

```
dynamics-emails/
├── admin/
│   └── usuarios.php          # Gerenciamento de usuários
├── auth/
│   └── session.php            # Sistema de autenticação
├── config/
│   └── database.php           # Configuração do banco de dados
├── core/
│   ├── geraEmail.php          # Geração de emails
│   └── enviarParaDynamics.php # Integração com Dynamics
├── setup/
│   ├── install.php            # Instalador automático
│   └── install.sql            # Script SQL de instalação
├── css/
├── js/
├── emails/                    # Emails gerados
├── dashboard.php              # Dashboard principal
├── login.php                  # Página de login
├── logout.php                 # Logout
├── index.php                  # Gerador de emails
└── visualizar.php             # Visualizador de emails
```

## 🔐 Segurança

- ✅ Senhas criptografadas com `password_hash()` (bcrypt)
- ✅ Proteção contra SQL Injection (prepared statements)
- ✅ Proteção XSS com `htmlspecialchars()`
- ✅ Controle de sessão com timeout automático (2 horas)
- ✅ Logs de acesso para auditoria
- ✅ Verificação de permissões em todas as páginas

### Boas Práticas de Segurança

1. Altere a senha padrão do admin imediatamente
2. Delete o arquivo `setup/install.php` após a instalação
3. Use senhas fortes para todos os usuários
4. Mantenha o PHP e MySQL atualizados
5. Configure HTTPS no servidor web

## 🐛 Solução de Problemas

### Erro: "Erro na conexão com o banco de dados"
- Verifique se o MySQL está rodando
- Confirme as credenciais em `config/database.php`
- Certifique-se de que o banco de dados existe

### Erro: "Call to undefined function password_hash()"
- Atualize o PHP para versão 7.4 ou superior

### Página em branco após login
- Verifique se as sessões estão habilitadas no PHP
- Confira as permissões das pastas

### Emails não são gerados
- Verifique as permissões da pasta `emails/`
- Certifique-se de que o servidor web pode escrever na pasta

## 📝 Changelog

### Versão 3.1 (2025-12-03)
- ✨ Adicionado sistema de autenticação completo
- ✨ Implementado gerenciamento de usuários
- ✨ Criado controle de acesso por níveis (admin, gerador, report)
- ✨ Adicionado dashboard administrativo
- ✨ Implementado sistema de logs de acesso
- ✨ Criado instalador automático
- 🔒 Proteção de todas as páginas com autenticação
- 🎨 Interface redesenhada com Bootstrap 5

### Versão 3.0
- ✨ Sistema base de geração de emails
- ✨ Integração com Power Automate
- ✨ Editor visual com TinyMCE

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

## 👥 Contribuindo

Contribuições são bem-vindas! Sinta-se à vontade para:

1. Fazer um fork do projeto
2. Criar uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abrir um Pull Request

## 💬 Suporte

Se encontrar problemas ou tiver dúvidas:

1. Verifique a seção de "Solução de Problemas"
2. Abra uma issue no GitHub
3. Entre em contato com a equipe de desenvolvimento

## ✨ Créditos

Desenvolvido com ❤️ para facilitar a criação e gerenciamento de campanhas de email no Microsoft Dynamics 365.

---

**Versão**: 3.1
**Última Atualização**: 03/12/2025
