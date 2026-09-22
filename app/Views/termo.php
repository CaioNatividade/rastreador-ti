<div class="d-print-none mb-4"><button type="button" class="btn btn-primary" onclick="window.print()">Imprimir / salvar PDF</button> <a class="btn btn-outline-secondary" href="<?= $e($basePath) ?>/home/emprestimos">Voltar</a></div>
<article class="card card-body">
    <h1 class="h3">Termo de responsabilidade — Rastreio TI</h1>
    <p>Empréstimo nº <?= (int) $emprestimo['id'] ?></p>
    <p>Eu, <strong><?= $e($emprestimo['colaborador']) ?></strong> (<?= $e($emprestimo['email']) ?>), declaro receber o equipamento abaixo para uso autorizado e me responsabilizo por sua guarda, conservação e devolução.</p>
    <dl class="row">
        <dt class="col-sm-4">Equipamento</dt><dd class="col-sm-8"><?= $e($emprestimo['equipamento']) ?></dd>
        <dt class="col-sm-4">Número de série</dt><dd class="col-sm-8"><?= $e($emprestimo['numero_serie']) ?></dd>
        <dt class="col-sm-4">Entrega</dt><dd class="col-sm-8"><?= $e(date('d/m/Y H:i', strtotime($emprestimo['data_entrega']))) ?></dd>
        <dt class="col-sm-4">Devolução prevista</dt><dd class="col-sm-8"><?= $emprestimo['data_devolucao_prevista'] ? $e(date('d/m/Y', strtotime($emprestimo['data_devolucao_prevista']))) : 'Não informada' ?></dd>
        <dt class="col-sm-4">Entregue por</dt><dd class="col-sm-8"><?= $e($emprestimo['responsavel']) ?></dd>
        <dt class="col-sm-4">Condições na entrega</dt><dd class="col-sm-8 text-break"><?= $e($emprestimo['observacoes_entrega']) ?></dd>
    </dl>
    <?php if ($emprestimo['data_devolucao']): ?><p>Devolvido em <?= $e(date('d/m/Y H:i', strtotime($emprestimo['data_devolucao']))) ?>. <?= $e($emprestimo['observacoes_devolucao']) ?></p><?php endif; ?>
    <p class="mt-5">____________________________________<br>Assinatura do colaborador</p>
    <p class="mt-4">____________________________________<br>Assinatura do responsável pela entrega</p>
    <p class="small text-muted mt-3">Documento para impressão e assinatura manual. A emissão não registra assinatura eletrônica.</p>
</article>
