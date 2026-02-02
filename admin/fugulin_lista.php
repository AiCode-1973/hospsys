<?php
require_once __DIR__ . '/../includes/header.php';

// Busca todos os setores para o filtro
$setores = $pdo->query("SELECT * FROM fugulin_setores ORDER BY nome ASC")->fetchAll();

// Filtros
$search_patient = isset($_GET['search_patient']) ? cleanInput($_GET['search_patient']) : '';
$filter_sector = isset($_GET['filter_sector']) ? (int)$_GET['filter_sector'] : null;

// Query base com filtros
$sql = "
    SELECT p.id, p.nome, p.prontuario, p.data_cadastro,
           c.classificacao, c.total_pontos, c.data_registro, c.id as last_class_id,
           u.nome as profissional, s.nome as setor, l.descricao as leito,
           (SELECT COUNT(*) FROM fugulin_classificacoes WHERE id_paciente = p.id) as total_historico
    FROM fugulin_pacientes p
    LEFT JOIN fugulin_classificacoes c ON c.id = (
        SELECT id FROM fugulin_classificacoes 
        WHERE id_paciente = p.id 
        ORDER BY data_registro DESC LIMIT 1
    )
    LEFT JOIN usuarios u ON c.id_usuario = u.id
    LEFT JOIN fugulin_setores s ON c.id_setor = s.id
    LEFT JOIN fugulin_leitos l ON c.id_leito = l.id
    WHERE p.ativo = 1
";

$params = [];

if (!empty($search_patient)) {
    $sql .= " AND p.nome LIKE ?";
    $params[] = "%$search_patient%";
}

if ($filter_sector) {
    $sql .= " AND s.id = ?";
    $params[] = $filter_sector;
}

$sql .= " ORDER BY p.nome ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pacientes = $stmt->fetchAll();

?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
    <div>
        <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight">Pacientes Fugulin</h1>
        <p class="text-sm text-slate-500">Acompanhamento centralizado por paciente.</p>
    </div>
    <a href="fugulin_novo.php" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-500/20">
        <i class="fas fa-plus"></i>
        Nova Classificação
    </a>
</div>

<!-- Barra de Filtros -->
<div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-4">
        <div class="flex-grow relative">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" name="search_patient" value="<?php echo cleanInput($search_patient); ?>" placeholder="Buscar por nome do paciente..." class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-medium">
        </div>
        <div class="md:w-64 relative">
            <i class="fas fa-hospital absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 z-10"></i>
            <select name="filter_sector" class="w-full pl-12 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-bold text-slate-700 appearance-none">
                <option value="">Todos os Setores</option>
                <?php foreach ($setores as $s): ?>
                    <option value="<?php echo $s['id']; ?>" <?php echo $filter_sector == $s['id'] ? 'selected' : ''; ?>><?php echo $s['nome']; ?></option>
                <?php endforeach; ?>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                <i class="fas fa-chevron-down text-xs"></i>
            </div>
        </div>
        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-8 py-3 rounded-2xl font-bold transition-all flex items-center justify-center gap-2">
            Filtrar
        </button>
        <?php if (!empty($search_patient) || $filter_sector): ?>
            <a href="fugulin_lista.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-3 rounded-2xl font-bold transition-all flex items-center justify-center">
                Limpar
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="bg-white rounded-2xl md:rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto scrollbar-hide">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-black tracking-widest">
                <tr>
                    <th class="px-8 py-5">Paciente / Prontuário</th>
                    <th class="px-8 py-5">Último Estado</th>
                    <th class="px-8 py-5">Setor / Leito</th>
                    <th class="px-8 py-5 text-center">Pontos</th>
                    <th class="px-8 py-5 text-center">Histórico</th>
                    <th class="px-8 py-5 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($pacientes)): ?>
                    <tr>
                        <td colspan="6" class="px-8 py-12 text-center text-slate-400">
                            <i class="fas fa-user-injured text-4xl mb-4 block opacity-20"></i>
                            Nenhum paciente cadastrado.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($pacientes as $p): 
                    $color = "text-slate-500 bg-slate-100";
                    if ($p['classificacao']) {
                        if (strpos($p['classificacao'], 'CM') !== false) $color = "text-green-700 bg-green-100";
                        else if (strpos($p['classificacao'], 'intermediários') !== false) $color = "text-blue-700 bg-blue-100";
                        else if (strpos($p['classificacao'], 'Alta') !== false) $color = "text-amber-700 bg-amber-100";
                        else if (strpos($p['classificacao'], 'CSI') !== false) $color = "text-orange-700 bg-orange-100";
                        else if (strpos($p['classificacao'], 'Intensivos') !== false) $color = "text-red-700 bg-red-100";
                    }
                ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-8 py-5">
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-slate-800 uppercase"><?php echo cleanInput($p['nome']); ?></span>
                            <span class="text-[10px] text-slate-400 font-bold tracking-wider">#<?php echo $p['prontuario']; ?></span>
                        </div>
                    </td>
                    <td class="px-8 py-5">
                        <?php if ($p['classificacao']): ?>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo $color; ?>">
                                <?php echo $p['classificacao']; ?>
                            </span>
                            <span class="block text-[9px] text-slate-400 mt-1 font-medium italic">Em: <?php echo date('d/m/Y H:i', strtotime($p['data_registro'])); ?></span>
                        <?php else: ?>
                            <span class="text-xs text-slate-400">Sem classificação</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-8 py-5">
                        <?php if ($p['setor']): ?>
                            <span class="text-xs font-bold text-slate-600 block"><?php echo $p['setor']; ?></span>
                            <span class="text-[10px] font-black text-blue-500 uppercase tracking-tighter opacity-70"><?php echo $p['leito']; ?></span>
                        <?php else: ?>
                            <span class="text-xs text-slate-400">---</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-8 py-5 text-center">
                        <span class="text-lg font-black text-slate-700"><?php echo $p['total_pontos'] ?? '---'; ?></span>
                    </td>
                    <td class="px-8 py-5 text-center">
                        <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-lg text-xs font-black">
                            <?php echo $p['total_historico']; ?> <?php echo $p['total_historico'] == 1 ? 'avaliação' : 'avaliações'; ?>
                        </span>
                    </td>
                    <td class="px-8 py-5 text-center flex items-center justify-center gap-2">
                        <a href="fugulin_paciente_historico.php?id=<?php echo $p['id']; ?>" class="w-10 h-10 inline-flex items-center justify-center bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Ver Histórico Completo">
                            <i class="fas fa-history"></i>
                        </a>
                        <a href="fugulin_novo.php?id_paciente=<?php echo $p['id']; ?>" class="w-10 h-10 inline-flex items-center justify-center bg-green-50 text-green-600 rounded-xl hover:bg-green-600 hover:text-white transition-all shadow-sm" title="Nova Avaliação">
                            <i class="fas fa-plus-circle"></i>
                        </a>
                        <a href="fugulin_action.php?acao=alta&id=<?php echo $p['id']; ?>" 
                           onclick="return confirm('Confirmar alta do paciente <?php echo cleanInput($p['nome']); ?>? Ele deixará de aparecer na lista ativa.')"
                           class="w-10 h-10 inline-flex items-center justify-center bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Dar Alta">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
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
