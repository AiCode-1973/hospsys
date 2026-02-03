<?php
require_once __DIR__ . '/../includes/header.php';

$id_checklist = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id_checklist) {
    $_SESSION['mensagem_erro'] = "Auditoria não especificada.";
    redirect('car_relatorios.php');
}

// 1. Busca Cabeçalho do Checklist
$sql_check = "
    SELECT ch.*, c.nome as carrinho_nome, s.nome as setor_nome, u.nome as usuario_nome
    FROM car_checklists ch
    JOIN car_carrinhos c ON ch.id_carrinho = c.id
    LEFT JOIN fugulin_setores s ON c.id_setor = s.id
    JOIN usuarios u ON ch.id_usuario = u.id
    WHERE ch.id = ?
";
$stmt = $pdo->prepare($sql_check);
$stmt->execute([$id_checklist]);
$checklist = $stmt->fetch();

if (!$checklist) {
    $_SESSION['mensagem_erro'] = "Registro de auditoria não encontrado.";
    redirect('car_relatorios.php');
}

// 2. Busca Itens Conferidos (Ordenados por Gaveta)
$sql_itens = "
    SELECT ci.*, i.nome as item_nome, i.tipo as item_tipo, i.unidade,
           comp.quantidade_ideal
    FROM car_checklist_itens ci
    JOIN car_itens_mestres i ON ci.id_item = i.id
    LEFT JOIN car_composicao_ideal comp ON (ci.id_item = comp.id_item AND comp.id_carrinho = ?)
    WHERE ci.id_checklist = ?
    ORDER BY ci.gaveta ASC, i.nome ASC
";
$stmt = $pdo->prepare($sql_itens);
$stmt->execute([$checklist['id_carrinho'], $id_checklist]);
$itens = $stmt->fetchAll();

// 3. Busca Nomes das Gavetas
$stmt_gavetas = $pdo->prepare("SELECT num_gaveta, descricao FROM car_gavetas_config WHERE id_carrinho = ?");
$stmt_gavetas->execute([$checklist['id_carrinho']]);
$gavetas_labels = $stmt_gavetas->fetchAll(PDO::FETCH_KEY_PAIR);
for ($i=1; $i<=4; $i++) {
    if (!isset($gavetas_labels[$i])) $gavetas_labels[$i] = "Gaveta $i";
}
?>

