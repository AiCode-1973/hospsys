<?php
require_once __DIR__ . '/../includes/header.php';

$id_paciente = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id_paciente) {
    redirect('fugulin_lista.php');
}

// Busca dados do paciente
$stmt_p = $pdo->prepare("SELECT * FROM fugulin_pacientes WHERE id = ?");
$stmt_p->execute([$id_paciente]);
$paciente = $stmt_p->fetch();

if (!$paciente) {
    $_SESSION['mensagem_erro'] = "Paciente não encontrado.";
    redirect('fugulin_lista.php');
}

// Lógica de exclusão de uma classificação específica
if (isset($_GET['excluir'])) {
    if (!$can_delete) {
        $_SESSION['mensagem_erro'] = "Você não tem permissão para excluir.";
    } else {
        $id_excluir = (int)$_GET['excluir'];
        $stmt_del = $pdo->prepare("DELETE FROM fugulin_classificacoes WHERE id = ? AND id_paciente = ?");
        $stmt_del->execute([$id_excluir, $id_paciente]);
        $_SESSION['mensagem_sucesso'] = "Avaliação excluída com sucesso!";
    }
    redirect("fugulin_paciente_historico.php?id=$id_paciente");
}

// Busca histórico
$stmt_h = $pdo->prepare("
    SELECT c.*, u.nome as profissional, s.nome as setor, l.descricao as leito
    FROM fugulin_classificacoes c
    JOIN usuarios u ON c.id_usuario = u.id
    LEFT JOIN fugulin_setores s ON c.id_setor = s.id
    LEFT JOIN fugulin_leitos l ON c.id_leito = l.id
    WHERE c.id_paciente = ?
    ORDER BY c.data_registro DESC
");
$stmt_h->execute([$id_paciente]);
$historico = $stmt_h->fetchAll();
?>

<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Histórico: <?php echo cleanInput($paciente['nome']); ?></h1>
        <p class="text-slate-500">Prontuário: <span class="font-bold text-slate-700">#<?php echo $paciente['prontuario']; ?></span></p>
    </div>
    <div class="flex flex-wrap gap-2 w-full md:w-auto">
        <a href="fugulin_novo.php?id_paciente=<?php echo $id_paciente; ?>" class="flex-1 md:flex-none bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-2xl font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-500/20">
            <i class="fas fa-plus"></i> Nova Avaliação
        </a>
        <a href="fugulin_lista.php" class="flex-1 md:flex-none bg-white border border-slate-200 text-slate-600 px-5 py-3 rounded-2xl font-bold hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="space-y-4">
    <?php if (empty($historico)): ?>
        <div class="bg-white p-12 rounded-3xl shadow-sm border border-slate-100 text-center text-slate-400">
            <i class="fas fa-history text-5xl mb-4 block opacity-10"></i>
            Nenhuma classificação realizada para este paciente.
        </div>
    <?php endif; ?>

    <?php foreach ($historico as $h): 
        $color = "text-slate-500 bg-slate-100 border-slate-100";
        if ($h['classificacao']) {
            if (strpos($h['classificacao'], 'CM') !== false) $color = "text-green-700 bg-green-100 border-green-200";
            else if (strpos($h['classificacao'], 'intermediários') !== false) $color = "text-blue-700 bg-blue-100 border-blue-200";
            else if (strpos($h['classificacao'], 'Alta') !== false) $color = "text-amber-700 bg-amber-100 border-amber-200";
            else if (strpos($h['classificacao'], 'CSI') !== false) $color = "text-orange-700 bg-orange-100 border-orange-200";
            else if (strpos($h['classificacao'], 'Intensivos') !== false) $color = "text-red-700 bg-red-100 border-red-200";
        }
    ?>
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col md:flex-row items-center gap-6 hover:shadow-md transition-shadow relative overflow-hidden">
        <div class="absolute left-0 top-0 bottom-0 w-1 <?php echo str_replace(['text-', 'bg-', 'border-'], 'bg-', explode(' ', $color)[0]); ?>"></div>
        
        <div class="flex-shrink-0 text-center md:text-left">
            <span class="text-[10px] uppercase font-black text-slate-400 tracking-widest block mb-1">Data / Hora</span>
            <div class="text-sm font-bold text-slate-700"><?php echo date('d/m/Y', strtotime($h['data_registro'])); ?></div>
            <div class="text-[10px] text-slate-400 font-bold"><?php echo date('H:i', strtotime($h['data_registro'])); ?></div>
        </div>
        
        <div class="flex-grow flex flex-col items-center md:items-start">
            <span class="text-[10px] uppercase font-black text-slate-400 tracking-widest block mb-1">Classificação</span>
            <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4">
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border <?php echo $color; ?>">
                    <?php echo $h['classificacao']; ?>
                </span>
                <span class="text-xl font-black text-slate-800 tracking-tighter"><?php echo $h['total_pontos']; ?> <small class="text-xs text-slate-400 uppercase font-bold">pontos</small></span>
            </div>
        </div>

        <div class="flex-shrink-0 text-center md:text-left">
            <span class="text-[10px] uppercase font-black text-slate-400 tracking-widest block mb-1">Setor / Leito</span>
            <div class="text-xs font-bold text-slate-600"><?php echo $h['setor'] ?? '---'; ?></div>
            <div class="text-[10px] font-black text-blue-500 uppercase"><?php echo $h['leito'] ?? '---'; ?></div>
        </div>

        <div class="flex-shrink-0 flex flex-col items-center md:items-start min-w-[140px]">
            <span class="text-[10px] uppercase font-black text-slate-400 tracking-widest block mb-1">Profissional</span>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-500 border border-slate-200">
                    <?php echo substr($h['profissional'], 0, 1); ?>
                </div>
                <span class="text-xs font-bold text-slate-600"><?php echo $h['profissional']; ?></span>
            </div>
        </div>

        <div class="flex items-center gap-2 ml-auto">
            <a href="fugulin_imprimir.php?id=<?php echo $h['id']; ?>" target="_blank" class="w-10 h-10 inline-flex items-center justify-center bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Imprimir Relatório">
                <i class="fas fa-print"></i>
            </a>
            <a href="fugulin_novo.php?id=<?php echo $h['id']; ?>" class="w-10 h-10 inline-flex items-center justify-center bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-600 hover:text-white transition-all shadow-sm" title="Editar esta versão">
                <i class="fas fa-edit"></i>
            </a>
            <?php if ($can_delete): ?>
                <a href="?id=<?php echo $id_paciente; ?>&excluir=<?php echo $h['id']; ?>" 
                   onclick="return confirm('Tem certeza que deseja excluir esta avaliação do histórico?')"
                   class="w-10 h-10 inline-flex items-center justify-center bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Excluir">
                    <i class="fas fa-trash-alt"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php 
echo "</div></main></body></html>";
?>
