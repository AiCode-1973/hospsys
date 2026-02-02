<?php
require_once __DIR__ . '/../includes/header.php';

// Período padrão: Início do mês atual até hoje
$data_inicio = isset($_GET['data_inicio']) ? cleanInput($_GET['data_inicio']) : date('Y-m-01');
$data_fim = isset($_GET['data_fim']) ? cleanInput($_GET['data_fim']) : date('Y-m-d');

// Busca todos os setores para o filtro opcional
$setores = $pdo->query("SELECT * FROM fugulin_setores ORDER BY nome ASC")->fetchAll();
$filter_sector = isset($_GET['filter_sector']) ? (int)$_GET['filter_sector'] : null;

// Query para o resumo por classificação (de pacientes que tiveram alta no período)
$sql_resumo = "
    SELECT c.classificacao, COUNT(DISTINCT p.id) as total
    FROM fugulin_pacientes p
    JOIN fugulin_classificacoes c ON c.id = (
        SELECT id FROM fugulin_classificacoes 
        WHERE id_paciente = p.id 
        ORDER BY data_registro DESC LIMIT 1
    )
    WHERE p.ativo = 0 
    AND DATE(p.data_alta) BETWEEN ? AND ?
";

$params_resumo = [$data_inicio, $data_fim];

if ($filter_sector) {
    $sql_resumo .= " AND c.id_setor = ?";
    $params_resumo[] = $filter_sector;
}

$sql_resumo .= " GROUP BY c.classificacao";

$stmt_resumo = $pdo->prepare($sql_resumo);
$stmt_resumo->execute($params_resumo);
$resumo_bruto = $stmt_resumo->fetchAll(PDO::FETCH_KEY_PAIR);

// Padronização das categorias para os cards
$categorias = [
    'Cuidados mínimos (CM)' => ['cor' => 'bg-green-500', 'texto' => 'text-green-600', 'bg_claro' => 'bg-green-50', 'icon' => 'fas fa-check-circle'],
    'Cuidados intermediários (CI)' => ['cor' => 'bg-blue-500', 'texto' => 'text-blue-600', 'bg_claro' => 'bg-blue-50', 'icon' => 'fas fa-info-circle'],
    'Alta dependência (AD)' => ['cor' => 'bg-amber-500', 'texto' => 'text-amber-600', 'bg_claro' => 'bg-amber-50', 'icon' => 'fas fa-exclamation-circle'],
    'Cuidados Semi-Intensivo (CSI)' => ['cor' => 'bg-orange-500', 'texto' => 'text-orange-600', 'bg_claro' => 'bg-orange-50', 'icon' => 'fas fa-procedures'],
    'Cuidados Intensivos (CI)' => ['cor' => 'bg-red-500', 'texto' => 'text-red-600', 'bg_claro' => 'bg-red-50', 'icon' => 'fas fa-heartbeat']
];

$total_periodo = array_sum($resumo_bruto);

// Query para a lista detalhada de altas do período
$sql_lista = "
    SELECT p.*, c.classificacao, c.total_pontos, c.data_registro as data_ultima_class,
           s.nome as setor, l.descricao as leito, u.nome as profissional
    FROM fugulin_pacientes p
    JOIN fugulin_classificacoes c ON c.id = (
        SELECT id FROM fugulin_classificacoes 
        WHERE id_paciente = p.id 
        ORDER BY data_registro DESC LIMIT 1
    )
    LEFT JOIN usuarios u ON c.id_usuario = u.id
    LEFT JOIN fugulin_setores s ON c.id_setor = s.id
    LEFT JOIN fugulin_leitos l ON c.id_leito = l.id
    WHERE p.ativo = 0
    AND DATE(p.data_alta) BETWEEN ? AND ?
";

$params_lista = [$data_inicio, $data_fim];

if ($filter_sector) {
    $sql_lista .= " AND c.id_setor = ?";
    $params_lista[] = $filter_sector;
}

$sql_lista .= " ORDER BY p.data_alta DESC";

$stmt_lista = $pdo->prepare($sql_lista);
$stmt_lista->execute($params_lista);
$altas_periodo = $stmt_lista->fetchAll();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Relatório de Altas Fugulin</h1>
        <p class="text-slate-500">Indicadores de pacientes que receberam alta por período.</p>
    </div>
</div>

<!-- Barra de Filtros por Período e Setor -->
<div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 mb-8">
    <form method="GET" class="flex flex-col md:flex-row items-end gap-4">
        <div class="flex-1 w-full">
            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 block">Data Início</label>
            <input type="date" name="data_inicio" value="<?php echo $data_inicio; ?>" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm font-bold px-4 py-3 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>
        <div class="flex-1 w-full">
            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 block">Data Fim</label>
            <input type="date" name="data_fim" value="<?php echo $data_fim; ?>" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm font-bold px-4 py-3 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>
        <div class="flex-1 w-full">
            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 block">Setor</label>
            <select name="filter_sector" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm font-bold px-4 py-3 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer">
                <option value="">Todos os Setores</option>
                <?php foreach ($setores as $s): ?>
                    <option value="<?php echo $s['id']; ?>" <?php echo $filter_sector == $s['id'] ? 'selected' : ''; ?>><?php echo $s['nome']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="w-full md:w-auto bg-slate-800 hover:bg-slate-900 text-white px-8 py-3.5 rounded-2xl font-bold transition-all flex items-center justify-center gap-2">
            <i class="fas fa-filter"></i>
            Filtrar
        </button>
    </form>
