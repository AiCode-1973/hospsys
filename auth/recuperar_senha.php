<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$mensagem = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email'] ?? '');
    
    // Busca usuário pelo email
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Simulação de envio de email
        $mensagem = "Um link de recuperação foi enviado para o seu e-mail (Simulação).";
        $tipo = "sucesso";
    } else {
        $mensagem = "E-mail não encontrado em nossa base de dados.";
        $tipo = "erro";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - HospSys</title>
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
            <h1 class="text-3xl font-bold text-white mb-2">Recuperar Senha</h1>
            <p class="text-slate-400">Insira seu e-mail cadastrado para receber as instruções</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="<?php echo $tipo === 'sucesso' ? 'bg-green-500/10 border-green-500/50 text-green-500' : 'bg-red-500/10 border-red-500/50 text-red-500'; ?> border p-4 rounded-lg mb-6 flex items-center gap-3">
                <i class="fas <?php echo $tipo === 'sucesso' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <span><?php echo $mensagem; ?></span>
            </div>
        <?php endif; ?>

        <form action="recuperar_senha.php" method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-2">E-mail Institucional</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" id="email" name="email" required
                        class="block w-full pl-10 pr-3 py-3 bg-slate-900/50 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="seu@email.com">
                </div>
            </div>

            <button type="submit" 
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                Enviar Link de Recuperação
            </button>
        </form>

        <div class="mt-8 text-center">
            <a href="login.php" class="text-sm font-medium text-slate-400 hover:text-white transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Voltar para o Login
            </a>
        </div>
    </div>
</body>
</html>