<div class="max-w-4xl mx-auto pb-24">
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <a href="car_relatorios.php" class="text-blue-600 font-bold text-xs uppercase tracking-widest flex items-center gap-2 mb-2 hover:gap-3 transition-all">
                <i class="fas fa-arrow-left"></i> Voltar aos Relatórios
            </a>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Detalhes da Auditoria</h1>
            <p class="text-slate-500 font-bold">Registro #<?php echo $id_checklist; ?> - <?php echo $checklist['tipo']; ?></p>
        </div>
        
        <div class="flex items-center gap-3">
            <?php 
                $status_color = ($checklist['status_final'] === 'Conforme') ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600';
            ?>
            <span class="px-6 py-3 <?php echo $status_color; ?> text-xs font-black uppercase rounded-2xl shadow-sm border border-current/10">
                <?php echo $checklist['status_final']; ?>
            </span>
            <button onclick="window.print()" class="w-12 h-12 bg-white border border-slate-200 text-slate-400 rounded-2xl hover:text-blue-600 transition-all shadow-sm flex items-center justify-center print:hidden">
                <i class="fas fa-print"></i>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Carrinho / Setor</label>
            <p class="font-black text-slate-800 uppercase text-sm"><?php echo cleanInput($checklist['carrinho_nome']); ?></p>
            <p class="text-[10px] text-slate-500 font-bold"><?php echo cleanInput($checklist['setor_nome']); ?></p>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Data e Hora</label>
            <p class="font-black text-slate-800 text-sm"><?php echo date('d/m/Y', strtotime($checklist['data_conferencia'])); ?></p>
            <p class="text-[10px] text-slate-500 font-bold"><?php echo date('H:i:s', strtotime($checklist['data_conferencia'])); ?></p>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Auditor Responsável</label>
            <p class="font-black text-slate-800 text-sm flex items-center gap-2">
                <i class="fas fa-user-circle text-slate-300"></i>
                <?php echo cleanInput($checklist['usuario_nome']); ?>
            </p>
        </div>
    </div>

    <!-- Lista de Itens Grouped by Gaveta -->
    <div class="space-y-8">
        <?php 
        $current_gaveta = null;
        foreach ($itens as $i): 
            if ($current_gaveta !== $i['gaveta']):
                $current_gaveta = $i['gaveta'];
        ?>
            <div class="pt-4 first:pt-0">
                <div class="flex items-center gap-4 mb-4">
                    <span class="px-5 py-2 bg-slate-800 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-full shadow-lg">
                        <?php echo $gavetas_labels[$current_gaveta] ?? "Gaveta $current_gaveta"; ?>
                    </span>
                    <div class="h-px flex-1 bg-slate-100"></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm hover:border-slate-200 transition-all">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex-1">
                    <span class="text-[9px] font-black uppercase tracking-widest <?php echo ($i['item_tipo'] == 'Medicamento') ? 'text-blue-500' : 'text-emerald-500'; ?>">
                        <?php echo $i['item_tipo']; ?>
                    </span>
                    <h4 class="text-lg font-black text-slate-800 leading-tight"><?php echo cleanInput($i['item_nome']); ?></h4>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-center">
                    <div>
                        <label class="block text-[8px] font-black uppercase text-slate-400 tracking-widest mb-1">Padrão</label>
                        <p class="font-black text-slate-400 text-sm"><?php echo $i['quantidade_ideal']; ?> <span class="text-[10px]"><?php echo $i['unidade']; ?></span></p>
                    </div>
                    <div>
                        <label class="block text-[8px] font-black uppercase text-slate-400 tracking-widest mb-1">Encontrado</label>
                        <?php 
                            $qtd_color = ($i['quantidade_encontrada'] < $i['quantidade_ideal']) ? 'text-red-500' : 'text-slate-800';
                        ?>
                        <p class="font-black <?php echo $qtd_color; ?> text-sm"><?php echo $i['quantidade_encontrada']; ?> <span class="text-[10px]"><?php echo $i['unidade']; ?></span></p>
                    </div>
                    <div class="col-span-2 md:col-span-1 border-t md:border-t-0 md:border-l border-slate-50 pt-3 md:pt-0 md:pl-6 text-left md:text-center">
                        <label class="block text-[8px] font-black uppercase text-slate-400 tracking-widest mb-1">Validade Registro</label>
                        <p class="font-bold text-slate-600 text-xs">
                            <?php echo $i['validade_conferida'] ? date('d/m/Y', strtotime($i['validade_conferida'])) : '<span class="text-slate-300">N/A</span>'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($checklist['observacoes'])): ?>
    <div class="mt-8 bg-amber-50 p-8 rounded-[2rem] border border-amber-100">
        <label class="block text-[10px] font-black uppercase text-amber-500 tracking-widest mb-3 flex items-center gap-2">
            <i class="fas fa-exclamation-triangle"></i>
            Observações e Não Conformidades
        </label>
        <p class="text-slate-700 font-bold leading-relaxed"><?php echo nl2br(cleanInput($checklist['observacoes'])); ?></p>
    </div>
    <?php endif; ?>
</div>

<style>
    @media print {
        .print\:hidden { display: none !important; }
        body { background: white !important; }
        .max-w-4xl { max-width: 100% !important; }
        main { padding: 0 !important; }
        .bg-white { box-shadow: none !important; border-color: #eee !important; }
    }
</style>

<?php 
echo "</div></main></body></html>";
?>
