<?php
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <!-- Hero Section -->
    <div class="relative rounded-3xl overflow-hidden bg-slate-900 shadow-2xl mb-12 animate-in fade-in slide-in-from-bottom-10 duration-700">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/30 to-indigo-600/30 backdrop-blur-3xl"></div>
        <div class="relative p-8 md:p-16 flex flex-col md:flex-row items-center justify-between gap-12">
            <div class="flex-1 space-y-6 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500/20 rounded-full border border-blue-500/30 backdrop-blur-md">
                    <span class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></span>
                    <span class="text-xs font-black text-blue-300 uppercase tracking-widest">HospSys v1.0 • Online</span>
                </div>
                <h1 class="text-4xl md:text-6xl font-black text-white tracking-tight leading-tight">
                    Bem-vindo ao <span class="text-blue-400">HospSys</span>
                </h1>
                <p class="text-lg text-slate-300 max-w-xl font-medium">
                    O ecossistema inteligente de gestão hospitalar desenhado para elevar a eficiência assistencial e a segurança do paciente.
                </p>
                <div class="flex flex-wrap gap-4 justify-center md:justify-start pt-4">
                    <a href="fugulin_novo.php" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-2xl font-bold transition-all shadow-xl shadow-blue-600/20 transform hover:-translate-y-1">
                        Iniciar Classificação
                    </a>
                    <a href="fugulin_lista.php" class="bg-white/10 hover:bg-white/20 text-white px-8 py-4 rounded-2xl font-bold backdrop-blur-md transition-all border border-white/10">
                        Ver Histórico
                    </a>
                </div>
            </div>
            <div class="w-full max-w-md animate-in fade-in slide-in-from-right-10 duration-1000 delay-300">
                <div class="relative bg-white/5 p-4 rounded-[40px] border border-white/10 backdrop-blur-2xl shadow-2xl">
                    <div class="bg-slate-900 rounded-[30px] p-6 space-y-6">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 font-bold text-sm">Status do Sistema</span>
                            <span class="text-green-400 text-xs font-black uppercase">Excelente</span>
                        </div>
                        <div class="space-y-4">
                            <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 w-[85%] rounded-full shadow-[0_0_15px_rgba(59,130,246,0.5)]"></div>
                            </div>
                            <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 w-[92%] rounded-full shadow-[0_0_15px_rgba(99,102,241,0.5)]"></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white/5 p-4 rounded-2xl border border-white/5 text-center">
                                <div class="text-2xl font-black text-white">24/7</div>
                                <div class="text-[10px] text-slate-500 uppercase font-black">Uptime</div>
                            </div>
                            <div class="bg-white/5 p-4 rounded-2xl border border-white/5 text-center">
                                <div class="text-2xl font-black text-white">< 1s</div>
                                <div class="text-[10px] text-slate-500 uppercase font-black">Latência</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500">
                <i class="fas fa-clipboard-check text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Escala de Fugulin</h3>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Classifique a dependência dos pacientes com precisão e rapidez. Otimize a distribuição da equipe assistencial.
            </p>
            <a href="fugulin_novo.php" class="text-blue-600 font-bold text-sm flex items-center gap-2 group-hover:gap-4 transition-all">
                Acessar Módulo <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500">
                <i class="fas fa-chart-pie text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Painel Analítico</h3>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Visualize métricas críticas e logs de acesso em tempo real através do nosso dashboard administrativo.
            </p>
            <a href="dashboard.php" class="text-indigo-600 font-bold text-sm flex items-center gap-2 group-hover:gap-4 transition-all">
                Ver Estatísticas <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="w-16 h-16 bg-slate-50 text-slate-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-slate-800 group-hover:text-white transition-all duration-500">
                <i class="fas fa-user-shield text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Administração</h3>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Gerencie usuários, permissões e políticas de segurança com ferramentas profissionais e intuitivas.
            </p>
            <a href="usuarios.php" class="text-slate-600 font-bold text-sm flex items-center gap-2 group-hover:gap-4 transition-all">
                Gerenciar Usuários <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Info Section -->
    <div class="bg-blue-600 rounded-[40px] p-8 md:p-12 text-center text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl"></div>
        <div class="relative max-w-2xl mx-auto space-y-6">
            <div class="inline-block p-4 bg-white/20 rounded-3xl backdrop-blur-lg mb-4">
                <i class="fas fa-shield-alt text-3xl"></i>
            </div>
            <h2 class="text-3xl font-black">Segurança em Primeiro Lugar</h2>
            <p class="text-blue-100 font-medium leading-relaxed">
                Toda a comunicação com o HospSys é criptografada de ponta-a-ponta. Seus dados estão protegidos seguindo os mais rigorosos padrões de conformidade.
            </p>
        </div>
    </div>
</div>

<?php 
echo "</div></main></body></html>";
?>
