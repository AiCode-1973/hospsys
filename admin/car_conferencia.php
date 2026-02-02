<?php
require_once __DIR__ . '/../includes/header.php';

$id_carrinho = isset($_GET['id']) ? (int)$_GET['id'] : null;
$token = isset($_GET['token']) ? cleanInput($_GET['token']) : null;

if (!$id_carrinho && !$token) {
    $_SESSION['mensagem_erro'] = "Carrinho não especificado.";
    redirect('car_dashboard.php');
}

// Busca dados do carrinho (por id ou token)
if ($token) {
    $stmt = $pdo->prepare("SELECT c.*, s.nome as setor_nome FROM car_carrinhos c LEFT JOIN fugulin_setores s ON c.id_setor = s.id WHERE c.qr_code_token = ?");
    $stmt->execute([$token]);
} else {
    $stmt = $pdo->prepare("SELECT c.*, s.nome as setor_nome FROM car_carrinhos c LEFT JOIN fugulin_setores s ON c.id_setor = s.id WHERE c.id = ?");
    $stmt->execute([$id_carrinho]);
}

$carrinho = $stmt->fetch();

if (!$carrinho) {
    $_SESSION['mensagem_erro'] = "Carrinho não encontrado.";
    redirect('car_dashboard.php');
}

$id_carrinho = $carrinho['id']; 

// Busca itens padronizados para este carrinho
$sql = "
    SELECT i.id as item_id, i.nome, i.tipo, i.unidade,
           comp.quantidade_ideal, comp.quantidade_minima,
           est.quantidade_atual, est.lote, est.data_validade
    FROM car_itens_mestres i
    JOIN car_composicao_ideal comp ON i.id = comp.id_item
    LEFT JOIN car_estoque_atual est ON (i.id = est.id_item AND est.id_carrinho = comp.id_carrinho)
    WHERE comp.id_carrinho = ? AND i.ativo = 1
    ORDER BY i.tipo, i.nome
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_carrinho]);
$itens = $stmt->fetchAll();

if (empty($itens)) {
    $_SESSION['mensagem_erro'] = "Este carrinho ainda não foi padronizado. Defina os itens no Estoque primeiro.";
    redirect("car_estoque.php?id=$id_carrinho");
}
?>

<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <a href="car_dashboard.php" class="text-blue-600 font-bold text-xs uppercase tracking-widest flex items-center gap-2 mb-2 hover:gap-3 transition-all">
            <i class="fas fa-arrow-left"></i> Sair da Conferência
        </a>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Checklist de Auditoria</h1>
        <p class="text-slate-500 font-bold"><?php echo cleanInput($carrinho['nome']); ?> - <?php echo cleanInput($carrinho['setor_nome']); ?></p>
    </div>

    <form action="car_action.php" method="POST" class="space-y-6 pb-24">
        <input type="hidden" name="acao" value="salvar_checklist">
        <input type="hidden" name="id_carrinho" value="<?php echo $id_carrinho; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <!-- Metadados da Conferência -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Tipo de Conferência</label>
                <select name="tipo_checklist" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none font-bold text-slate-700 appearance-none">
                    <option value="Mensal">Mensal (Preventiva)</option>
                    <option value="Pós-Uso">Pós-Uso (Reposição)</option>
                    <option value="Diário">Monitoramento Diário</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Auditado por</label>
                <div class="w-full px-5 py-4 bg-slate-100 border border-slate-200 rounded-2xl font-bold text-slate-500 flex items-center gap-2">
                    <i class="fas fa-user-circle"></i> <?php echo $_SESSION['user_nome']; ?>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-4">Itens a serem conferidos</h3>
            
            <?php foreach ($itens as $i): ?>
                <div class="item-card bg-white p-5 rounded-3xl border border-slate-100 shadow-sm hover:border-blue-200 transition-all">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex-1">
                            <span class="text-[9px] font-black uppercase tracking-widest <?php echo ($i['tipo'] == 'Medicamento') ? 'text-blue-500' : 'text-emerald-500'; ?>">
                                <?php echo $i['tipo']; ?>
                            </span>
                            <h4 class="text-lg font-black text-slate-800 leading-tight"><?php echo cleanInput($i['nome']); ?></h4>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-tighter">Padrão: <span class="text-slate-600"><?php echo $i['quantidade_ideal']; ?> <?php echo $i['unidade']; ?></span></p>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="text-center group">
                                <label class="block text-[8px] font-black uppercase text-slate-400 tracking-widest mb-1">Qtd Atual</label>
                                <input type="number" name="item_qtd[<?php echo $i['item_id']; ?>]" value="<?php echo $i['quantidade_atual'] ?? $i['quantidade_ideal']; ?>" 
                                       class="w-16 px-2 py-2 bg-slate-50 border border-slate-200 rounded-xl text-center font-black text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            </div>
                            <div class="flex-1">
                                <label class="block text-[8px] font-black uppercase text-slate-400 tracking-widest mb-1">Lote</label>
                                <input type="text" name="item_lote[<?php echo $i['item_id']; ?>]" value="<?php echo cleanInput($i['lote'] ?? ''); ?>" placeholder="Lote"
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div class="flex-1">
                                <label class="block text-[8px] font-black uppercase text-slate-400 tracking-widest mb-1">Validade</label>
                                <input type="date" name="item_validade[<?php echo $i['item_id']; ?>]" value="<?php echo $i['data_validade']; ?>"
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Observações Gerais / Não Conformidades</label>
            <textarea name="observacoes" rows="4" placeholder="Ex: Faltam 2 ampolas de Adrenalina, solicitada reposição ao almoxarifado..." class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none font-bold text-slate-700"></textarea>
        </div>

        <!-- Barra de Ações Fixa no Rodapé -->
        <div class="fixed bottom-0 left-0 right-0 md:left-64 p-4 md:p-6 bg-white/80 backdrop-blur-md border-t border-slate-100 flex justify-center z-40">
            <button type="submit" class="w-full max-w-lg bg-blue-600 hover:bg-blue-700 text-white px-8 py-5 rounded-[2rem] font-black uppercase tracking-widest transition-all shadow-2xl shadow-blue-200 flex items-center justify-center gap-3">
                <i class="fas fa-check-double text-xl"></i>
                Finalizar Auditoria
            </button>
        </div>
    </form>
</div>

<style>
    /* Estilo para focar o card quando o input estiver em foco */
    .item-card:has(input:focus) {
        border-color: #3b82f6;
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.1);
        transform: translateY(-2px);
    }
</style>

<?php 
echo "</div></main></body></html>";
?>
