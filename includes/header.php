<?php
/**
 * Header dinâmico com menu adaptável às permissões
 */
require_once __DIR__ . '/../auth/verificar_permissao.php';

// Busca módulos permitidos para o menu
$stmt_menu = $pdo->prepare("
    SELECT m.* 
    FROM modulos m
    JOIN permissoes p ON m.id = p.id_modulo
    WHERE p.id_usuario = ? AND p.pode_visualizar = 1
    ORDER BY m.nome_modulo ASC
");
$stmt_menu->execute([$_SESSION['user_id']]);
$modulos_menu = $stmt_menu->fetchAll();

// Se for admin, garante que vê todos os módulos (mesmo que não estejam na tabela de permissões individualmente)
if ($_SESSION['user_nivel'] === 'Administrador') {
    $stmt_admin = $pdo->query("SELECT * FROM modulos ORDER BY nome_modulo ASC");
    $modulos_menu = $stmt_admin->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-br" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - HospSys</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar-glass {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        .nav-item:hover {
            background: rgba(59, 130, 246, 0.1);
            color: #60a5fa;
        }
        .nav-item.active {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>
<body class="h-full bg-slate-50 flex overflow-hidden">
    
    <!-- Mobile Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden transition-opacity duration-300"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar-glass text-slate-300 fixed inset-y-0 left-0 z-50 w-64 transform -translate-x-full md:relative md:translate-x-0 flex flex-col flex-shrink-0 transition-transform duration-300 ease-in-out">
        <div class="p-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <i class="fas fa-hospital-user text-white text-xl"></i>
                </div>
                <span class="text-xl font-bold text-white tracking-wider">HospSys</span>
            </div>
            <!-- Close button only on mobile -->
            <button id="closeSidebar" class="md:hidden text-slate-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <nav class="flex-1 px-4 space-y-1 mt-4 overflow-y-auto">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-4 px-2">Menu Principal</p>
            
            <?php foreach ($modulos_menu as $modulo): ?>
                <?php 
                    $active = (strpos($_SERVER['PHP_SELF'], $modulo['rota']) !== false) ? 'active' : '';
                ?>
                <a href="/hospsys/<?php echo $modulo['rota']; ?>" class="nav-item <?php echo $active; ?> flex items-center gap-3 px-3 py-3 rounded-lg transition-all">
                    <i class="<?php echo $modulo['icone']; ?> w-5 text-center"></i>
                    <span class="font-medium"><?php echo $modulo['nome_modulo']; ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center gap-3 p-3 bg-slate-900/50 rounded-xl">
                <div class="w-10 h-10 bg-slate-700 rounded-full flex items-center justify-center border-2 border-slate-600">
                    <i class="fas fa-user text-slate-400"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-bold text-white truncate"><?php echo $_SESSION['user_nome']; ?></p>
                    <p class="text-xs text-slate-500 truncate"><?php echo $_SESSION['user_nivel']; ?></p>
                </div>
            </div>
            <a href="/hospsys/auth/logout.php" class="mt-4 flex items-center gap-3 px-3 py-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-all">
                <i class="fas fa-sign-out-alt"></i>
                <span class="text-sm font-semibold">Sair do Sistema</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Header -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 z-10 shadow-sm">
            <div class="flex items-center gap-4">
                <!-- Hamburger Button -->
                <button id="openSidebar" class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h2 class="text-lg md:text-xl font-bold text-slate-800 truncate">
                    <?php 
                        // Tenta encontrar o nome do módulo atual
                        $titulo = "Dashboard";
                        foreach ($modulos_menu as $m) {
                            if (strpos($_SERVER['PHP_SELF'], $m['rota']) !== false) {
                                $titulo = $m['nome_modulo'];
                                break;
                            }
                        }
                        echo $titulo;
                    ?>
                </h2>
            </div>
            
            <div class="flex items-center gap-3 md:gap-6">
                <!-- Notifications (Hidden on very small screens to save space) -->
                <div class="relative hidden sm:block">
                    <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    <i class="fas fa-bell text-slate-400 hover:text-slate-600 cursor-pointer transition-colors"></i>
                </div>
                <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
                <span class="text-xs md:text-sm text-slate-500 font-medium whitespace-nowrap"><?php echo date('d/m/Y'); ?></span>
            </div>
        </header>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            <?php if (isset($_SESSION['mensagem_erro'])): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 flex justify-between items-center rounded-r-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="text-sm md:text-base"><?php echo $_SESSION['mensagem_erro']; unset($_SESSION['mensagem_erro']); ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 flex justify-between items-center rounded-r-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle"></i>
                        <span class="text-sm md:text-base"><?php echo $_SESSION['mensagem_sucesso']; unset($_SESSION['mensagem_sucesso']); ?></span>
                    </div>
                </div>
            <?php endif; ?>

<script>
    // Logic for Mobile Sidebar Toggle
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const openBtn = document.getElementById('openSidebar');
    const closeBtn = document.getElementById('closeSidebar');

    function toggleSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    if (openBtn) openBtn.addEventListener('click', toggleSidebar);
    if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
    if (overlay) overlay.addEventListener('click', toggleSidebar);
</script>
