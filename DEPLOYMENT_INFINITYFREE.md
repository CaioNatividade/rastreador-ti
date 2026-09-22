# Publicação no InfinityFree

Este guia publica o Rastreio TI para fins acadêmicos no InfinityFree. A hospedagem deve usar HTTPS e a conta deve ser destinada apenas à demonstração do projeto.

## 1. Criar a conta e o endereço

1. Crie uma conta no InfinityFree e um site com um subdomínio gratuito.
2. Aguarde o endereço ficar ativo e acesse-o por `https://`.
3. Anote os dados de FTP exibidos pelo painel. Eles serão usados para o envio dos arquivos.

## 2. Criar o banco de dados

1. No painel da conta, abra **MySQL Databases** e crie um banco de dados.
2. Anote o host, nome do banco, usuário e senha. No InfinityFree, o nome do banco e o usuário normalmente possuem um prefixo fornecido pela plataforma.
3. Abra o phpMyAdmin pelo painel, selecione o banco recém-criado e importe `database/rastreio_ti.sql`.
4. Após a importação, entre com `admin@rastreadorti.local` / `admin123` e altere a senha em **Minha conta**. A senha inicial é somente para instalação; a atualização do código não altera as senhas existentes.

## 3. Preparar os arquivos localmente

1. Na pasta do projeto, execute `composer install --no-dev --optimize-autoloader`. Isso cria a pasta `vendor/`, que também deve ser enviada ao servidor.
2. Copie `config/database.example.php` para `config/database.local.php`.
3. Edite apenas `config/database.local.php` com os dados anotados no passo anterior. Exemplo:

   ```php
   <?php

   return [
       'host' => 'sqlXXX.infinityfree.com',
       'port' => 3306,
       'dbname' => 'epiz_XXXXXXX_rastreio_ti',
       'user' => 'epiz_XXXXXXX',
       'pass' => 'SUA_SENHA',
       'charset' => 'utf8mb4',
   ];
   ```

4. Não envie esse arquivo ao GitHub. Ele já é ignorado pelo `.gitignore`.

## 4. Enviar para o servidor

1. Abra o **File Manager** ou conecte-se por FTP.
2. Abra a pasta `htdocs/` do domínio.
3. Execute `powershell -ExecutionPolicy Bypass -File scripts/package.ps1` localmente. Envie e extraia o ZIP gerado em `dist/` dentro de `htdocs/` usando **Upload & Unzip**.
4. Na primeira instalação, envie separadamente `config/database.local.php` para `htdocs/config/`. Nas atualizações, preserve o que já está no servidor.
5. Não envie `.git`, bancos de teste ou ZIPs antigos com credenciais. Remova o ZIP do servidor após extrair.

O arquivo `.htaccess` da raiz direciona as requisições para o front controller e bloqueia acesso público ao código, às configurações e às dependências. Não mova apenas o conteúdo de `public/` para `htdocs/`: o projeto depende das demais pastas estarem no mesmo nível.

## 5. Testar antes da entrega

1. Abra `https://SEU-ENDERECO/` e confirme que a tela de login é exibida.
2. Entre como administrador, altere a senha inicial e teste criar, editar e excluir um equipamento.
3. Entre com um colaborador e confirme que não pode abrir nem executar ações administrativas.
4. Confirme que as URLs `https://SEU-ENDERECO/config/Database.php` e `https://SEU-ENDERECO/public/index.php` retornam acesso negado.
5. Confirme que o CSS está carregado e que logout, expiração de sessão e mensagens de erro funcionam.

## Atualizações futuras

Para atualizar a aplicação, faça backup do banco antes de substituir arquivos. Preserve `config/database.local.php` no servidor; ele contém credenciais e não faz parte do repositório.

Para os novos módulos, siga [docs/ATUALIZACAO_MODULOS.md](docs/ATUALIZACAO_MODULOS.md). Não reimporte o SQL inicial: esta versão usa o esquema já existente.