</div>

<!-- Cards de Resumo do Período -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <?php foreach ($categorias as $nome => $style): 
        $count = $resumo_bruto[$nome] ?? 0;
        $perc = $total_periodo > 0 ? ($count / $total_periodo) * 100 : 0;
    ?>
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center text-center">
        <div class="w-12 h-12 <?php echo $style['bg_claro'] . ' ' . $style['texto']; ?> rounded-2xl flex items-center justify-center text-xl mb-4">
            <i class="<?php echo $style['icon']; ?>"></i>
        </div>
        <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1"><?php 
            $nome_curto = str_replace(['Cuidados ', ' (CM)', ' (CI)', ' (AD)', ' (CSI)'], '', $nome);
            echo $nome_curto;
        ?></span>
        <div class="text-3xl font-black text-slate-800"><?php echo $count; ?></div>
        <div class="mt-2 w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
            <div class="<?php echo $style['cor']; ?> h-full" style="width: <?php echo $perc; ?>%"></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Lista de Altas -->
    <div class="lg:col-span-2 space-y-4">
        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <i class="fas fa-file-invoice text-amber-500"></i>
            Detalhes das Altas no Período
        </h3>
        
        <?php if (empty($altas_periodo)): ?>
            <div class="bg-white p-12 rounded-3xl border border-dashed border-slate-200 text-center text-slate-400">
                Nenhuma alta registrada para o período selecionado.
            </div>
        <?php else: ?>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-[10px] uppercase font-black text-slate-400 tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Paciente</th>
                            <th class="px-6 py-4 text-center">Data Alta</th>
                            <th class="px-6 py-4">Última Classific.</th>
                            <th class="px-6 py-4 text-center">Pontos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($altas_periodo as $p): 
                             $color = "text-slate-500 bg-slate-100";
                             if (strpos($p['classificacao'], 'CM') !== false) $color = "text-green-700 bg-green-100";
                             else if (strpos($p['classificacao'], 'intermediários') !== false) $color = "text-blue-700 bg-blue-100";
                             else if (strpos($p['classificacao'], 'Alta') !== false) $color = "text-amber-700 bg-amber-100";
                             else if (strpos($p['classificacao'], 'CSI') !== false) $color = "text-orange-700 bg-orange-100";
                             else if (strpos($p['classificacao'], 'Intensivos') !== false) $color = "text-red-700 bg-red-100";
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700 uppercase"><?php echo cleanInput($p['nome']); ?></span>
                                    <span class="text-[9px] text-slate-400 font-bold tracking-wider">SETOR: <?php echo $p['setor']; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-black text-slate-500"><?php echo date('d/m/Y', strtotime($p['data_alta'])); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase <?php echo $color; ?>">
                                    <?php echo $p['classificacao']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-lg font-black text-slate-700"><?php echo $p['total_pontos']; ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Estatísticas de Fechamento -->
    <div class="space-y-6">
        <div class="bg-slate-900 p-8 rounded-3xl text-white shadow-xl shadow-slate-900/20 relative overflow-hidden">
            <i class="fas fa-folder-open absolute -bottom-4 -right-4 text-8xl opacity-10"></i>
            <h4 class="text-sm font-black uppercase tracking-widest opacity-60 mb-6">Total de Altas</h4>
            <div class="text-6xl font-black mb-2"><?php echo $total_periodo; ?></div>
            <p class="text-xs text-slate-400">Pacientes que deixaram o sistema no período.</p>
            
            <div class="mt-8 pt-6 border-t border-slate-800 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                    <span class="text-[10px] font-bold opacity-60">Média de altas diária: <?php 
                        $dias = (strtotime($data_fim) - strtotime($data_inicio)) / (60 * 60 * 24) + 1;
                        echo number_format($total_periodo / $dias, 1);
                    ?></span>
                </div>
            </div>
        </div>

        <div class="bg-blue-600 p-8 rounded-3xl text-white shadow-xl shadow-blue-500/20">
             <h4 class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-2">Exportação</h4>
             <p class="text-sm font-bold mb-4">Deseja gerar um arquivo PDF deste relatório?</p>
             <button onclick="window.print()" class="w-full bg-white text-blue-600 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-50 transition-all">
                Imprimir Relatório
             </button>
        </div>
    </div>
</div>

<?php 
echo "</div></main></body></html>";
?>
