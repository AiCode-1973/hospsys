<?php
require_once __DIR__ . '/../includes/header.php';

// Filtros
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$setor_id = isset($_GET['setor']) ? (int)$_GET['setor'] : null;

// Query para buscar carrinhos
$sql = "
    SELECT c.*, s.nome as setor_nome,
           (SELECT COUNT(*) FROM car_checklists WHERE id_carrinho = c.id) as total_checklists,
           (SELECT MAX(data_conferencia) FROM car_checklists WHERE id_carrinho = c.id) as ultima_conferencia
    FROM car_carrinhos c
    LEFT JOIN fugulin_setores s ON c.id_setor = s.id
    WHERE c.ativo = 1
";

$params = [];
if (!empty($search)) {
    $sql .= " AND (c.nome LIKE ? OR c.localizacao LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($setor_id) {
    $sql .= " AND c.id_setor = ?";
    $params[] = $setor_id;
}

$sql .= " ORDER BY c.status DESC, c.nome ASC"; // Críticos primeiro

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$carrinhos = $stmt->fetchAll();

// Estatísticas para o topo
$total_carrinhos = count($carrinhos);
$criticos = 0;
$atencao = 0;
foreach ($carrinhos as $c) {
    if ($c['status'] === 'Crítico') $criticos++;
    if ($c['status'] === 'Atenção') $atencao++;
}

// Busca setores para o filtro
$setores = $pdo->query("SELECT * FROM fugulin_setores ORDER BY nome ASC")->fetchAll();
?>

<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Carrinhos de Emergência</h1>
        <p class="text-slate-500">Gestão de suprimentos e checklist de prontidão assistencial.</p>
    </div>
    <div class="flex gap-3 w-full md:w-auto">
        <a href="car_relatorios.php" class="flex-1 md:flex-none px-5 py-3 bg-white border border-slate-200 text-slate-600 rounded-2xl font-bold hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
            <i class="fas fa-file-alt"></i> Relatórios
        </a>
        <a href="car_itens.php" class="flex-1 md:flex-none px-5 py-3 bg-white border border-slate-200 text-slate-600 rounded-2xl font-bold hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
            <i class="fas fa-box-open"></i> Itens
        </a>
        <button onclick="document.getElementById('modal-novo').classList.remove('hidden')" class="flex-1 md:flex-none px-6 py-3 bg-slate-800 text-white rounded-2xl font-bold hover:bg-slate-900 transition-all flex items-center justify-center gap-2 shadow-xl shadow-slate-200">
            <i class="fas fa-plus"></i> Novo Carrinho
        </button>
    </div>
</div>

<!-- Grid de Estatísticas Rápidas -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-5">
        <div class="w-14 h-14 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center text-2xl">
            <i class="fas fa-ambulance"></i>
        </div>
        <div>
            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest leading-none mb-2">Total de Carrinhos</p>
            <h3 class="text-2xl font-black text-slate-800 leading-none"><?php echo $total_carrinhos; ?></h3>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-5">
        <div class="w-14 h-14 <?php echo $criticos > 0 ? 'bg-red-50 text-red-500 animate-pulse' : 'bg-green-50 text-green-500'; ?> rounded-2xl flex items-center justify-center text-2xl">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest leading-none mb-2">Status Crítico</p>
            <h3 class="text-2xl font-black <?php echo $criticos > 0 ? 'text-red-600' : 'text-slate-800'; ?> leading-none"><?php echo $criticos; ?></h3>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-5">
        <div class="w-14 h-14 <?php echo $atencao > 0 ? 'bg-amber-50 text-amber-500' : 'bg-blue-50 text-blue-500'; ?> rounded-2xl flex items-center justify-center text-2xl">
            <i class="fas fa-clock"></i>
        </div>
        <div>
            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest leading-none mb-2">Em Atenção</p>
            <h3 class="text-2xl font-black text-slate-800 leading-none"><?php echo $atencao; ?></h3>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm mb-8">
    <form method="GET" class="flex flex-col md:flex-row gap-4">
        <div class="flex-1 relative">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" name="search" value="<?php echo cleanInput($search); ?>" placeholder="Buscar por nome ou localização..." class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-medium">
        </div>
        <div class="md:w-64">
            <select name="setor" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-bold text-slate-700 appearance-none">
                <option value="">Todos os Setores</option>
                <?php foreach ($setores as $s): ?>
                    <option value="<?php echo $s['id']; ?>" <?php echo $setor_id == $s['id'] ? 'selected' : ''; ?>><?php echo $s['nome']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="bg-slate-800 text-white px-8 py-3 rounded-2xl font-bold hover:bg-slate-900 transition-all">Filtrar</button>
        <?php if ($search || $setor_id): ?>
            <a href="car_dashboard.php" class="px-6 py-3 bg-slate-100 text-slate-600 rounded-2xl font-bold flex items-center justify-center">Limpar</a>
        <?php endif; ?>
    </form>
