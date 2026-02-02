<?php
require_once __DIR__ . '/../includes/header.php';

// Lógica de exclusão
if (isset($_GET['excluir'])) {
    $id_excluir = (int)$_GET['excluir'];
    try {
        // A verificação de permissão é feita pelo middleware no header.php
        // mas aqui garantimos que apenas admins ou quem tem permissão de excluir pode fazer
        if ($can_delete) {
            $stmt = $pdo->prepare("DELETE FROM fugulin_classificacoes WHERE id = ?");
            $stmt->execute([$id_excluir]);
            $_SESSION['mensagem_sucesso'] = "Classificação excluída com sucesso!";
        } else {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para excluir classificações.";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem_erro'] = "Erro ao excluir: " . $e->getMessage();
    }
    redirect('fugulin_lista.php');
}

// Busca histórico de classificações
$classificacoes = $pdo->query("
    SELECT c.*, u.nome as profissional, s.nome as setor, l.descricao as leito
    FROM fugulin_classificacoes c
    JOIN usuarios u ON c.id_usuario = u.id
    JOIN fugulin_setores s ON c.id_setor = s.id
    JOIN fugulin_leitos l ON c.id_leito = l.id
    ORDER BY c.data_registro DESC
")->fetchAll();

?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
    <div>
        <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight">Histórico Fugulin</h1>
        <p class="text-sm text-slate-500">Abaixo estão listadas todas as classificações realizadas.</p>
    </div>
    <a href="fugulin_novo.php" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-500/20">
        <i class="fas fa-plus"></i>
        Nova Classificação
    </a>
</div>

<div class="bg-white rounded-2xl md:rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto scrollbar-hide">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-black tracking-widest">
                <tr>
                    <th class="px-8 py-5">Data/Hora</th>
                    <th class="px-8 py-5">Paciente</th>
                    <th class="px-8 py-5">Setor / Leito</th>
                    <th class="px-8 py-5">Classificação</th>
                    <th class="px-8 py-5 text-center">Pontos</th>
                    <th class="px-8 py-5">Profissional</th>
                    <th class="px-8 py-5 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($classificacoes)): ?>
                    <tr>
                        <td colspan="6" class="px-8 py-12 text-center text-slate-400">
                            <i class="fas fa-folder-open text-4xl mb-4 block opacity-20"></i>
                            Nenhuma classificação encontrada.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($classificacoes as $c): 
                    $color = "text-slate-500 bg-slate-100";
                    if (strpos($c['classificacao'], 'CM') !== false) $color = "text-green-700 bg-green-100";
                    else if (strpos($c['classificacao'], 'intermediários') !== false) $color = "text-blue-700 bg-blue-100";
                    else if (strpos($c['classificacao'], 'Alta') !== false) $color = "text-amber-700 bg-amber-100";
                    else if (strpos($c['classificacao'], 'CSI') !== false) $color = "text-orange-700 bg-orange-100";
                    else if (strpos($c['classificacao'], 'Intensivos') !== false) $color = "text-red-700 bg-red-100";
                ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-8 py-5">
                        <span class="text-sm font-bold text-slate-700"><?php echo date('d/m/Y', strtotime($c['data_registro'])); ?></span>
                        <span class="block text-[10px] text-slate-400 font-medium"><?php echo date('H:i', strtotime($c['data_registro'])); ?></span>
                    </td>
                    <td class="px-8 py-5">
                        <span class="text-sm font-black text-slate-800 uppercase"><?php echo cleanInput($c['paciente_nome']); ?></span>
                    </td>
                    <td class="px-8 py-5">
                        <span class="text-xs font-bold text-slate-600 block"><?php echo $c['setor']; ?></span>
                        <span class="text-[10px] font-black text-blue-500 uppercase tracking-tighter opacity-70"><?php echo $c['leito']; ?></span>
                    </td>
                    <td class="px-8 py-5">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo $color; ?>">
                            <?php echo $c['classificacao']; ?>
                        </span>
                    </td>
                    <td class="px-8 py-5 text-center">
                        <span class="text-lg font-black text-slate-700"><?php echo $c['total_pontos']; ?></span>
                    </td>
                    <td class="px-8 py-5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-black text-slate-500">
                                <?php echo substr($c['profissional'], 0, 1); ?>
                            </div>
                            <span class="text-xs font-bold text-slate-600"><?php echo $c['profissional']; ?></span>
                        </div>
                    </td>
                    <td class="px-8 py-5 text-center flex items-center justify-center gap-2">
                        <a href="fugulin_imprimir.php?id=<?php echo $c['id']; ?>" target="_blank" class="w-10 h-10 inline-flex items-center justify-center bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Imprimir Relatório">
                            <i class="fas fa-print"></i>
                        </a>
                        <?php if ($can_delete): ?>
                            <a href="?excluir=<?php echo $c['id']; ?>" 
                               onclick="return confirm('Tem certeza que deseja excluir esta classificação?')"
                               class="w-10 h-10 inline-flex items-center justify-center bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm" 
                               title="Excluir">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        <?php endif; ?>
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
