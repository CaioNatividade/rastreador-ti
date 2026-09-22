# Rastreio TI

Sistema web para controle de ativos de tecnologia em ambientes corporativos, desenvolvido em PHP nativo com arquitetura MVC e banco de dados MySQL/MariaDB.

O projeto faz parte da disciplina **Projeto e Implementação de Sistemas para Web II**, do curso de Análise e Desenvolvimento de Sistemas da UNIVASF.

## Sobre o projeto

O Rastreio TI centraliza o inventário e o ciclo de vida de equipamentos como notebooks, monitores, periféricos e servidores. O sistema registra ativos e integra sua disponibilidade aos empréstimos, devoluções e manutenções.

### Público-alvo

- **Administradores e técnicos de TI:** responsáveis pelo inventário e pelas movimentações dos ativos.
- **Colaboradores:** usuários que poderão consultar os equipamentos sob sua responsabilidade.

## Estado atual — módulos da entrega final

Funcionalidades implementadas e testadas:

- estrutura MVC nativa;
- Front Controller e sistema de rotas;
- suporte a rotas GET e POST;
- autoload PSR-4 com Composer;
- conexão com MySQL/MariaDB por PDO;
- cadastro de equipamentos (Create);
- listagem de equipamentos com suas categorias (Read);
- edição de equipamentos e de seu status (Update);
- exclusão de equipamentos com confirmação (Delete);
- validação dos dados do cadastro;
- validação dos dados da edição;
- bloqueio de número de série duplicado;
- mensagens de sucesso e erro nas operações;
- consultas preparadas;
- autenticação por e-mail e senha;
- sessões, proteção das rotas internas e logout;
- dashboard com indicadores reais do inventário;
- tratamento de erros HTTP 404, 405, 422 e 500.

### Módulos integrados

- cadastro, edição e exclusão de categorias sem vínculos;
- cadastro e edição de usuários, redefinição de senha e desativação;
- empréstimos, devoluções e identificação de atrasos;
- abertura e conclusão de manutenções com custo e histórico;
- sincronização transacional do status dos equipamentos;
- consulta dos próprios empréstimos e equipamentos pelo colaborador;
- troca de senha em Minha conta e revogação das demais sessões;
- termo de responsabilidade imprimível (assinatura manual);
- inventário filtrado, impressão/PDF pelo navegador e exportação CSV;
- proteção CSRF e autorização por perfil no servidor.

Veja as regras, os testes e o roteiro de atualização em [docs/ATUALIZACAO_MODULOS.md](docs/ATUALIZACAO_MODULOS.md). A versão acadêmica não inclui assinatura eletrônica, recuperação de senha por e-mail nem notificações automáticas.

## Requisitos

- PHP 8.1 ou superior;
- extensões `pdo_mysql` e `mbstring` habilitadas;
- MySQL ou MariaDB;
- Apache com `mod_rewrite` habilitado;
- Composer.

O projeto foi desenvolvido e testado localmente com XAMPP, PHP 8.2 e MariaDB.

## Instalação

1. Clone o repositório dentro do diretório servido pelo Apache:

   ```bash
   git clone https://github.com/CaioNatividade/rastreador-ti.git
   cd rastreador-ti
   ```

2. Instale o autoload do Composer:

   ```bash
   composer install
   ```

3. Inicie o Apache e o MySQL/MariaDB.

4. Crie ou selecione o banco que será usado pela aplicação. Depois, importe [`database/rastreio_ti.sql`](database/rastreio_ti.sql) nesse banco pelo phpMyAdmin ou pelo cliente do MySQL usando o conjunto de caracteres `utf8mb4`. Em hospedagens compartilhadas, use o banco fornecido pelo provedor.

5. Para desenvolvimento local, copie [`config/database.example.php`](config/database.example.php) como `config/database.local.php` e ajuste as credenciais. Esse arquivo local é ignorado pelo Git. A configuração padrão é:

   ```text
   host: localhost
   banco: rastreio_ti
   usuário: root
   senha: vazia
   ```

6. Acesse a aplicação. Considerando a pasta `rastreio-ti` dentro do `htdocs` do XAMPP:

   ```text
   http://localhost/rastreio-ti/login
   ```

> Em produção, prefira configurar `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` e `DB_CHARSET` no ambiente da hospedagem. As variáveis de ambiente prevalecem sobre o arquivo local e as credenciais nunca devem ser versionadas.

## Rotas implementadas

