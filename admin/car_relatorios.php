<?php
require_once __DIR__ . '/../includes/header.php';

// Filtros
$cart_id = isset($_GET['cart_id']) ? (int)$_GET['cart_id'] : null;
$status_filtro = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$data_inicio = isset($_GET['data_inicio']) ? cleanInput($_GET['data_inicio']) : '';
$data_fim = isset($_GET['data_fim']) ? cleanInput($_GET['data_fim']) : '';

// Query de Checklists
$sql = "
    SELECT ch.*, c.nome as carrinho_nome, u.nome as usuario_nome
    FROM car_checklists ch
    JOIN car_carrinhos c ON ch.id_carrinho = c.id
    JOIN usuarios u ON ch.id_usuario = u.id
    WHERE 1=1
";

$params = [];
if ($cart_id) {
    $sql .= " AND ch.id_carrinho = ?";
    $params[] = $cart_id;
}
if ($status_filtro) {
    if ($status_filtro === 'Conforme') $sql .= " AND ch.status_final = 'Conforme'";
    else $sql .= " AND ch.status_final = 'Não Conforme'";
}
if ($data_inicio) {
    $sql .= " AND DATE(ch.data_conferencia) >= ?";
    $params[] = $data_inicio;
}
if ($data_fim) {
    $sql .= " AND DATE(ch.data_conferencia) <= ?";
    $params[] = $data_fim;
}

$sql .= " ORDER BY ch.data_conferencia DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$checklists = $stmt->fetchAll();

// Busca carrinhos para o filtro
$carrinhos = $pdo->query("SELECT id, nome FROM car_carrinhos WHERE ativo = 1 ORDER BY nome ASC")->fetchAll();
?>

<div class="mb-8">
    <a href="car_dashboard.php" class="text-blue-600 font-bold text-xs uppercase tracking-widest flex items-center gap-2 mb-2 hover:gap-3 transition-all">
        <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
    </a>
    <h1 class="text-3xl font-black text-slate-800 tracking-tight">Relatório de Auditorias</h1>
    <p class="text-slate-500">Histórico completo de conferências e conformidades dos carrinhos.</p>
</div>

<!-- Filtros Avançados -->
<div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm mb-8">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Carrinho</label>
            <select name="cart_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold text-slate-700 appearance-none">
                <option value="">Todos os Carrinhos</option>
                <?php foreach ($carrinhos as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $cart_id == $c['id'] ? 'selected' : ''; ?>><?php echo $c['nome']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Status da Auditoria</label>
            <select name="status" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold text-slate-700 appearance-none">
                <option value="">Todos os Status</option>
                <option value="Conforme" <?php echo $status_filtro == 'Conforme' ? 'selected' : ''; ?>>Conforme</option>
                <option value="Não Conforme" <?php echo $status_filtro == 'Não Conforme' ? 'selected' : ''; ?>>Não Conforme</option>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Período de</label>
            <input type="date" name="data_inicio" value="<?php echo $data_inicio; ?>" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold text-slate-700">
        </div>
        <div class="flex gap-2">
            <div class="flex-grow">
                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Até</label>
                <input type="date" name="data_fim" value="<?php echo $data_fim; ?>" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold text-slate-700">
            </div>
            <button type="submit" class="bg-slate-800 text-white px-6 py-3 rounded-2xl font-bold hover:bg-slate-900 transition-all self-end">Filtrar</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest font-black">
                <tr>
                    <th class="px-6 py-4">Data/Hora</th>
                    <th class="px-6 py-4">Carrinho</th>
                    <th class="px-6 py-4">Auditor</th>
                    <th class="px-6 py-4">Tipo</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($checklists)): ?>
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 font-bold">Nenhuma auditoria encontrada.</td></tr>
                <?php endif; ?>

                <?php foreach ($checklists as $ch): ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-700 text-sm"><?php echo date('d/m/Y', strtotime($ch['data_conferencia'])); ?></p>
                        <p class="text-[10px] text-slate-400 font-bold"><?php echo date('H:i', strtotime($ch['data_conferencia'])); ?></p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-black text-slate-800 uppercase"><?php echo cleanInput($ch['carrinho_nome']); ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-bold text-slate-600"><?php echo cleanInput($ch['usuario_nome']); ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-[10px] font-black uppercase text-slate-400"><?php echo $ch['tipo']; ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <?php 
                            $status_color = ($ch['status_final'] === 'Conforme') ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600';
                        ?>
                        <span class="px-2.5 py-1 <?php echo $status_color; ?> text-[10px] font-black uppercase rounded-lg">
                            <?php echo $ch['status_final']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center">
                            <button onclick="alert('Funcionalidade de detalhamento em breve')" class="w-8 h-8 bg-slate-100 text-slate-400 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                <i class="fas fa-eye fa-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
echo "</div></main></body></html>";
?>
