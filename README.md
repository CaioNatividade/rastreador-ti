# Rastreio TI

Sistema web para controle de ativos de tecnologia em ambientes corporativos, desenvolvido em PHP nativo com arquitetura MVC e banco de dados MySQL/MariaDB.

O projeto faz parte da disciplina **Projeto e Implementação de Sistemas para Web II**, do curso de Análise e Desenvolvimento de Sistemas da UNIVASF.

## Sobre o projeto

O Rastreio TI busca centralizar o inventário e o ciclo de vida de equipamentos como notebooks, monitores, periféricos e servidores. A proposta é substituir controles descentralizados por uma aplicação capaz de registrar ativos, acompanhar sua disponibilidade e, em etapas futuras, controlar empréstimos, devoluções e manutenções.

### Público-alvo

- **Administradores e técnicos de TI:** responsáveis pelo inventário e pelas movimentações dos ativos.
- **Colaboradores:** usuários que poderão consultar os equipamentos sob sua responsabilidade.

## Estado atual — Entrega Parcial 3

Funcionalidades implementadas e testadas:

- estrutura MVC nativa;
- Front Controller e sistema de rotas;
- suporte a rotas GET e POST;
- autoload PSR-4 com Composer;
- conexão com MySQL/MariaDB por PDO;
- cadastro de equipamentos (Create);
- listagem de equipamentos com suas categorias (Read);
- validação dos dados do cadastro;
- bloqueio de número de série duplicado;
- consultas preparadas;
- dashboard com indicadores reais do inventário;
- tratamento de erros HTTP 404, 405, 422 e 500.

### Funcionalidades futuras

- atualização e exclusão de equipamentos;
- cadastro e gerenciamento de categorias;
- cadastro e gerenciamento de usuários;
- registro de empréstimos e devoluções;
- histórico de manutenções;
- login, sessões e controle de acesso;
- termos de responsabilidade e relatórios.

O link de empréstimos permanece visível como indicação de um módulo planejado, mas essa funcionalidade ainda não faz parte da implementação atual.

## Requisitos

- PHP 8.1 ou superior;
- extensão `pdo_mysql` habilitada;
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

4. Importe [`database/rastreio_ti.sql`](database/rastreio_ti.sql) pelo phpMyAdmin ou pelo cliente do MySQL usando o conjunto de caracteres `utf8mb4`.

5. Confira as credenciais locais em [`config/Database.php`](config/Database.php). A configuração padrão do projeto é:

   ```text
   host: localhost
   banco: rastreio_ti
   usuário: root
   senha: vazia
   ```

6. Acesse a aplicação. Considerando a pasta `rastreio-ti` dentro do `htdocs` do XAMPP:

   ```text
   http://localhost/rastreio-ti/public/home
   ```

> As credenciais do banco devem ser adaptadas ao ambiente local. A configuração padrão não deve ser usada em produção.

## Rotas implementadas

| Método | Rota | Descrição |
|---|---|---|
| GET | `/home` | Dashboard com indicadores reais |
| GET | `/home/equipamentos` | Listagem de equipamentos |
| GET | `/home/equipamentos/novo` | Formulário de cadastro |
| POST | `/home/equipamentos` | Processamento do cadastro |
| GET | `/home/emprestimos` | Tela informativa do módulo futuro |
| GET | `/home/categorias` | Tela inicial de categorias |
| GET | `/home/manutencoes` | Tela inicial de manutenções |
| GET | `/home/usuarios` | Tela inicial de usuários |

As rotas são relativas ao diretório `public`. Por exemplo:

```text
http://localhost/rastreio-ti/public/home/equipamentos
```

## Estrutura do projeto

```text
rastreio-ti/
├── app/
│   ├── Controllers/       # Controle das requisições e regras de entrada
│   ├── Models/            # Models e Repositories de acesso aos dados
│   └── Views/             # Layout, componentes e telas
├── config/
│   └── Database.php      # Configuração e conexão PDO
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

## Banco de dados

O script atual cria as tabelas:

- `usuarios`;
- `categorias`;
- `equipamentos`;
- `emprestimos`;
- `manutencoes`;
- `termos_responsabilidade`.

Nesta entrega, a aplicação utiliza diretamente as tabelas `equipamentos` e `categorias`. As demais fazem parte da modelagem preparada para as próximas etapas.

## Demonstração da Entrega Parcial 3

Fluxo sugerido para demonstrar o sistema:

1. abrir o dashboard e apresentar os indicadores reais;
2. acessar a listagem de equipamentos;
3. abrir o formulário de novo equipamento;
4. preencher e salvar um cadastro;
5. confirmar a mensagem de sucesso e o item na listagem;
6. atualizar o dashboard e confirmar a alteração dos indicadores;
7. tentar repetir o número de série para demonstrar a validação.

## Observação de segurança

O arquivo `public/teste_conexao.php` existe para diagnóstico no ambiente de desenvolvimento. Ele deve ser protegido ou removido antes de uma eventual publicação do sistema.
