<?php
require_once __DIR__ . '/../includes/header.php';

// Busca estatísticas rápidas
$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$total_logs = $pdo->query("SELECT COUNT(*) FROM logs_acesso WHERE sucesso = 1")->fetchColumn();
$falhas_login = $pdo->query("SELECT COUNT(*) FROM logs_acesso WHERE sucesso = 0")->fetchColumn();

// Busca as últimas tentativas de login
$ultimos_logs = $pdo->query("
    SELECT * FROM logs_acesso 
    ORDER BY data_hora DESC 
    LIMIT 5
")->fetchAll();
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stat Cards -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total de Usuários</p>
            <p class="text-2xl font-bold text-slate-800"><?php echo $total_usuarios; ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center text-xl">
            <i class="fas fa-sign-in-alt"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Logins com Sucesso</p>
            <p class="text-2xl font-bold text-slate-800"><?php echo $total_logs; ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center text-xl">
            <i class="fas fa-user-lock"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Falhas de Login</p>
            <p class="text-2xl font-bold text-slate-800"><?php echo $falhas_login; ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center text-xl">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Módulos Ativos</p>
            <p class="text-2xl font-bold text-slate-800">3</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Logs -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Últimas Atividades de Acesso</h3>
            <button class="text-blue-600 text-sm font-semibold hover:underline">Ver tudo</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-6 py-4">Usuário</th>
                        <th class="px-6 py-4">IP</th>
                        <th class="px-6 py-4">Data/Hora</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($ultimos_logs as $log): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-700"><?php echo cleanInput($log['usuario_tentativa']); ?></span>
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-sm"><?php echo $log['ip_address']; ?></td>
                        <td class="px-6 py-4 text-slate-500 text-sm"><?php echo date('d/m H:i', strtotime($log['data_hora'])); ?></td>
                        <td class="px-6 py-4">
                            <?php if ($log['sucesso']): ?>
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Sucesso</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">Falha</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Tips -->
    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl shadow-xl p-8 text-white relative overflow-hidden">
        <div class="relative z-10">
            <h3 class="text-2xl font-bold mb-4">Bem-vindo ao HospSys!</h3>
            <p class="text-blue-100 mb-6 leading-relaxed">
                Este é seu painel administrativo centralizado. Aqui você pode gerenciar usuários, 
                configurar permissões granulares e monitorar acessos em tempo real.
            </p>
            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 bg-blue-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-key text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold">Segurança Reforçada</p>
                        <p class="text-sm text-blue-100">Todas as senhas são criptografadas com bcrypt.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 bg-blue-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-tag text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold">Controle de Módulos</p>
                        <p class="text-sm text-blue-100">Atribua permissões de Visualização, Criação, Edição e Exclusão.</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Decorative icons -->
        <i class="fas fa-shield-alt absolute -bottom-10 -right-10 text-[180px] text-white/10 rotate-12"></i>
    </div>
</div>

<?php 
// Fecha o layout aberto no header.php
echo "</div></main></body></html>";
?>
