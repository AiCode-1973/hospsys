<?php
require_once __DIR__ . '/../includes/header.php';

// Filtros
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$tipo_filtro = isset($_GET['tipo']) ? cleanInput($_GET['tipo']) : '';

// Paginação
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $limit;

// Query base
$sql = "SELECT * FROM car_itens_mestres WHERE ativo = 1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (nome LIKE ? OR descricao LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($tipo_filtro)) {
    $sql .= " AND tipo = ?";
    $params[] = $tipo_filtro;
}

// Contador para paginação
$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM (" . $sql . ") as t");
$stmt_count->execute($params);
$total_registros = $stmt_count->fetchColumn();
$total_paginas = ceil($total_registros / $limit);

$sql .= " ORDER BY nome ASC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>

<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <a href="car_dashboard.php" class="text-blue-600 font-bold text-xs uppercase tracking-widest flex items-center gap-2 mb-2 hover:gap-3 transition-all">
            <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
        </a>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Catálogo de Itens</h1>
        <p class="text-slate-500">Gerencie os medicamentos e materiais padronizados para os carrinhos.</p>
    </div>
    <button onclick="document.getElementById('modal-item').classList.remove('hidden')" class="w-full md:w-auto px-6 py-3 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 transition-all flex items-center justify-center gap-2 shadow-xl shadow-blue-200">
        <i class="fas fa-plus"></i> Novo Item
    </button>
</div>

<!-- Filtros e Busca -->
<div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm mb-8 flex flex-col md:flex-row gap-4">
    <form method="GET" class="flex-1 flex flex-col md:flex-row gap-4">
        <div class="flex-1 relative">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" name="search" value="<?php echo cleanInput($search); ?>" placeholder="Buscar item..." class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-medium">
        </div>
        <div class="md:w-48">
            <select name="tipo" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-bold text-slate-700 appearance-none">
                <option value="">Todos os Tipos</option>
                <option value="Medicamento" <?php echo $tipo_filtro == 'Medicamento' ? 'selected' : ''; ?>>Medicamento</option>
                <option value="Material" <?php echo $tipo_filtro == 'Material' ? 'selected' : ''; ?>>Material</option>
                <option value="Equipamento" <?php echo $tipo_filtro == 'Equipamento' ? 'selected' : ''; ?>>Equipamento</option>
            </select>
        </div>
        <button type="submit" class="bg-slate-800 text-white px-8 py-3 rounded-2xl font-bold hover:bg-slate-900 transition-all">Filtrar</button>
    </form>
</div>

<div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest font-black">
                <tr>
                    <th class="px-6 py-4">Item</th>
                    <th class="px-6 py-4">Tipo</th>
                    <th class="px-6 py-4">Unidade</th>
                    <th class="px-6 py-4 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($items as $item): ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-700"><?php echo cleanInput($item['nome']); ?></p>
                        <p class="text-xs text-slate-400"><?php echo cleanInput($item['descricao']); ?></p>
                    </td>
                    <td class="px-6 py-4">
                        <?php 
                            $tipo_color = 'bg-slate-100 text-slate-600';
                            if ($item['tipo'] == 'Medicamento') $tipo_color = 'bg-blue-100 text-blue-600';
                            if ($item['tipo'] == 'Material') $tipo_color = 'bg-emerald-100 text-emerald-600';
                            if ($item['tipo'] == 'Equipamento') $tipo_color = 'bg-purple-100 text-purple-600';
                        ?>
                        <span class="px-2.5 py-1 <?php echo $tipo_color; ?> text-[10px] font-black uppercase rounded-lg">
                            <?php echo $item['tipo']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest"><?php echo $item['unidade']; ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick='openEditItem(<?php echo json_encode($item); ?>)' class="w-8 h-8 bg-slate-50 text-slate-400 rounded-lg hover:bg-blue-600 hover:text-white transition-all">
                                <i class="fas fa-edit fa-xs"></i>
                            </button>
                            <a href="car_action.php?acao=excluir_item&id=<?php echo $item['id']; ?>" onclick="return confirm('Desativar este item?')" class="w-8 h-8 bg-slate-50 text-slate-400 rounded-lg hover:bg-red-600 hover:text-white transition-all">
                                <i class="fas fa-trash-alt fa-xs"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Paginação simplificada -->
    <?php if ($total_paginas > 1): ?>
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-center gap-2">
            <?php for ($i=1; $i<=$total_paginas; $i++): ?>
                <a href="?p=<?php echo $i; ?>&search=<?php echo $search; ?>&tipo=<?php echo $tipo_filtro; ?>" 
                   class="w-10 h-10 flex items-center justify-center rounded-xl font-bold text-sm <?php echo $page == $i ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Novo/Editar Item -->
<div id="modal-item" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center">
            <h3 id="modal-title" class="text-2xl font-black text-slate-800 tracking-tight">Novo Item</h3>
            <button onclick="document.getElementById('modal-item').classList.add('hidden')" class="w-10 h-10 bg-slate-100 text-slate-400 hover:text-slate-600 rounded-full flex items-center justify-center transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="car_action.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="acao" value="salvar_item">
            <input type="hidden" name="id" id="item-id" value="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Nome do Item</label>
                <input type="text" name="nome" id="item-nome" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700">
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Tipo</label>
                    <select name="tipo" id="item-tipo" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700 appearance-none">
                        <option value="Medicamento">Medicamento</option>
                        <option value="Material">Material</option>
                        <option value="Equipamento">Equipamento</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Unidade</label>
                    <input type="text" name="unidade" id="item-unidade" required placeholder="Ex: amp, fa, un" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Descrição / Detalhes</label>
                <textarea name="descricao" id="item-descricao" rows="3" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700"></textarea>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="document.getElementById('modal-item').classList.add('hidden')" class="flex-1 px-6 py-4 text-slate-500 font-bold hover:bg-slate-100 rounded-2xl transition-all">Cancelar</button>
                <button type="submit" class="flex-[2] bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-2xl font-bold transition-all shadow-xl shadow-blue-200">
                    Salvar Item
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditItem(item) {
        document.getElementById('modal-title').innerText = 'Editar Item';
        document.getElementById('item-id').value = item.id;
        document.getElementById('item-nome').value = item.nome;
        document.getElementById('item-tipo').value = item.tipo;
        document.getElementById('item-unidade').value = item.unidade;
        document.getElementById('item-descricao').value = item.descricao;
        document.getElementById('modal-item').classList.remove('hidden');
    }

    // Reset modal when closing or opening new
    function resetModal() {
        document.getElementById('modal-title').innerText = 'Novo Item';
        document.getElementById('item-id').value = '';
        document.getElementById('item-nome').value = '';
        document.getElementById('item-tipo').value = 'Material';
        document.getElementById('item-unidade').value = '';
        document.getElementById('item-descricao').value = '';
    }
</script>

<?php 
echo "</div></main></body></html>";
?>
