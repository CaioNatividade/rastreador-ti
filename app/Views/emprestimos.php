<?php

/** @var array<int, array<string, mixed>> $dataEquipamentos */
$status = [
  'disponivel' => [
    'texto' => 'Disponível',
    'classe' => 'bg-success'
  ],
  'em_uso' => [
    'texto' => 'Em uso',
    'classe' => 'bg-primary'
  ],
  'manutencao' => [
    'texto' => 'Manutenção',
    'classe' => 'bg-warning text-dark'
  ],
  'baixado' => [
    'texto' => 'Baixado',
    'classe' => 'bg-danger'
  ]
];
?>

<h1 class="mb-3">Listagem de Empréstimos</h1>
<!-- <h3 class="mt-5 mb-3 text-center">Últimos Movimentos</h3> -->

<div class="table-responsive rounded-3 overflow-hidden">
  <table class="table table-hover align-middle ">
    <thead class="table-dark">
      <tr>
        <th>Nome/Modelo</th>
        <th class="text-center">Categoria</th>
        <th class="text-center">Número de Série</th>
        <th class="text-center">Status</th>
        <th class="text-center">Data</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($dataEquipamentos as $e): ?>
        <?php $statusAtual = $status[$e['status']] ?? ['texto' => 'Desconhecido', 'classe' => 'bg-secondary']; ?>
        <tr>
          <td class="align-middle py-3">
            <span><?= htmlspecialchars($e['nome']) ?></span>
          </td>
          <td class="text-center">
            <span><?= htmlspecialchars((string) $e['categoria_id']) ?></span>
          </td>
          <td class="text-center">
            <span><?= htmlspecialchars($e['numero_serie']) ?></span>
          </td>
          <td class="text-center">
            <span class="p-3 badge <?= htmlspecialchars($statusAtual['classe']) ?>" style="width: 100%;">
              <?= htmlspecialchars($statusAtual['texto']) ?>
            </span>
          </td>
          <td class="text-center">
            <span><?= htmlspecialchars($e['atualizado_em']) ?></span>
          </td>
          <td>
            <a href="#">Exc | Edit | Viz</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>