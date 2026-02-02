<?php
require_once __DIR__ . '/../includes/header.php';

// Apenas administradores podem gerenciar setores
if ($_SESSION['user_nivel'] !== 'Administrador') {
    $_SESSION['mensagem_erro'] = "Acesso restrito a administradores.";
    redirect('home.php');
}

// Busca todos os setores
$stmt = $pdo->query("SELECT * FROM fugulin_setores ORDER BY nome ASC");
$setores = $stmt->fetchAll();
?>

<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Gerenciar Setores</h1>
        <p class="text-slate-500">Cadastre e edite os departamentos e alas do hospital.</p>
    </div>
    <button onclick="openModal('modal-setor')" class="bg-slate-800 text-white px-6 py-3 rounded-2xl font-bold hover:bg-slate-900 transition-all flex items-center gap-2 shadow-xl shadow-slate-200">
        <i class="fas fa-plus"></i> Novo Setor
    </button>
</div>

<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">ID</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">Nome do Setor</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($setores)): ?>
                    <tr>
                        <td colspan="3" class="px-8 py-10 text-center text-slate-400 font-medium">Nenhum setor cadastrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($setores as $s): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-5 text-sm text-slate-400 font-bold"><?php echo $s['id']; ?></td>
                            <td class="px-8 py-5 text-sm text-slate-700 font-black uppercase tracking-tight"><?php echo cleanInput($s['nome']); ?></td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onclick='editSetor(<?php echo json_encode($s); ?>)' class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <a href="setores_action.php?acao=excluir&id=<?php echo $s['id']; ?>" 
                                       onclick="return confirm('ATENÇÃO: Deseja realmente excluir este setor? Esta ação não pode ser desfeita e só é permitida se o setor não estiver em uso.')"
                                       class="w-9 h-9 bg-red-50 text-red-500 rounded-xl flex items-center justify-center hover:bg-red-500 hover:text-white transition-all">
                                        <i class="fas fa-trash text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Setor (Novo/Editar) -->
<div id="modal-setor" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in duration-300">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 id="modal-title" class="text-2xl font-black text-slate-800 tracking-tight">Novo Setor</h3>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Identificação da Ala</p>
            </div>
            <button onclick="closeModal('modal-setor')" class="w-10 h-10 bg-slate-100 text-slate-400 hover:text-slate-600 rounded-full flex items-center justify-center transition-all">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        
        <form action="setores_action.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="acao" value="salvar_setor">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" id="setor_id" name="id" value="">

            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2 mb-2">Nome do Setor</label>
                <input type="text" id="setor_nome" name="nome" required placeholder="Ex: UTI Adulto, Pediatria..." 
                       class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700">
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('modal-setor')" class="flex-1 px-6 py-4 text-slate-500 font-bold hover:bg-slate-100 rounded-2xl transition-all">Cancelar</button>
                <button type="submit" class="flex-[2] bg-slate-800 hover:bg-slate-900 text-white px-8 py-4 rounded-2xl font-bold transition-all shadow-xl shadow-slate-200">
                    Salvar Setor
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.body.style.overflow = 'auto';
    // Reset form
    document.getElementById('setor_id').value = '';
    document.getElementById('setor_nome').value = '';
    document.getElementById('modal-title').innerText = 'Novo Setor';
}

function editSetor(s) {
    document.getElementById('setor_id').value = s.id;
    document.getElementById('setor_nome').value = s.nome;
    document.getElementById('modal-title').innerText = 'Editar Setor';
    openModal('modal-setor');
}
</script>

<?php 
echo "</div></main></body></html>";
?>
