<?php
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

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Rastreio TI</title>
</head>

<body>
  <div class="container-fluid">

    <h1 class="mb-4">Dashboard</h1>

    <h3 class="my-4 mb-3 text-center">Painel de Controle</h3>

    <div class="row g-4">

      <div class="col-md-3">
        <div class="card shadow">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h6 class="text-muted">
                  Total Equipamentos
                </h6>

                <h2>74</h2>
              </div>

              <i class="bi bi-pc-display fs-1"></i>

            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow border-0">
          <div class="card-body border-start border-5 border-success rounded">
            <h6 class="text-muted">Disponíveis</h6>
            <h2>15</h2>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow border-0">
          <div class="card-body border-start border-5 border-warning rounded">
            <h6 class="text-muted">Emprestados</h6>
            <h2>52</h2>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow border-0">
          <div class="card-body border-start border-5 border-danger rounded">
            <h6 class="text-muted">Manutenção</h6>
            <h2>7</h2>
          </div>
        </div>
      </div>

    </div>

    <h3 class="mt-5 mb-3 text-center">Últimos Movimentos</h3>

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
          <?php foreach (array_slice($dataEquipamentos, 0, 5) as $e): ?>
            <tr>
              <td class="align-middle py-3">
                <span class=""><?= $e['nome'] ?></span>
              </td>
              <td class="text-center">
                <span class=""><?= $e['categoria_id'] ?></span>
              </td>
              <td class="text-center">
                <span class=""><?= $e['numero_serie'] ?></span>
              </td>
              <td class="text-center">
                <span class="p-3 badge text-<?= $status[$e['status']]['classe'] ?>" style="width: 100%;"><?= str_replace("_", " ", ucfirst($e['status'])) ?></span>
              </td>
              <td class="text-center">
                <span><?= $e['atualizado_em'] ?></span>
              </td>
              <td>
                <a href="#">Exc | Edit | Viz</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
  </div>

</body>

</html>