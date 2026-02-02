<?php
require_once __DIR__ . '/../includes/header.php';

$id_carrinho = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id_carrinho) {
    $_SESSION['mensagem_erro'] = "Carrinho não especificado.";
    redirect('car_dashboard.php');
}

// Busca dados do carrinho
$stmt = $pdo->prepare("SELECT c.*, s.nome as setor_nome FROM car_carrinhos c LEFT JOIN fugulin_setores s ON c.id_setor = s.id WHERE c.id = ?");
$stmt->execute([$id_carrinho]);
$carrinho = $stmt->fetch();

if (!$carrinho) {
    $_SESSION['mensagem_erro'] = "Carrinho não encontrado.";
    redirect('car_dashboard.php');
}

// Busca Composição Ideal vs Estoque Atual
$sql = "
    SELECT i.id as item_id, i.nome, i.tipo, i.unidade,
           comp.quantidade_ideal, comp.quantidade_minima,
           est.quantidade_atual, est.lote, est.data_validade, est.id as estoque_id
    FROM car_itens_mestres i
    JOIN car_composicao_ideal comp ON i.id = comp.id_item
    LEFT JOIN car_estoque_atual est ON (i.id = est.id_item AND est.id_carrinho = comp.id_carrinho)
    WHERE comp.id_carrinho = ? AND i.ativo = 1
    ORDER BY i.tipo, i.nome
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_carrinho]);
$itens_estoque = $stmt->fetchAll();

// Busca todos os itens mestres para o modal de composição
$todos_itens = $pdo->query("SELECT id, nome, tipo FROM car_itens_mestres WHERE ativo = 1 ORDER BY nome ASC")->fetchAll();
?>

<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <a href="car_dashboard.php" class="text-blue-600 font-bold text-xs uppercase tracking-widest flex items-center gap-2 mb-2 hover:gap-3 transition-all">
            <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
        </a>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight"><?php echo cleanInput($carrinho['nome']); ?></h1>
        <p class="text-slate-500 font-bold flex items-center gap-2">
            <i class="fas fa-map-marker-alt text-blue-500"></i>
            <?php echo cleanInput($carrinho['setor_nome']); ?> - <?php echo cleanInput($carrinho['localizacao']); ?>
        </p>
    </div>
    <div class="flex gap-3 w-full md:w-auto">
        <button onclick="document.getElementById('modal-composicao').classList.remove('hidden')" class="flex-1 md:flex-none px-5 py-3 bg-white border border-slate-200 text-slate-600 rounded-2xl font-bold hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
            <i class="fas fa-cog"></i> Padronizar Itens
        </button>
        <button onclick="document.getElementById('modal-reposicao').classList.remove('hidden')" class="flex-1 md:flex-none px-6 py-3 bg-slate-800 text-white rounded-2xl font-bold hover:bg-slate-900 transition-all flex items-center justify-center gap-2 shadow-xl shadow-slate-200">
            <i class="fas fa-plus"></i> Reposição Rápida
        </button>
    </div>
</div>

<div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
        <h3 class="font-bold text-slate-800 tracking-tight">Inventário Atual</h3>
        <p class="text-xs text-slate-500">Comparativo entre o estoque físico e o padrão estabelecido.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest font-black">
                <tr>
                    <th class="px-6 py-4">Item</th>
                    <th class="px-6 py-4 text-center">Ideal</th>
                    <th class="px-6 py-4 text-center">Mínimo</th>
                    <th class="px-6 py-4 text-center">Atual</th>
                    <th class="px-6 py-4">Lote / Validade</th>
                    <th class="px-6 py-4 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($itens_estoque)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium">
                            Nenhum item padronizado para este carrinho.<br>
                            Clique em <strong class="text-blue-600">"Padronizar Itens"</strong> para começar.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($itens_estoque as $i): ?>
                <?php 
                    $estoque_status = "OK";
                    $status_color = "bg-green-100 text-green-600";
                    
                    if ($i['quantidade_atual'] < $i['quantidade_minima']) {
                        $estoque_status = "Crítico";
                        $status_color = "bg-red-100 text-red-600";
                    } elseif ($i['quantidade_atual'] < $i['quantidade_ideal']) {
                        $estoque_status = "Incompleto";
                        $status_color = "bg-amber-100 text-amber-600";
                    }

                    // Verifica validade
                    $validade_alerta = false;
                    if ($i['data_validade']) {
                        $dias_restantes = (strtotime($i['data_validade']) - time()) / 86400;
                        if ($dias_restantes < 30) {
                            $validade_alerta = "vencido";
                            $status_color = "bg-red-600 text-white";
                        } elseif ($dias_restantes < 90) {
                            $validade_alerta = "vencendo";
                            $status_color = "bg-amber-500 text-white";
                        }
                    }
                ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-700 text-sm"><?php echo cleanInput($i['nome']); ?></p>
                        <p class="text-[10px] text-slate-400 uppercase font-black tracking-tighter"><?php echo $i['tipo']; ?> (<?php echo $i['unidade']; ?>)</p>
                    </td>
                    <td class="px-6 py-4 text-center font-bold text-slate-400"><?php echo $i['quantidade_ideal']; ?></td>
                    <td class="px-6 py-4 text-center font-bold text-slate-400"><?php echo $i['quantidade_minima']; ?></td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-lg font-black text-slate-800"><?php echo $i['quantidade_atual'] ?? 0; ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($i['lote']): ?>
                            <p class="text-xs font-bold text-slate-600">Lote: <?php echo cleanInput($i['lote']); ?></p>
                            <p class="text-xs font-bold <?php echo ($validade_alerta === 'vencido') ? 'text-red-500' : (($validade_alerta === 'vencendo') ? 'text-amber-500' : 'text-slate-400'); ?>">
                                Val: <?php echo date('d/m/Y', strtotime($i['data_validade'])); ?>
                            </p>
                        <?php else: ?>
                            <span class="text-[10px] text-slate-300 italic">Não informado</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 <?php echo $status_color; ?> text-[10px] font-black uppercase rounded-lg">
                            <?php echo $validade_alerta ?: $estoque_status; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Padronizar/Composição -->
