<?php
require_once __DIR__ . '/../includes/header.php';

// Apenas administradores podem gerenciar usuários (regra extra além do middleware)
if ($_SESSION['user_nivel'] !== 'Administrador') {
    $_SESSION['mensagem_erro'] = "Acesso restrito a administradores.";
    redirect(url('admin/home.php'));
}

// Lógica de exclusão
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id_excluir = (int)$_GET['excluir'];
    
    // Não permite excluir a si mesmo
    if ($id_excluir == $_SESSION['user_id']) {
        $_SESSION['mensagem_erro'] = "Você não pode excluir sua própria conta.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        if ($stmt->execute([$id_excluir])) {
            $_SESSION['mensagem_sucesso'] = "Usuário removido com sucesso.";
        }
    }
    redirect('usuarios.php');
}

// Lógica de Ativar/Desativar
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $novo_status = ((int)$_GET['status'] == 1) ? 1 : 0;
    
    $stmt = $pdo->prepare("UPDATE usuarios SET ativo = ? WHERE id = ?");
    $stmt->execute([$novo_status, $id]);
    redirect('usuarios.php');
}

// Paginação
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Query para contar total de registros
$total_registros = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$total_paginas = ceil($total_registros / $limit);

// Busca usuários com limites
$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nome ASC LIMIT $limit OFFSET $offset")->fetchAll();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
    <div>
        <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight">Gerenciamento de Usuários</h1>
        <p class="text-sm text-slate-500">Crie, edite e gerencie as contas de acesso do sistema.</p>
    </div>
    <button onclick="openNewModal()" 
        class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-500/20">
        <i class="fas fa-plus"></i>
        Novo Usuário
    </button>
</div>