</div>

<!-- Grid de Carrinhos -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php if (empty($carrinhos)): ?>
        <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-slate-100">
            <div class="w-20 h-20 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-search text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-400">Nenhum carrinho encontrado</h3>
            <p class="text-slate-400 text-sm">Tente ajustar seus filtros ou cadastre um novo carrinho.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($carrinhos as $c): ?>
        <?php 
            $status_class = "border-green-100 bg-green-50 text-green-600";
            if ($c['status'] === 'Atenção') $status_class = "border-amber-100 bg-amber-50 text-amber-600";
            if ($c['status'] === 'Crítico') $status_class = "border-red-100 bg-red-50 text-red-600 pulse-red";
        ?>
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden group hover:shadow-xl hover:shadow-slate-200 transition-all duration-300">
            <div class="p-6">
                <!-- Status Badge -->
                <div class="flex justify-between items-start mb-6">
                    <span class="px-3 py-1.5 rounded-xl border <?php echo $status_class; ?> text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                        <span class="w-2 h-2 <?php echo str_replace('text-', 'bg-', explode(' ', $status_class)[2]); ?> rounded-full"></span>
                        <?php echo $c['status']; ?>
                    </span>
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick='openQuickView(<?php echo $c["id"]; ?>, "<?php echo cleanInput($c["nome"]); ?>")' class="w-8 h-8 bg-slate-100 text-slate-400 rounded-lg hover:bg-slate-800 hover:text-white transition-all shadow-sm" title="Visualizar Itens"><i class="fas fa-eye fa-xs"></i></button>
                        <button onclick='openEdit(<?php echo json_encode($c); ?>)' class="w-8 h-8 bg-slate-100 text-slate-400 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Editar Configurações"><i class="fas fa-edit fa-xs"></i></button>
                        <button onclick="openQR('<?php echo cleanInput($c['nome']); ?>', '<?php echo $c['qr_code_token']; ?>')" class="w-8 h-8 bg-slate-100 text-slate-400 rounded-lg hover:bg-slate-800 hover:text-white transition-all shadow-sm" title="QR Code">
                            <i class="fas fa-qrcode fa-xs"></i>
                        </button>
                    </div>
                </div>

                <h3 class="text-xl font-black text-slate-800 mb-1"><?php echo cleanInput($c['nome']); ?></h3>
                <p class="text-xs text-slate-500 font-bold flex items-center gap-2 mb-6">
                    <i class="fas fa-map-marker-alt text-blue-500"></i>
                    <?php echo cleanInput($c['setor_nome'] ?? 'Setor Indefinido'); ?> - <?php echo cleanInput($c['localizacao']); ?>
                </p>

                <div class="grid grid-cols-2 gap-4 pt-6 border-t border-slate-50">
                    <div>
                        <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Última Conferência</p>
                        <p class="text-xs font-bold text-slate-700">
                            <?php echo $c['ultima_conferencia'] ? date('d/m/Y', strtotime($c['ultima_conferencia'])) : 'Nunca realizada'; ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Checklists Efetuados</p>
                        <p class="text-xs font-bold text-slate-700"><?php echo $c['total_checklists']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas inferiros -->
            <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex gap-3">
                <a href="car_conferencia.php?id=<?php echo $c['id']; ?>" class="flex-1 bg-white border border-slate-200 text-slate-700 py-2.5 rounded-xl text-xs font-bold hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-clipboard-check"></i> Conferir
                </a>
                <a href="car_estoque.php?id=<?php echo $c['id']; ?>" class="flex-1 bg-white border border-slate-200 text-slate-700 py-2.5 rounded-xl text-xs font-bold hover:bg-slate-800 hover:text-white hover:border-slate-800 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-list-ul"></i> Estoque
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal Novo Carrinho -->
<div id="modal-novo" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">Novo Carrinho</h3>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Cadastro de Unidade Móvel</p>
            </div>
            <button onclick="document.getElementById('modal-novo').classList.add('hidden')" class="w-10 h-10 bg-slate-100 text-slate-400 hover:text-slate-600 rounded-full flex items-center justify-center transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="car_action.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="acao" value="criar_carrinho">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Identificação do Carrinho</label>
                <input type="text" name="nome" required placeholder="Ex: Emergência UTI Posto 1" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Setor Responsável</label>
                    <select name="id_setor" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700 appearance-none">
                        <option value="">Selecione...</option>
                        <?php foreach ($setores as $s): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo $s['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Localização Específica</label>
                    <input type="text" name="localizacao" placeholder="Ex: Sala Vermelha" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700">
                </div>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="document.getElementById('modal-novo').classList.add('hidden')" class="flex-1 px-6 py-4 text-slate-500 font-bold hover:bg-slate-100 rounded-2xl transition-all">Cancelar</button>
                <button type="submit" class="flex-[2] bg-slate-800 hover:bg-slate-900 text-white px-8 py-4 rounded-2xl font-bold transition-all shadow-xl shadow-slate-200">
                    Cadastrar Carrinho
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Carrinho -->
<div id="modal-editar" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">Editar Carrinho</h3>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Atualizar Unidade Móvel</p>
            </div>
            <button onclick="document.getElementById('modal-editar').classList.add('hidden')" class="w-10 h-10 bg-slate-100 text-slate-400 hover:text-slate-600 rounded-full flex items-center justify-center transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="car_action.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="acao" value="editar_carrinho">
            <input type="hidden" id="edit-id" name="id">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Identificação do Carrinho</label>
                <input type="text" id="edit-nome" name="nome" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Setor Responsável</label>
                    <select id="edit-id_setor" name="id_setor" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700 appearance-none">
                        <?php foreach ($setores as $s): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo $s['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Localização Específica</label>
                    <input type="text" id="edit-localizacao" name="localizacao" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700">
                </div>
            </div>

            <div class="pt-4 flex flex-col gap-3">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white px-8 py-4 rounded-2xl font-bold transition-all shadow-xl shadow-slate-200">
                    Salvar Alterações
                </button>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modal-editar').classList.add('hidden')" class="flex-1 px-6 py-4 text-slate-500 font-bold hover:bg-slate-100 rounded-2xl transition-all">Cancelar</button>
                    <a id="btn-excluir" href="#" onclick="return confirm('Tem certeza que deseja remover este carrinho?')" class="flex-1 px-6 py-4 text-red-500 font-bold hover:bg-red-50 rounded-2xl transition-all text-center">Remover</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Visualização Rápida de Itens -->
<div id="modal-quick-view" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 id="quick-view-title" class="text-2xl font-black text-slate-800 tracking-tight">Itens do Carrinho</h3>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Listagem Completa de Medicamentos e Materiais</p>
            </div>
            <button onclick="document.getElementById('modal-quick-view').classList.add('hidden')" class="w-10 h-10 bg-white shadow-sm text-slate-400 hover:text-slate-600 rounded-full flex items-center justify-center transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-0 max-h-[60vh] overflow-y-auto custom-scrollbar">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-widest sticky top-0 z-10">
                    <tr>
                        <th class="px-8 py-4">Item</th>
                        <th class="px-4 py-4 text-center">Gaveta</th>
                        <th class="px-4 py-4 text-center">Padrao</th>
                        <th class="px-8 py-4 text-center">Atual</th>
                    </tr>
                </thead>
                <tbody id="quick-view-body" class="divide-y divide-slate-100">
                    <!-- Conteúdo inserido via JS -->
                </tbody>
            </table>
            <div id="quick-view-loading" class="p-12 text-center text-slate-400 hidden">
                <i class="fas fa-circle-notch fa-spin text-2xl mb-2"></i>
                <p class="text-xs font-bold uppercase tracking-widest">Carregando estoque...</p>
            </div>
        </div>
        <div class="p-8 border-t border-slate-100 bg-slate-50/50 flex justify-end">
            <button onclick="document.getElementById('modal-quick-view').classList.add('hidden')" class="px-8 py-3 bg-slate-800 text-white rounded-2xl font-bold hover:bg-slate-900 transition-all shadow-lg">Fechar</button>
        </div>
    </div>
</div>

    </div>
</div>

<!-- Modal QR Code -->
<div id="modal-qrcode" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in duration-300">
        <div class="p-8 text-center">
            <h3 id="qr-title" class="text-xl font-black text-slate-800 mb-2">QR Code</h3>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mb-6">Acesso Rápido via Mobile</p>
            
            <div id="qr-container" class="bg-slate-50 p-6 rounded-3xl inline-block mb-6 border border-slate-100">
                <!-- Imagem do QR Code será inserida aqui -->
            </div>

            <p class="text-[10px] text-slate-400 font-medium px-4 mb-8">
                Imprima este código e fixe-o no carrinho para que a equipe possa realizar a conferência via smartphone.
            </p>

            <div class="flex gap-3">
                <button onclick="document.getElementById('modal-qrcode').classList.add('hidden')" class="flex-1 px-6 py-4 text-slate-500 font-bold hover:bg-slate-100 rounded-2xl transition-all">Fechar</button>
                <button onclick="window.print()" class="flex-1 bg-slate-800 text-white px-6 py-4 rounded-2xl font-bold hover:bg-slate-900 transition-all">Imprimir</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openQR(nome, token) {
        document.getElementById('qr-title').innerText = nome;
        
        // Cálculo mais robusto da URL
        const currentPath = window.location.pathname;
        const basePath = currentPath.substring(0, currentPath.lastIndexOf('/') + 1);
        const urlFinal = window.location.origin + basePath + 'car_conferencia.php?token=' + token;
        
        // Uso de uma API de QR Code mais estável
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(urlFinal)}`;
        
        document.getElementById('qr-container').innerHTML = `<img src="${qrUrl}" alt="QR Code" class="w-48 h-48 mx-auto shadow-sm rounded-lg">`;
        document.getElementById('modal-qrcode').classList.remove('hidden');
    }

    function openEdit(carrinho) {
        document.getElementById('edit-id').value = carrinho.id;
        document.getElementById('edit-nome').value = carrinho.nome;
        document.getElementById('edit-id_setor').value = carrinho.id_setor;
        document.getElementById('edit-localizacao').value = carrinho.localizacao;
        document.getElementById('btn-excluir').href = `car_action.php?acao=excluir_carrinho&id=${carrinho.id}`;
        
        document.getElementById('modal-editar').classList.remove('hidden');
    }

    async function openQuickView(id, nome) {
        document.getElementById('quick-view-title').innerText = nome;
        const body = document.getElementById('quick-view-body');
        const loading = document.getElementById('quick-view-loading');
        
        body.innerHTML = '';
        loading.classList.remove('hidden');
        document.getElementById('modal-quick-view').classList.remove('hidden');

        try {
            const response = await fetch(`car_get_estoque_json.php?id=${id}`);
            const data = await response.json();
            
            loading.classList.add('hidden');
            
            if (data.length === 0) {
                body.innerHTML = '<tr><td colspan="4" class="px-8 py-12 text-center text-slate-400">Nenhum item padronizado neste carrinho.</td></tr>';
                return;
            }

            data.forEach(item => {
                const statusColor = (parseInt(item.quantidade_atual) < parseInt(item.quantidade_ideal)) ? 'text-red-500' : 'text-green-500';
                const row = `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-8 py-4">
                            <div class="text-sm font-bold text-slate-700">${item.nome}</div>
                            <div class="text-[9px] font-black uppercase text-slate-400">${item.tipo}</div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="px-2 py-1 bg-slate-100 text-slate-500 text-[9px] font-black rounded-lg border border-slate-200">G${item.gaveta}</span>
                        </td>
                        <td class="px-4 py-4 text-center text-xs font-bold text-slate-400">${item.quantidade_ideal}</td>
                        <td class="px-8 py-4 text-center">
                            <span class="text-sm font-black ${statusColor}">${item.quantidade_atual || 0}</span>
                        </td>
                    </tr>
                `;
                body.insertAdjacentHTML('beforeend', row);
            });
        } catch (error) {
            loading.classList.add('hidden');
            body.innerHTML = '<tr><td colspan="4" class="px-8 py-12 text-center text-red-500">Erro ao carregar dados.</td></tr>';
        }
    }
</script>

<style>
    @keyframes pulse-red {
        0% { border-color: rgba(239, 68, 68, 0.2); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.1); }
        50% { border-color: rgba(239, 68, 68, 0.5); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        100% { border-color: rgba(239, 68, 68, 0.2); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    .pulse-red {
        animation: pulse-red 2s infinite;
    }
</style>

<?php 
echo "</div></main></body></html>";
?>
