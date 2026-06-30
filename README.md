# Rastreador TI - Sistema de Controle de Ativos de TI

## 1. Descrição do Projeto

O **Rastreador TI** é uma aplicação web voltada para o gerenciamento de inventário e controle de atribuição de ativos de tecnologia (como notebooks, monitores, periféricos e servidores) em organizações. O sistema visa substituir o controle descentralizado feito por planilhas, centralizando o ciclo de vida do hardware — desde a entrada no estoque, passando pela entrega ao colaborador (empréstimo), até a baixa para manutenção ou descarte.

O projeto está sendo desenvolvido como Projeto Integrador para a disciplina de **Projeto e Implementação de Sistemas para Web II** no curso de Análise e Desenvolvimento de Sistemas (UNIVASF).

---

## 2. Objetivos e Público-Alvo

- **Objetivo Geral:** Desenvolver uma aplicação web responsiva utilizando PHP nativo sob a arquitetura MVC para automatizar o controle de inventário de TI e o histórico de posse de equipamentos de uma empresa.
- **Objetivos Específicos:**
  - Registrar equipamentos individualizados por número de série e categoria;
  - Controlar o fluxo de empréstimo e devolução de ativos para colaboradores;
  - Rastrear o histórico de manutenções de cada máquina;
  - Implementar níveis de acesso seguros;
  - Disponibilizar relatórios rápidos de ativos disponíveis ou em uso.
- **Público-Alvo:** \* **Administradores/Técnicos de TI:** Usuários gerenciais que cadastram movimentações, gerenciam o estoque e realizam as atribuições.
  - **Colaboradores da Empresa:** Usuários finais que acessam o sistema para consultar os equipamentos que estão sob sua posse atual e os termos de responsabilidade.

---

## 🛠️ 3. Funcionalidades Previstas

### Módulo de Segurança e Acesso

- Login/Logout com controle de sessão PHP.
- Proteção de rotas baseado no perfil (Admin e Colaborador).

### Módulo de Ativos (Equipamentos)

- CRUD completo de Equipamentos (Nome, Marca, Modelo, Número de Série, Status, Data de Aquisição).
- CRUD de Categorias (Ex: Notebooks, Monitores, Periféricos).

### Módulo de Empréstimos e Movimentação

- Registrar entrega de equipamento a um colaborador (vínculo com data de atribuição).
- Registrar devolução do equipamento com observações sobre o estado do ativo.
- Histórico completo de movimentações por equipamento.

### Módulo de Alertas e Relatórios

- Dashboard com indicadores visuais de estoque (Disponíveis, Em Uso, Em Manutenção).
- Relatório de Termos de Responsabilidade ativos.

---

## 📂 4. Estrutura do Projeto (MVC Nativo)

A estrutura de diretórios segue o padrão arquitetural MVC implementado de forma nativa:

```text
Rastreador TI/
├── app/
│   ├── Controllers/          # Lógica de controle
│   ├── Models/               # Acesso a dados (PDO)
│   └── Views/                # Templates e telas
├── config/                   # Configurações do sistema/banco
├── core/                     # Núcleo do framework nativo (Roteador, Session, etc.)
├── public/                   # Único ponto de entrada da aplicação
│   ├── css/                  # Estilos (Bootstrap 5 via CDN)
│   ├── js/                   # Scripts JavaScript
│   └── index.php             # Front Controller
└── README.md
```