<!-- Controle de Registros (Separado) -->
<div class="flex justify-end items-center mb-4 px-2">
    <div class="flex items-center gap-3">
        <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Mostrar:</span>
        <select onchange="updateLimit(this.value)" class="bg-white border border-slate-200 text-slate-700 text-xs font-bold px-3 py-1.5 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer shadow-sm">
            <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
            <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
            <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
            <option value="30" <?php echo $limit == 30 ? 'selected' : ''; ?>>30</option>
        </select>
        <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">por página</span>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-6 py-4">Usuário</th>
                    <th class="px-6 py-4">CPF / Email / Coren</th>
                    <th class="px-6 py-4">Nível de Acesso</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($usuarios as $u): ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 font-bold">
                                <?php echo strtoupper(substr($u['nome'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="font-bold text-slate-700"><?php echo cleanInput($u['nome']); ?></p>
                                <p class="text-xs text-slate-400">@<?php echo cleanInput($u['usuario']); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-slate-600 font-medium"><?php echo formatCPF($u['cpf']); ?></p>
                        <p class="text-xs text-slate-400"><?php echo cleanInput($u['email']); ?></p>
                        <?php if(!empty($u['coren'])): ?>
                            <p class="text-[10px] text-blue-500 font-black mt-1 uppercase tracking-tighter">Coren: <?php echo cleanInput($u['coren']); ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <?php 
                            $badge_color = 'bg-slate-100 text-slate-700';
                            if ($u['nivel_acesso'] === 'Administrador') $badge_color = 'bg-indigo-100 text-indigo-700';
                            if ($u['nivel_acesso'] === 'Gestor') $badge_color = 'bg-blue-100 text-blue-700';
                        ?>
                        <span class="px-2.5 py-1 <?php echo $badge_color; ?> text-[10px] font-black uppercase rounded-lg">
                            <?php echo $u['nivel_acesso']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="?id=<?php echo $u['id']; ?>&status=<?php echo $u['ativo'] ? 0 : 1; ?>" 
                           class="flex items-center gap-2 group">
                            <?php if ($u['ativo']): ?>
                                <span class="w-2.5 h-2.5 bg-green-500 rounded-full group-hover:scale-125 transition-transform"></span>
                                <span class="text-sm text-green-700 font-medium">Ativo</span>
                            <?php else: ?>
                                <span class="w-2.5 h-2.5 bg-slate-300 rounded-full group-hover:scale-125 transition-transform"></span>
                                <span class="text-sm text-slate-500 font-medium">Inativo</span>
                            <?php endif; ?>
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-3">
                            <a href="permissoes.php?usuario=<?php echo $u['id']; ?>" class="w-8 h-8 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all" title="Privilégios">
                                <i class="fas fa-shield-alt text-sm"></i>
                            </a>
                            <button onclick='openEditModal(<?php echo json_encode($u); ?>)' class="w-8 h-8 flex items-center justify-center bg-slate-50 text-slate-600 rounded-lg hover:bg-slate-600 hover:text-white transition-all" title="Editar">
                                <i class="fas fa-edit text-sm"></i>
                            </button>
                            <a href="?excluir=<?php echo $u['id']; ?>" 
                               onclick="return confirm('Tem certeza que deseja excluir este usuário?')"
                               class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-all" title="Excluir">
                                <i class="fas fa-trash-alt text-sm"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Controles de Paginação -->
    <?php if ($total_paginas > 1): ?>
        <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs font-bold text-slate-500">
                Mostrando <span class="text-slate-800"><?php echo min($total_registros, $offset + 1); ?></span> 
                a <span class="text-slate-800"><?php echo min($total_registros, $offset + $limit); ?></span> 
                de <span class="text-slate-800"><?php echo $total_registros; ?></span> usuários
            </p>
            
            <div class="flex items-center gap-2">
                <?php 
                    $query_params = $_GET;
                    
                    // Botão Anterior
                    $prev_query = $query_params;
                    $prev_query['p'] = $page - 1;
                    $prev_link = "?" . http_build_query($prev_query);
                ?>
                
                <?php if ($page > 1): ?>
                    <a href="<?php echo $prev_link; ?>" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all shadow-sm">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </a>
                <?php endif; ?>

                <?php 
                    $start_loop = max(1, $page - 2);
                    $end_loop = min($total_paginas, $page + 2);
                    
                    for ($i = $start_loop; $i <= $end_loop; $i++):
                        $page_query = $query_params;
                        $page_query['p'] = $i;
                        $page_link = "?" . http_build_query($page_query);
                        $active_class = ($i == $page) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50';
                ?>
                    <a href="<?php echo $page_link; ?>" class="w-8 h-8 flex items-center justify-center border <?php echo $active_class; ?> rounded-lg font-bold text-xs transition-all shadow-sm">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php 
                    // Botão Próximo
                    $next_query = $query_params;
                    $next_query['p'] = $page + 1;
                    $next_link = "?" . http_build_query($next_query);
                ?>
                
                <?php if ($page < $total_paginas): ?>
                    <a href="<?php echo $next_link; ?>" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all shadow-sm">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Novo Usuário -->
<div id="modal-novo" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-800">Novo Usuário</h3>
            <button onclick="document.getElementById('modal-novo').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="usuarios_action.php" method="POST" class="p-6 space-y-4" autocomplete="off">
            <input type="hidden" name="acao" value="criar">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nome Completo</label>
                    <input type="text" name="nome" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Usuário</label>
                    <input type="text" name="usuario" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">CPF</label>
                    <input type="text" name="cpf" required placeholder="000.000.000-00" maxlength="14" oninput="maskCPF(this)" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1">COREN</label>
                    <input type="text" name="coren" oninput="maskCoren(this)" placeholder="Ex: 000.000-UF" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1">E-mail</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nível de Acesso</label>
                    <select name="nivel_acesso" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="Usuário Padrão">Usuário Padrão</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Gestor">Gestor</option>
                        <option value="Visualizador">Visualizador</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Senha</label>
                    <input type="password" name="senha" required autocomplete="new-password" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="document.getElementById('modal-novo').classList.add('hidden')" class="px-5 py-2 text-slate-500 font-bold hover:bg-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-xl font-bold transition-all shadow-lg shadow-blue-500/20">Salvar Usuário</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Usuário -->
<div id="modal-editar" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-800">Editar Usuário</h3>
            <button onclick="document.getElementById('modal-editar').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="usuarios_action.php" method="POST" class="p-6 space-y-4" autocomplete="off">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" id="edit-id">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nome Completo</label>
                    <input type="text" name="nome" id="edit-nome" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Usuário</label>
                    <input type="text" name="usuario" id="edit-usuario" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">CPF</label>
                    <input type="text" name="cpf" id="edit-cpf" required placeholder="000.000.000-00" maxlength="14" oninput="maskCPF(this)" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1">COREN</label>
                    <input type="text" name="coren" id="edit-coren" oninput="maskCoren(this)" placeholder="Ex: 000.000-UF" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1">E-mail</label>
                    <input type="email" name="email" id="edit-email" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nível de Acesso</label>
                    <select name="nivel_acesso" id="edit-nivel" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="Usuário Padrão">Usuário Padrão</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Gestor">Gestor</option>
                        <option value="Visualizador">Visualizador</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nova Senha (deixe em branco para não alterar)</label>
                    <input type="password" name="senha" autocomplete="new-password" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="document.getElementById('modal-editar').classList.add('hidden')" class="px-5 py-2 text-slate-500 font-bold hover:bg-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-xl font-bold transition-all shadow-lg shadow-blue-500/20">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openNewModal() {
        const modal = document.getElementById('modal-novo');
        modal.querySelector('form').reset();
        modal.classList.remove('hidden');
    }

    function updateLimit(newLimit) {
        const url = new URL(window.location.href);
        url.searchParams.set('limit', newLimit);
        url.searchParams.set('p', '1');
        window.location.href = url.toString();
    }

    function maskCPF(input) {
        let value = input.value.replace(/\D/g, "");
        if (value.length > 11) value = value.slice(0, 11);
        
        value = value.replace(/(\d{3})(\d)/, "$1.$2");
        value = value.replace(/(\d{3})(\d)/, "$1.$2");
        value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        
        input.value = value;
    }

    function maskCoren(input) {
        let value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, "");
        if (value.length > 6) {
            let nums = value.slice(0, 6);
            let uf = value.slice(6, 8);
            value = nums.replace(/(\d{3})(\d{3})/, "$1.$2") + "-" + uf;
        } else {
            value = value.replace(/(\d{3})(\d{1,3})/, "$1.$2");
        }
        input.value = value;
    }

    function openEditModal(user) {
        document.getElementById('edit-id').value = user.id;
        document.getElementById('edit-nome').value = user.nome;
        document.getElementById('edit-usuario').value = user.usuario;
        
        // Aplica a máscara ao carregar o valor no modal de edição
        const cpfInput = document.getElementById('edit-cpf');
        cpfInput.value = user.cpf;
        maskCPF(cpfInput);
        
        document.getElementById('edit-email').value = user.email;
        
        const corenInput = document.getElementById('edit-coren');
        corenInput.value = user.coren || '';
        maskCoren(corenInput);

        document.getElementById('edit-nivel').value = user.nivel_acesso;
        
        document.getElementById('modal-editar').classList.remove('hidden');
    }
</script>

<?php 
// Fecha o layout aberto no header.php
echo "</div></main></body></html>";
?>
