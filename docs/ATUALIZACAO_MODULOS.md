# Atualização dos módulos acadêmicos

## O que passa a funcionar

- **Equipamentos:** cadastro, edição, exclusão sem histórico, busca por nome/série/categoria e filtro de status.
- **Manutenções:** abrir ficha, visualizar andamento/histórico, concluir com descrição e custo, devolver para disponível ou dar baixa. Marcar um equipamento como manutenção cria a ficha na mesma transação.
- **Empréstimos:** selecionar equipamento disponível e colaborador ativo, registrar entrega, previsão, observações e devolução. Atrasos são calculados pela data atual, sem cron.
- **Categorias:** criar, editar e excluir quando não houver equipamentos vinculados.
- **Usuários:** cadastrar administrador/colaborador, editar, redefinir senha e desativar. Não é permitido remover o próprio acesso administrativo nem deixar o sistema sem administrador.
- **Minha conta:** trocar a senha usando a senha atual. As outras sessões deixam de valer.
- **Termos:** impressão ou salvamento em PDF pelo navegador, com campos para assinatura manual. Não há assinatura eletrônica nem upload de documentos assinados.
- **Relatórios:** inventário filtrado, CSV e impressão/PDF pelo navegador.
- **Dashboard:** contagens de equipamentos, empréstimos ativos e atrasados. Colaboradores veem somente seus empréstimos e equipamentos.

## Atualizar o site existente no InfinityFree

1. Exporte um backup do banco pelo phpMyAdmin e baixe os arquivos atuais para seu computador.
2. Gere o pacote com `powershell -ExecutionPolicy Bypass -File scripts/package.ps1` ou use o ZIP fornecido em `dist/`.
3. No File Manager, entre em `htdocs` e use **Upload & Unzip** para extrair o novo pacote nessa pasta, substituindo os arquivos existentes.
4. **Preserve `config/database.local.php`** no servidor: o ZIP não o contém. O pacote também não contém SQL, dados de usuários ou uploads.
5. **Não importe `database/rastreio_ti.sql` novamente.** Esta atualização usa as mesmas seis tabelas existentes, sem migração destrutiva. A senha do administrador e os cadastros são preservados.
6. Faça logout/login e use Ctrl+F5 para atualizar o CSS.
7. Remova do servidor qualquer ZIP que tenha sido mantido após a extração.

Atualizar o GitHub não atualiza automaticamente os arquivos do InfinityFree.

## Cadastros anteriores à atualização

- Equipamentos já marcados como manutenção sem ficha aparecem como pendentes na aba Manutenções. Selecione-os no formulário e registre o problema e a data de início. As novas alterações de status passam a criar a ficha automaticamente.
- Equipamentos antigos Em uso sem empréstimo aparecem num aviso em Empréstimos. Edite-os para Disponível e registre uma entrega com o responsável correto. O sistema não inventa um colaborador ou uma data histórica.
- Um equipamento com histórico não pode ser excluído. Para retirá-lo de uso, conclua as movimentações abertas e marque Baixado.

## Roteiro da demonstração

1. Como administrador, cadastre uma categoria e um colaborador ativo.
2. Cadastre um equipamento disponível nessa categoria.
3. Empreste-o ao colaborador com devolução prevista. Verifique Em uso no inventário e o empréstimo no dashboard.
4. Abra uma aba anônima com o colaborador: confira somente seus dados, abra o termo e tente acessar `/home/usuarios` (403).
5. Como administrador, registre a devolução. Confira que o equipamento fica disponível.
6. Edite o equipamento para Manutenção e confirme a ficha na aba Manutenções.
7. Conclua a ficha com descrição, data e custo. Confira a volta para Disponível.
8. Abra uma nova manutenção e conclua com Baixado. Confira que ele não aparece para empréstimo.
9. Filtre o relatório, exporte CSV e imprima/salve PDF.
10. Troque a senha em Minha conta e confirme que a senha anterior não funciona.

## Verificação local

Os testes criam bancos com prefixo aleatório `rastreio_test_` somente em `127.0.0.1`. Nunca leem as credenciais do InfinityFree. Configure `TEST_DB_PORT`, `TEST_DB_USER` e `TEST_DB_PASS` para um MariaDB local destinado a testes.

```powershell
php tests/integration.php
php tests/http.php
```

O segundo teste inicia um servidor PHP local na porta 18086. Para repetir com Apache/XAMPP, ajuste os caminhos em `tests/httpd-test.conf` e defina `TEST_APACHE` com o caminho de `httpd.exe`.

As verificações cobrem transições e rollback, preservação de histórico, formulários reais com sessões e CSRF, permissões, isolamento de colaboradores, duas sessões concorrentes emprestando o mesmo item, geração de CSV e revogação de sessões. Os testes Apache verificam também os bloqueios por `.htaccess` e a entrega de CSS.