| Método | Rota | Descrição |
|---|---|---|
| GET | `/login` | Formulário de autenticação |
| POST | `/login/autenticar` | Processamento da autenticação |
| POST | `/logout` | Encerramento da sessão |
| GET | `/home` | Dashboard com indicadores reais |
| GET | `/home/equipamentos` | Listagem de equipamentos |
| GET | `/home/equipamentos/novo` | Formulário de cadastro |
| GET | `/home/equipamentos/editar?id={id}` | Formulário de edição |
| POST | `/home/equipamentos` | Processamento do cadastro |
| POST | `/home/equipamentos/atualizar` | Processamento da edição |
| POST | `/home/equipamentos/excluir` | Exclusão de equipamento |
| GET / POST | `/home/emprestimos` | Listar empréstimos / registrar entrega |
| POST | `/home/emprestimos/devolver` | Registrar devolução |
| GET | `/home/emprestimos/termo?id={id}` | Termo para impressão |
| GET | `/home/categorias` | Listar e editar categorias |
| POST | `/home/categorias/salvar`, `/home/categorias/excluir` | Gerenciar categorias |
| GET / POST | `/home/manutencoes` | Listar manutenções / abrir ficha |
| POST | `/home/manutencoes/concluir` | Concluir ficha e atualizar status |
| GET | `/home/usuarios` | Listar e editar usuários |
| POST | `/home/usuarios/salvar` | Cadastrar, editar ou desativar usuário |
| GET / POST | `/home/conta`, `/home/conta/senha` | Formulário / troca da própria senha |
| GET | `/home/relatorios`, `/home/relatorios/csv` | Relatório e exportação |

As rotas usam o front controller na raiz; `public/` é usado internamente. Por exemplo:

```text
http://localhost/rastreio-ti/home/equipamentos
```

## Estrutura do projeto

```text
rastreio-ti/
├── app/
│   ├── Controllers/       # Controle das requisições e regras de entrada
│   ├── Models/            # Models e Repositories de acesso aos dados
│   ├── Services/          # Regras e transações de movimentação e cadastros
│   └── Views/             # Layout, componentes e telas
├── config/
│   ├── Database.php          # Conexão PDO e leitura da configuração
│   └── database.example.php # Modelo da configuração local
├── core/
│   ├── Controller.php    # Controller base e renderização das Views
│   └── Router.php        # Registro e execução das rotas
├── database/
│   └── rastreio_ti.sql   # Estrutura e dados iniciais do banco
├── public/
│   ├── css/              # Estilos da aplicação
│   ├── .htaccess         # Reescrita para URLs amigáveis
│   └── index.php         # Ponto de entrada da aplicação
├── composer.json             # Configuração do autoload PSR-4
├── composer.lock             # Versões reproduzíveis do Composer
└── README.md
```

O diretório `vendor/` é gerado por `composer install` e não é versionado.

## Publicação acadêmica no InfinityFree

O projeto inclui uma estrutura compatível com a pasta `htdocs/` do InfinityFree, mantendo `public/` como entrada interna e bloqueando o acesso direto aos diretórios de código e configuração. Consulte o guia completo em [`DEPLOYMENT_INFINITYFREE.md`](DEPLOYMENT_INFINITYFREE.md).

## Banco de dados

O script atual cria as tabelas:

- `usuarios`;
- `categorias`;
- `equipamentos`;
- `emprestimos`;
- `manutencoes`;
- `termos_responsabilidade`.

O sistema utiliza `usuarios`, `categorias`, `equipamentos`, `emprestimos` e `manutencoes`. O termo imprimível é gerado a partir do empréstimo; a tabela `termos_responsabilidade` fica reservada para um futuro armazenamento de documentos assinados. Não é necessária alteração de esquema para atualizar a versão já publicada.

## Demonstração

Fluxo sugerido para demonstrar o sistema:

O roteiro completo de empréstimo, devolução, manutenção, relatórios e permissões está em [docs/ATUALIZACAO_MODULOS.md](docs/ATUALIZACAO_MODULOS.md). Para o CRUD básico:

1. abrir o dashboard e apresentar os indicadores reais;
2. acessar a listagem de equipamentos;
3. abrir o formulário de novo equipamento;
4. preencher e salvar um cadastro;
5. confirmar a mensagem de sucesso e o item na listagem;
6. atualizar o dashboard e confirmar a alteração dos indicadores;
7. editar o item e confirmar a mensagem e os dados atualizados;
8. tentar repetir o número de série para demonstrar a validação;
9. excluir o item e confirmar sua remoção da listagem.

## Observação de segurança

O diagnóstico público de conexão com o banco foi removido. Credenciais nunca devem ser versionadas. O servidor usa `config/database.local.php`, protegido contra acesso HTTP. O pacote gerado por `scripts/package.ps1` exclui esse arquivo, testes, SQL, uploads e artefatos locais. Na primeira instalação, configure o arquivo local separadamente; em atualizações, preserve o existente.
