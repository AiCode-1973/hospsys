<?php
require_once __DIR__ . '/../includes/header.php';

// Busca dados atualizados do usuário (opcional, mas bom por segurança)
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Meu Perfil</h1>
        <p class="text-slate-500">Gerencie suas informações pessoais e segurança da conta.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Card de Informações -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center text-center">
                <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center border-4 border-slate-50 mb-4 shadow-inner">
                    <i class="fas fa-user text-4xl text-slate-300"></i>
                </div>
                <h2 class="text-xl font-black text-slate-800 uppercase tracking-tighter"><?php echo cleanInput($user['nome']); ?></h2>
                <p class="text-sm font-bold text-blue-600 mt-1 uppercase tracking-widest text-[10px]"><?php echo $user['nivel_acesso']; ?></p>
                
                <div class="w-full mt-8 pt-6 border-t border-slate-50 space-y-3 text-left">
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Usuário</span>
                        <span class="text-sm font-bold text-slate-700">@<?php echo cleanInput($user['usuario']); ?></span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">E-mail</span>
                        <span class="text-sm font-bold text-slate-700"><?php echo cleanInput($user['email']); ?></span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">CPF</span>
                        <span class="text-sm font-bold text-slate-700"><?php echo formatCPF($user['cpf']); ?></span>
                    </div>
                    <?php if($user['coren']): ?>
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">COREN</span>
                        <span class="text-sm font-bold text-slate-700"><?php echo cleanInput($user['coren']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Formulário de Alteração de Senha -->
        <div class="lg:col-span-2">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-key"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Segurança</h3>
                        <p class="text-xs text-slate-500 font-medium">Altere sua senha de acesso ao sistema.</p>
                    </div>
                </div>

                <form action="perfil_action.php" method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 pl-1">Senha Atual</label>
                        <input type="password" name="senha_atual" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700" placeholder="••••••••">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 pl-1">Nova Senha</label>
                            <input type="password" name="nova_senha" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700" placeholder="Mínimo 6 caracteres">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 pl-1">Confirmar Nova Senha</label>
                            <input type="password" name="confirmar_senha" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700" placeholder="Repita a nova senha">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full md:w-auto bg-slate-800 hover:bg-slate-900 text-white px-10 py-4 rounded-2xl font-bold transition-all shadow-xl shadow-slate-200 flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i>
                            Atualizar Senha
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="mt-6 p-6 bg-blue-50 rounded-2xl border border-blue-100 flex gap-4">
                <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                <p class="text-xs text-blue-700 leading-relaxed font-medium">
                    <strong class="block mb-1">Dica de Segurança:</strong>
                    Para maior proteção, utilize senhas que combinem letras maiúsculas, minúsculas, números e caracteres especiais. Evite datas de nascimento ou sequências óbvias.
                </p>
            </div>
        </div>
    </div>
</div>

<?php 
echo "</div></main></body></html>";
?>
