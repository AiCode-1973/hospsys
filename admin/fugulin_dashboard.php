<?php
require_once __DIR__ . '/../includes/header.php';

// Data de hoje
$hoje = date('Y-m-d');

// Busca todos os setores para o filtro
$setores = $pdo->query("SELECT * FROM fugulin_setores ORDER BY nome ASC")->fetchAll();

// Filtro de setor
$filter_sector = isset($_GET['filter_sector']) ? (int)$_GET['filter_sector'] : null;

// Busca o resumo por classificação do dia (última de cada paciente hoje, apenas ativos)
$sql_resumo = "
    SELECT c.classificacao, COUNT(DISTINCT c.id_paciente) as total
    FROM fugulin_classificacoes c
    JOIN fugulin_pacientes p ON c.id_paciente = p.id
    WHERE DATE(c.data_registro) = ?
    AND p.ativo = 1
";

$params_resumo = [$hoje];

if ($filter_sector) {
    $sql_resumo .= " AND c.id_setor = ?";
    $params_resumo[] = $filter_sector;
}

$sql_resumo .= "
    AND c.id = (
        SELECT id FROM fugulin_classificacoes 
        WHERE id_paciente = c.id_paciente 
        AND DATE(data_registro) = ?
        ORDER BY data_registro DESC LIMIT 1
    )
    GROUP BY c.classificacao
";
$params_resumo[] = $hoje;

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

// Calcula total geral de hoje
$total_dia = array_sum($resumo_bruto);

// Busca lista de pacientes ativos classificados hoje
$sql_hoje = "
    SELECT c.*, s.nome as setor, l.descricao as leito, u.nome as profissional
    FROM fugulin_classificacoes c
    JOIN usuarios u ON c.id_usuario = u.id
    JOIN fugulin_pacientes p ON c.id_paciente = p.id
    LEFT JOIN fugulin_setores s ON c.id_setor = s.id
    LEFT JOIN fugulin_leitos l ON c.id_leito = l.id
    WHERE DATE(c.data_registro) = ?
    AND p.ativo = 1
";

$params_hoje = [$hoje];

if ($filter_sector) {
    $sql_hoje .= " AND c.id_setor = ?";
    $params_hoje[] = $filter_sector;
}

$sql_hoje .= "
    AND c.id = (
        SELECT id FROM fugulin_classificacoes 
        WHERE id_paciente = c.id_paciente 
        AND DATE(data_registro) = ?
        ORDER BY data_registro DESC LIMIT 1
    )
    ORDER BY c.data_registro DESC
";
$params_hoje[] = $hoje;

$stmt_hoje = $pdo->prepare($sql_hoje);
$stmt_hoje->execute($params_hoje);
$pacientes_hoje = $stmt_hoje->fetchAll();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Painel Fugulin</h1>
        <p class="text-slate-500">Resumo das classificações de hoje: <span class="font-bold text-slate-700"><?php echo date('d/m/Y'); ?></span></p>
    </div>
    
    <!-- Filtro de Setor -->
    <div class="bg-white p-2 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-2">
        <form id="filterForm" method="GET" class="flex items-center gap-2">
            <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2">Filtrar Setor</span>
            <select name="filter_sector" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold px-4 py-2 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition-all appearance-none pr-8 relative cursor-pointer">
                <option value="">Todos os Setores</option>
                <?php foreach ($setores as $s): ?>
                    <option value="<?php echo $s['id']; ?>" <?php echo $filter_sector == $s['id'] ? 'selected' : ''; ?>><?php echo $s['nome']; ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <?php foreach ($categorias as $nome => $style): 
        $count = $resumo_bruto[$nome] ?? 0;
        $perc = $total_dia > 0 ? ($count / $total_dia) * 100 : 0;
    ?>
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center text-center hover:shadow-md transition-shadow">
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
    <!-- Lista de Pacientes do Dia -->
    <div class="lg:col-span-2 space-y-4">
        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <i class="fas fa-list-ul text-blue-500"></i>
            Pacientes Classificados Hoje
        </h3>
        
        <?php if (empty($pacientes_hoje)): ?>
            <div class="bg-white p-12 rounded-3xl border border-dashed border-slate-200 text-center text-slate-400">
                Ainda não foram realizadas classificações hoje.
            </div>
        <?php else: ?>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-[10px] uppercase font-black text-slate-400 tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Paciente</th>
                            <th class="px-6 py-4">Setor/Leito</th>
                            <th class="px-6 py-4">Classificação</th>
                            <th class="px-6 py-4 text-center">Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($pacientes_hoje as $p): 
                             $color = "text-slate-500 bg-slate-100";
                             if (strpos($p['classificacao'], 'CM') !== false) $color = "text-green-700 bg-green-100";
                             else if (strpos($p['classificacao'], 'intermediários') !== false) $color = "text-blue-700 bg-blue-100";
                             else if (strpos($p['classificacao'], 'Alta') !== false) $color = "text-amber-700 bg-amber-100";
                             else if (strpos($p['classificacao'], 'CSI') !== false) $color = "text-orange-700 bg-orange-100";
                             else if (strpos($p['classificacao'], 'Intensivos') !== false) $color = "text-red-700 bg-red-100";
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-slate-700 uppercase"><?php echo cleanInput($p['paciente_nome']); ?></span>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-slate-500">
                                <?php echo $p['setor']; ?> <span class="text-blue-500"><?php echo $p['leito']; ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase <?php echo $color; ?>">
                                    <?php echo $p['classificacao']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-xs font-bold text-slate-400">
                                <?php echo date('H:i', strtotime($p['data_registro'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Estatísticas de Ocupação/Equipe -->
    <div class="space-y-6">
        <div class="bg-slate-800 p-8 rounded-3xl text-white shadow-xl shadow-slate-900/20 relative overflow-hidden">
            <i class="fas fa-users-cog absolute -bottom-4 -right-4 text-8xl opacity-10"></i>
            <h4 class="text-sm font-black uppercase tracking-widest opacity-60 mb-6">Total de Avaliações</h4>
            <div class="text-6xl font-black mb-2"><?php echo $total_dia; ?></div>
            <p class="text-xs text-slate-400">Pacientes processados hoje.</p>
            
            <div class="mt-8 pt-6 border-t border-slate-700 space-y-4">
                <div class="flex justify-between items-center text-xs">
                    <span class="opacity-60">Meta do Dia</span>
                    <span class="font-bold">Em andamento</span>
                </div>
                <div class="w-full bg-slate-700 h-2 rounded-full">
                    <div class="bg-blue-500 h-full w-2/3 rounded-full"></div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Ação Rápida</h4>
            <a href="fugulin_novo.php" class="flex items-center justify-between p-4 bg-blue-50 text-blue-600 rounded-2xl hover:bg-blue-600 hover:text-white transition-all group">
                <span class="font-bold">Nova Classificação</span>
                <i class="fas fa-plus-circle transform group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>
    </div>
</div>

<?php 
echo "</div></main></body></html>";
?>
