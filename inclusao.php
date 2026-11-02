<?php
require_once __DIR__ . '/conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = trim($_POST['categoria'] ?? 'Trabalho');
    $prioridade = $_POST['prioridade'] ?? 'Normal';

    if (mb_strlen($titulo) < 3) {
        $erro = 'Digite um título com pelo menos 3 caracteres.';
    } else {
        $tarefa = R::dispense('tarefas');
        $tarefa->titulo = $titulo;
        $tarefa->descricao = $descricao ?: null;
        $tarefa->categoria = $categoria ?: 'Trabalho';
        $tarefa->prioridade = in_array($prioridade, ['Baixa', 'Normal', 'Alta'], true)
            ? $prioridade
            : 'Normal';
        $tarefa->prazo = $_POST['prazo'] ?: null;
        $tarefa->concluida = 0;

        R::store($tarefa);
        header('Location: index.php?sucesso=1');
        exit;
    }
}

topo('Nova tarefa', 'inclusao');
?>
<section class="page">
    <div class="form-card">
        <h1>Nova tarefa</h1>
        <p class="intro">Preencha os dados abaixo.</p>

        <?php if ($erro): ?>
            <div class="message error"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="field-grid">
                <div class="field full">
                    <label for="titulo">Título</label>
                    <input id="titulo" name="titulo" required autofocus maxlength="180">
                </div>

                <div class="field full">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao"></textarea>
                </div>

                <div class="field">
                    <label for="prazo">Prazo</label>
                    <input id="prazo" name="prazo" type="date">
                </div>

                <div class="field">
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria">
                        <option>Trabalho</option>
                        <option>Pessoal</option>
                        <option>Estudos</option>
                        <option>Casa</option>
                    </select>
                </div>

                <div class="field">
                    <label for="prioridade">Prioridade</label>
                    <select id="prioridade" name="prioridade">
                        <option>Normal</option>
                        <option>Alta</option>
                        <option>Baixa</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <a class="secondary-button" href="index.php">Cancelar</a>
                <button class="primary-button" type="submit">Salvar</button>
            </div>
        </form>
    </div>
</section>
<?php rodape(); ?>
