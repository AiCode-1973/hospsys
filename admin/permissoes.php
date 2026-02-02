<?php
require_once __DIR__ . '/../includes/header.php';

// Apenas administradores gerenciam permissões
if ($_SESSION['user_nivel'] !== 'Administrador') {
    $_SESSION['mensagem_erro'] = "Acesso restrito a administradores.";
    redirect('dashboard.php');
}

$id_usuario_selecionado = isset($_GET['usuario']) ? (int)$_GET['usuario'] : null;

// Salvar permissões
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_permissoes'])) {
    $id_user = (int)$_POST['id_usuario'];
    $permissoes_post = $_POST['perm'] ?? []; // Array [id_modulo][ação] = 1

    try {
        $pdo->beginTransaction();

        // 1. Reseta as permissões do usuário para os módulos enviados
        // Para garantir que desmarcados sejam deletados ou zerados
        $modulos_ids = $pdo->query("SELECT id FROM modulos")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($modulos_ids as $m_id) {
            $pode_v = isset($permissoes_post[$m_id]['v']) ? 1 : 0;
            $pode_c = isset($permissoes_post[$m_id]['c']) ? 1 : 0;
            $pode_e = isset($permissoes_post[$m_id]['e']) ? 1 : 0;
            $pode_d = isset($permissoes_post[$m_id]['d']) ? 1 : 0;

            // Verifica se já existe o registro
            $check = $pdo->prepare("SELECT id FROM permissoes WHERE id_usuario = ? AND id_modulo = ?");
            $check->execute([$id_user, $m_id]);
            
            if ($check->fetch()) {
                $stmt = $pdo->prepare("
                    UPDATE permissoes 
                    SET pode_visualizar = ?, pode_criar = ?, pode_editar = ?, pode_excluir = ? 
                    WHERE id_usuario = ? AND id_modulo = ?
                ");
                $stmt->execute([$pode_v, $pode_c, $pode_e, $pode_d, $id_user, $m_id]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO permissoes (pode_visualizar, pode_criar, pode_editar, pode_excluir, id_usuario, id_modulo) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$pode_v, $pode_c, $pode_e, $pode_d, $id_user, $m_id]);
            }
        }

        $pdo->commit();
        $_SESSION['mensagem_sucesso'] = "Permissões atualizadas com sucesso!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mensagem_erro'] = "Erro ao salvar: " . $e->getMessage();
    }
    redirect("permissoes.php?usuario=$id_user");
}

// Busca lista de usuários para o seletor
$usuarios_lista = $pdo->query("SELECT id, nome, nivel_acesso FROM usuarios ORDER BY nome ASC")->fetchAll();

// Se um usuário foi selecionado, busca os detalhes e permissões
$usuario_info = null;
$permissoes_atuais = [];
if ($id_usuario_selecionado) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario_selecionado]);
    $usuario_info = $stmt->fetch();

    if ($usuario_info) {
        $stmt_p = $pdo->prepare("SELECT * FROM permissoes WHERE id_usuario = ?");
        $stmt_p->execute([$id_usuario_selecionado]);
        $perms = $stmt_p->fetchAll();
        foreach ($perms as $p) {
            $permissoes_atuais[$p['id_modulo']] = $p;
        }
    }
}

$modulos_lista = $pdo->query("SELECT * FROM modulos ORDER BY nome_modulo ASC")->fetchAll();
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Controle de Permissões</h1>
    <p class="text-slate-500">Defina o que cada usuário pode visualizar ou manipular em cada módulo.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <!-- Seletor de Usuário -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fas fa-users text-blue-500"></i>
                Selecione o Usuário
            </h3>
            <div class="space-y-2 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                <?php foreach ($usuarios_lista as $ul): ?>
                    <a href="?usuario=<?php echo $ul['id']; ?>" 
                       class="block p-3 rounded-xl transition-all <?php echo ($id_usuario_selecionado == $ul['id']) ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'bg-slate-50 text-slate-600 hover:bg-slate-100'; ?>">
                        <p class="text-sm font-bold truncate"><?php echo cleanInput($ul['nome']); ?></p>
                        <p class="text-[10px] uppercase opacity-70"><?php echo $ul['nivel_acesso']; ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Tabela de Permissões -->
    <div class="lg:col-span-3">
        <?php if ($usuario_info): ?>
            <form action="permissoes.php" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <input type="hidden" name="id_usuario" value="<?php echo $usuario_info['id']; ?>">
                
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-slate-800 tracking-tight">
                            Permissões de: <span class="text-blue-600"><?php echo cleanInput($usuario_info['nome']); ?></span>
                        </h3>
                        <p class="text-xs text-slate-500">Marque as ações permitidas para cada módulo abaixo.</p>
                    </div>
                    <?php if ($usuario_info['nivel_acesso'] === 'Administrador'): ?>
                        <div class="bg-amber-100 text-amber-700 px-3 py-1 rounded-lg text-[10px] font-bold uppercase flex items-center gap-2">
                            <i class="fas fa-crown"></i> Admin: Acesso Global
                        </div>
                    <?php endif; ?>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest font-black">
                            <tr>
                                <th class="px-6 py-4">Módulo</th>
                                <th class="px-6 py-4 text-center">Visualizar</th>
                                <th class="px-6 py-4 text-center">Criar</th>
                                <th class="px-6 py-4 text-center">Editar</th>
                                <th class="px-6 py-4 text-center">Excluir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($modulos_lista as $m): 
                                $p = $permissoes_atuais[$m['id']] ?? null;
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center">
                                            <i class="<?php echo $m['icone']; ?> text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-700"><?php echo $m['nome_modulo']; ?></p>
                                            <p class="text-[10px] text-slate-400"><?php echo $m['descricao']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <!-- Visualizar -->
                                <td class="px-6 py-4 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="perm[<?php echo $m['id']; ?>][v]" value="1" <?php echo ($p && $p['pode_visualizar']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </td>
                                <!-- Criar -->
                                <td class="px-6 py-4 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="perm[<?php echo $m['id']; ?>][c]" value="1" <?php echo ($p && $p['pode_criar']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                    </label>
                                </td>
                                <!-- Editar -->
                                <td class="px-6 py-4 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="perm[<?php echo $m['id']; ?>][e]" value="1" <?php echo ($p && $p['pode_editar']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                    </label>
                                </td>
                                <!-- Excluir -->
                                <td class="px-6 py-4 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="perm[<?php echo $m['id']; ?>][d]" value="1" <?php echo ($p && $p['pode_excluir']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                                    </label>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button type="submit" name="salvar_permissoes" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-12 text-center">
                <div class="w-20 h-20 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-user-shield text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-400">Nenhum usuário selecionado</h3>
                <p class="text-slate-400 max-w-xs mx-auto mt-2">Escolha um usuário na lista ao lado para gerenciar suas permissões de acesso aos módulos.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>

<?php 
echo "</div></main></body></html>";
?>
