<?php
require_once __DIR__ . '/conexao.php';

$erroNova = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'alternar') {
        $tarefa = R::load('tarefas', (int) ($_POST['id'] ?? 0));

        if ($tarefa->id) {
            $tarefa->concluida = $tarefa->concluida ? 0 : 1;
            R::store($tarefa);
        }

        header('Location: index.php');
        exit;
    }

    if ($acao === 'adicionar') {
        $tituloNovo = trim($_POST['titulo'] ?? '');
        $descricaoNova = trim($_POST['descricao'] ?? '');
        $categoriaNova = trim($_POST['categoria'] ?? 'Trabalho');
        $prioridadeNova = $_POST['prioridade'] ?? 'Normal';

        if (mb_strlen($tituloNovo) < 3) {
            $erroNova = 'Digite um título com pelo menos 3 caracteres.';
        } else {
            $tarefa = R::dispense('tarefas');
            $tarefa->titulo = $tituloNovo;
            $tarefa->descricao = $descricaoNova ?: null;
            $tarefa->categoria = $categoriaNova ?: 'Trabalho';
            $tarefa->prioridade = in_array($prioridadeNova, ['Baixa', 'Normal', 'Alta'], true)
                ? $prioridadeNova
                : 'Normal';
            $tarefa->prazo = $_POST['prazo'] ?: null;
            $tarefa->concluida = 0;

            R::store($tarefa);
            header('Location: index.php?sucesso=1');
            exit;
        }
    }
}

$filtro = $_GET['filtro'] ?? 'todas';
$ordem = 'ORDER BY concluida ASC, prazo IS NULL ASC, prazo ASC, criada_em DESC';

if ($filtro === 'pendentes') {
    $consulta = 'WHERE concluida = 0 ' . $ordem;
} elseif ($filtro === 'concluidas') {
    $consulta = 'WHERE concluida = 1 ' . $ordem;
} else {
    $consulta = $ordem;
}

$tarefas = R::findAll('tarefas', $consulta);
$total = R::count('tarefas');
$concluidas = R::count('tarefas', 'concluida = 1');
$pendentes = $total - $concluidas;

topo('Lista de tarefas', 'index');
?>
<section class="page">
    <?php if (isset($_GET['sucesso'])): ?>
        <div class="message">Tarefa adicionada.</div>
    <?php endif; ?>

    <?php if (isset($_GET['excluida'])): ?>
        <div class="message">Tarefa excluída.</div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <aside class="control-panel">
            <div class="panel-title">
                <span class="eyebrow">Painel</span>
                <h1>Tarefas</h1>
                <p><?= $total ?> cadastrada(s)</p>
            </div>

            <label class="primary-button full-button" for="modal-nova">＋ Nova tarefa</label>

            <div class="counter-list">
                <div class="counter-item">
                    <span>Total</span>
                    <strong><?= $total ?></strong>
                </div>
                <div class="counter-item">
                    <span>Pendentes</span>
                    <strong><?= $pendentes ?></strong>
                </div>
                <div class="counter-item">
                    <span>Concluídas</span>
                    <strong><?= $concluidas ?></strong>
                </div>
            </div>

            <div class="filter-panel">
                <span class="filter-title">Exibir</span>
                <a class="filter <?= $filtro === 'todas' ? 'active' : '' ?>" href="?filtro=todas">Todas</a>
                <a class="filter <?= $filtro === 'pendentes' ? 'active' : '' ?>" href="?filtro=pendentes">Pendentes</a>
                <a class="filter <?= $filtro === 'concluidas' ? 'active' : '' ?>" href="?filtro=concluidas">Concluídas</a>
            </div>
        </aside>

        <section class="tasks-panel">
            <div class="tasks-heading">
                <div>
                    <span class="eyebrow">Registros</span>
                    <h2><?= $filtro === 'todas' ? 'Todas as tarefas' : ucfirst($filtro) ?></h2>
                </div>
                <span class="task-count"><?= count($tarefas) ?> item(ns)</span>
            </div>

            <?php if (!$tarefas): ?>
                <div class="empty">
                    <strong>Nenhuma tarefa encontrada.</strong>
                    <span>Use “Nova tarefa” para cadastrar uma.</span>
                </div>
            <?php else: ?>
                <div class="task-list">
                    <?php foreach ($tarefas as $tarefa): ?>
                        <article class="task-row">
                            <form method="post" action="index.php">
                                <input type="hidden" name="acao" value="alternar">
                                <input type="hidden" name="id" value="<?= (int) $tarefa->id ?>">
                                <button class="check <?= $tarefa->concluida ? 'done' : '' ?>" type="submit" title="Marcar como concluída">
                                    <?= $tarefa->concluida ? '✓' : '' ?>
                                </button>
                            </form>

                            <div class="task-main <?= $tarefa->concluida ? 'completed' : '' ?>">
                                <strong><?= e($tarefa->titulo) ?></strong>
                                <?php if ($tarefa->descricao): ?>
                                    <small><?= e(mb_strimwidth($tarefa->descricao, 0, 80, '…')) ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="task-details">
                                <span class="tag <?= $tarefa->prioridade === 'Alta' ? 'high' : ($tarefa->prioridade === 'Baixa' ? 'low' : '') ?>">
                                    <?= e($tarefa->categoria) ?>
                                </span>
                                <span class="due">
                                    <?= $tarefa->prazo ? e(date('d/m/Y', strtotime((string) $tarefa->prazo))) : 'Sem prazo' ?>
                                </span>
                            </div>

                            <a class="delete-link" href="exclusao.php?id=<?= (int) $tarefa->id ?>" title="Excluir">×</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</section>

<input class="modal-toggle" type="checkbox" id="modal-nova" <?= $erroNova ? 'checked' : '' ?>>
<div class="modal-backdrop">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modal-titulo">
        <div class="modal-header">
            <h2 id="modal-titulo">Nova tarefa</h2>
            <label class="modal-close" for="modal-nova" aria-label="Fechar">×</label>
        </div>

        <?php if ($erroNova): ?>
            <div class="message error"><?= e($erroNova) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="acao" value="adicionar">
            <div class="field-grid">
                <div class="field full">
                    <label for="modal-titulo-input">Título</label>
                    <input id="modal-titulo-input" name="titulo" required maxlength="180">
                </div>
                <div class="field full">
                    <label for="modal-descricao">Descrição</label>
                    <textarea id="modal-descricao" name="descricao"></textarea>
                </div>
                <div class="field">
                    <label for="modal-prazo">Prazo</label>
                    <input id="modal-prazo" name="prazo" type="date">
                </div>
                <div class="field">
                    <label for="modal-categoria">Categoria</label>
                    <select id="modal-categoria" name="categoria">
                        <option>Trabalho</option>
                        <option>Pessoal</option>
                        <option>Estudos</option>
                        <option>Casa</option>
                    </select>
                </div>
                <div class="field">
                    <label for="modal-prioridade">Prioridade</label>
                    <select id="modal-prioridade" name="prioridade">
                        <option>Normal</option>
                        <option>Alta</option>
                        <option>Baixa</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <label class="secondary-button" for="modal-nova">Cancelar</label>
                <button class="primary-button" type="submit">Salvar</button>
            </div>
        </form>
    </div>
</div>
<?php rodape(); ?>
