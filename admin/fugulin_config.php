<?php
require_once __DIR__ . '/../includes/header.php';

// Apenas administradores podem configurar as questões
if ($_SESSION['user_nivel'] !== 'Administrador') {
    $_SESSION['mensagem_erro'] = "Acesso restrito a administradores.";
    redirect('dashboard.php');
}

// Lógica de Atualização/Criação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_questao'])) {
    $id_questao = (int)$_POST['id_questao'];
    $titulo = cleanInput($_POST['titulo']);
    $opcoes = $_POST['opcao'] ?? []; // [id_opcao] => texto

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE fugulin_questoes SET titulo = ? WHERE id = ?");
        $stmt->execute([$titulo, $id_questao]);

        foreach ($opcoes as $id_opt => $texto) {
            $stmt_opt = $pdo->prepare("UPDATE fugulin_opcoes SET descricao = ? WHERE id = ?");
            $stmt_opt->execute([$texto, (int)$id_opt]);
        }

        $pdo->commit();
        $_SESSION['mensagem_sucesso'] = "Questão '$titulo' atualizada com sucesso!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mensagem_erro'] = "Erro ao salvar: " . $e->getMessage();
    }
    redirect('fugulin_config.php');
}

// Busca questões e opções
$questoes_raw = $pdo->query("
    SELECT q.*, o.id as opcao_id, o.pontuacao, o.descricao as opcao_descricao
    FROM fugulin_questoes q
    JOIN fugulin_opcoes o ON q.id = o.id_questao
    ORDER BY q.ordem ASC, o.pontuacao ASC
")->fetchAll();

$questoes = [];
foreach ($questoes_raw as $row) {
    if (!isset($questoes[$row['id']])) {
        $questoes[$row['id']] = [
            'id' => $row['id'],
            'titulo' => $row['titulo'],
            'opcoes' => []
        ];
    }
    $questoes[$row['id']]['opcoes'][] = [
        'id' => $row['opcao_id'],
        'pontos' => $row['pontuacao'],
        'texto' => $row['opcao_descricao']
    ];
}
?>

<div class="mb-8">
    <h1 class="text-3xl font-black text-slate-800 tracking-tight">Configuração Fugulin</h1>
    <p class="text-slate-500">Edite os títulos das questões e os textos das opções de resposta.</p>
</div>

<div class="grid grid-cols-1 gap-6">
    <?php foreach ($questoes as $q): ?>
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <form action="fugulin_config.php" method="POST">
                <input type="hidden" name="id_questao" value="<?php echo $q['id']; ?>">
                
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <div class="flex-1">
                        <label class="block text-[10px] font-black uppercase text-blue-500 tracking-widest mb-1">Título da Questão</label>
                        <input type="text" name="titulo" value="<?php echo cleanInput($q['titulo']); ?>" 
                               class="w-full bg-transparent text-lg font-black text-slate-800 border-none p-0 focus:ring-0">
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($q['opcoes'] as $opt): ?>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">
                                Opção de <?php echo $opt['pontos']; ?> Pontos
                            </label>
                            <textarea name="opcao[<?php echo $opt['id']; ?>]" rows="2" 
                                      class="w-full bg-white border border-slate-200 rounded-xl p-3 text-sm font-bold text-slate-600 focus:ring-2 focus:ring-blue-500 outline-none transition-all"><?php echo cleanInput($opt['texto']); ?></textarea>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button type="submit" name="salvar_questao" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl font-bold text-sm transition-all shadow-lg shadow-blue-500/20">
                        <i class="fas fa-save mr-2"></i> Atualizar Questão
                    </button>
                </div>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<?php 
echo "</div></main></body></html>";
?>