<div id="modal-composicao" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in slide-in-from-bottom duration-300">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">Padronização</h3>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Defina os itens e quantidades ideais</p>
            </div>
            <button onclick="document.getElementById('modal-composicao').classList.add('hidden')" class="w-10 h-10 bg-slate-100 text-slate-400 hover:text-slate-600 rounded-full flex items-center justify-center transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="car_action.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="acao" value="salvar_composicao">
            <input type="hidden" name="id_carrinho" value="<?php echo $id_carrinho; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="bg-slate-50 p-6 rounded-3xl space-y-4 max-h-[400px] overflow-y-auto custom-scrollbar">
                <div class="grid grid-cols-12 gap-4 text-[9px] font-black uppercase text-slate-400 tracking-widest px-2 group">
                    <div class="col-span-6">Item do Catálogo</div>
                    <div class="col-span-3 text-center">Ideal</div>
                    <div class="col-span-3 text-center">Mínimo</div>
                </div>

                <div id="container-itens" class="space-y-3">
                    <?php if (empty($itens_estoque)): ?>
                        <div class="item-row grid grid-cols-12 gap-4 items-center">
                            <div class="col-span-6">
                                <select name="item_id[]" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Selecione um item...</option>
                                    <?php foreach ($todos_itens as $ti): ?>
                                        <option value="<?php echo $ti['id']; ?>"><?php echo cleanInput($ti['nome']); ?> (<?php echo $ti['tipo']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-span-3">
                                <input type="number" name="qtd_ideal[]" value="10" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-center text-slate-700 outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-3">
                                <input type="number" name="qtd_minima[]" value="5" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-center text-slate-700 outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach($itens_estoque as $ie): ?>
                            <div class="item-row grid grid-cols-12 gap-4 items-center">
                                <div class="col-span-6">
                                    <select name="item_id[]" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none">
                                        <option value="<?php echo $ie['item_id']; ?>" selected><?php echo cleanInput($ie['nome']); ?></option>
                                        <?php foreach ($todos_itens as $ti): ?>
                                            <?php if($ti['id'] != $ie['item_id']): ?>
                                                <option value="<?php echo $ti['id']; ?>"><?php echo cleanInput($ti['nome']); ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <input type="number" name="qtd_ideal[]" value="<?php echo $ie['quantidade_ideal']; ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-center text-slate-700">
                                </div>
                                <div class="col-span-3">
                                    <input type="number" name="qtd_minima[]" value="<?php echo $ie['quantidade_minima']; ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-center text-slate-700">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <button type="button" onclick="adicionarLinha()" class="w-full py-3 border-2 border-dashed border-slate-200 rounded-2xl text-slate-400 text-xs font-bold hover:bg-slate-100 hover:border-slate-300 transition-all">
                    <i class="fas fa-plus mr-2"></i> Adicionar outro item ao padrão
                </button>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="document.getElementById('modal-composicao').classList.add('hidden')" class="flex-1 px-6 py-4 text-slate-500 font-bold hover:bg-slate-100 rounded-2xl transition-all">Cancelar</button>
                <button type="submit" class="flex-[2] bg-slate-800 hover:bg-slate-900 text-white px-8 py-4 rounded-2xl font-bold transition-all shadow-xl shadow-slate-200">
                    Salvar Padronização
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function adicionarLinha() {
        const container = document.getElementById('container-itens');
        const original = container.querySelector('.item-row');
        const nova = original.cloneNode(true);
        
        // Limpa valores dos inputs na nova linha
        nova.querySelectorAll('input').forEach(input => input.value = '');
        nova.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        
        container.appendChild(nova);
    }
</script>

<?php 
echo "</div></main></body></html>";
?>
