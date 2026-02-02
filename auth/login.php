<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect(url('admin/home.php'));
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = cleanInput($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($token)) {
        $erro = "Falha na validação de segurança (CSRF).";
    } elseif (empty($usuario) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {
        // Busca usuário pelo username ou CPF
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE (usuario = ? OR cpf = ?) AND ativo = 1 LIMIT 1");
        $stmt->execute([$usuario, $usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha_hash'])) {
            // Sucesso no login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_nivel'] = $user['nivel_acesso'];
            
            logAccess($pdo, $user['id'], $usuario, 1, 'Login realizado com sucesso.');
            
            redirect(url('admin/home.php'));
        } else {
            // Falha no login
            $erro = "Usuário ou senha inválidos.";
            logAccess($pdo, null, $usuario, 0, 'Tentativa de login falhou.');
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HospSys</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
        }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="glass w-full max-w-md p-8 rounded-2xl shadow-2xl">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-full mb-4 shadow-lg shadow-blue-500/50">
                <i class="fas fa-hospital-user text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">HospSys</h1>
            <p class="text-slate-400">Entre com suas credenciais para acessar o painel</p>
        </div>

        <?php if ($erro): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-lg mb-6 flex items-center gap-3">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $erro; ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div>
                <label for="usuario" class="block text-sm font-medium text-slate-300 mb-2">Usuário ou CPF</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" id="usuario" name="usuario" required
                        class="block w-full pl-10 pr-3 py-3 bg-slate-900/50 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="Digite seu usuário ou CPF">
                </div>
            </div>

            <div>
                <label for="senha" class="block text-sm font-medium text-slate-300 mb-2">Senha</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="senha" name="senha" required
                        class="block w-full pl-10 pr-3 py-3 bg-slate-900/50 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="Digite sua senha">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="lembrar" name="lembrar" type="checkbox" 
                        class="h-4 w-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500">
                    <label for="lembrar" class="ml-2 block text-sm text-slate-400">Lembrar-me</label>
                </div>
                <div class="text-sm">
                    <a href="recuperar_senha.php" class="font-medium text-blue-400 hover:text-blue-300 transition-colors">Esqueceu a senha?</a>
                </div>
            </div>

            <button type="submit" 
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                Entrar no Sistema
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-700/50 text-center">
            <p class="text-slate-500 text-xs">
                &copy; <?php echo date('Y'); ?> HospSys - Sistema de Gestão Hospitalar. <br>
                Desenvolvido com foco em segurança e performance.
            </p>
        </div>
    </div>
</body>
</html>
