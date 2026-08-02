<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EquipamentosModel;
use App\Models\EquipamentosRepository;
use Core\Controller;
use DateTimeImmutable;

class EquipamentosController extends Controller
{
    private EquipamentosRepository $repository;

    public function __construct()
    {
        $this->repository = new EquipamentosRepository();
    }

    public function index(): void
    {
        $this->view('equipamentos', [
            'equipamentos' => $this->repository->select(),
            'cadastroRealizado' => ($_GET['cadastro'] ?? '') === 'sucesso',
        ]);
    }

    public function create(array $errors = [], array $old = []): void
    {
        $this->view('equipamentos-form', [
            'categorias' => $this->repository->selectCategories(),
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    public function store(): void
    {
        $input = [
            'nome' => trim((string) ($_POST['nome'] ?? '')),
            'marca' => trim((string) ($_POST['marca'] ?? '')),
            'modelo' => trim((string) ($_POST['modelo'] ?? '')),
            'numero_serie' => trim((string) ($_POST['numero_serie'] ?? '')),
            'categoria_id' => trim((string) ($_POST['categoria_id'] ?? '')),
            'data_aquisicao' => trim((string) ($_POST['data_aquisicao'] ?? '')),
            'observacoes' => trim((string) ($_POST['observacoes'] ?? '')),
        ];

        $errors = $this->validate($input);

        if ($errors !== []) {
            http_response_code(422);
            $this->create($errors, $input);
            return;
        }

        $model = new EquipamentosModel();
        $model->nome = $input['nome'];
        $model->marca = $input['marca'] !== '' ? $input['marca'] : null;
        $model->modelo = $input['modelo'] !== '' ? $input['modelo'] : null;
        $model->numeroSerie = $input['numero_serie'];
        $model->categoriaId = (int) $input['categoria_id'];
        $model->dataAquisicao = $input['data_aquisicao'] !== '' ? $input['data_aquisicao'] : null;
        $model->observacoes = $input['observacoes'] !== '' ? $input['observacoes'] : null;
        $model->save();

        $this->redirect('home/equipamentos?cadastro=sucesso');
    }

    /**
     * @param array<string, string> $input
     * @return array<string, string>
     */
    private function validate(array $input): array
    {
        $errors = [];

        if ($input['nome'] === '') {
            $errors['nome'] = 'Informe o nome do equipamento.';
        } elseif (mb_strlen($input['nome']) > 150) {
            $errors['nome'] = 'O nome deve ter no máximo 150 caracteres.';
        }

        if (mb_strlen($input['marca']) > 100) {
            $errors['marca'] = 'A marca deve ter no máximo 100 caracteres.';
        }

        if (mb_strlen($input['modelo']) > 100) {
            $errors['modelo'] = 'O modelo deve ter no máximo 100 caracteres.';
        }

        if ($input['numero_serie'] === '') {
            $errors['numero_serie'] = 'Informe o número de série.';
        } elseif (mb_strlen($input['numero_serie']) > 100) {
            $errors['numero_serie'] = 'O número de série deve ter no máximo 100 caracteres.';
        } elseif ($this->repository->serialNumberExists($input['numero_serie'])) {
            $errors['numero_serie'] = 'Este número de série já está cadastrado.';
        }

        $categoryId = filter_var($input['categoria_id'], FILTER_VALIDATE_INT);
        if ($categoryId === false || !$this->repository->categoryExists((int) $categoryId)) {
            $errors['categoria_id'] = 'Selecione uma categoria válida.';
        }

        if ($input['data_aquisicao'] !== '' && !$this->isValidDate($input['data_aquisicao'])) {
            $errors['data_aquisicao'] = 'Informe uma data de aquisição válida.';
        }

        return $errors;
    }

    private function isValidDate(string $date): bool
    {
        $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsedDate !== false && $parsedDate->format('Y-m-d') === $date;
    }
}
