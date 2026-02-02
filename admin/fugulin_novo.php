<?php
require_once __DIR__ . '/../includes/header.php';

// Busca setores
$setores = $pdo->query("SELECT * FROM fugulin_setores ORDER BY nome ASC")->fetchAll();

// Busca questões e suas opções
$questoes = $pdo->query("
    SELECT q.*, o.id as opcao_id, o.pontuacao, o.descricao as opcao_descricao
    FROM fugulin_questoes q
    JOIN fugulin_opcoes o ON q.id = o.id_questao
    WHERE q.ativo = 1
    ORDER BY q.ordem ASC, o.pontuacao ASC
")->fetchAll();

// Agrupa opções por questão
$questoes_agrupadas = [];
foreach ($questoes as $row) {
    if (!isset($questoes_agrupadas[$row['id']])) {
        $questoes_agrupadas[$row['id']] = [
            'id' => $row['id'],
            'titulo' => $row['titulo'],
            'opcoes' => []
        ];
    }
    $questoes_agrupadas[$row['id']]['opcoes'][] = [
        'id' => $row['opcao_id'],
        'pontos' => $row['pontuacao'],
        'texto' => $row['opcao_descricao']
    ];
}

// Busca todos os leitos para o JavaScript
$leitos_all = $pdo->query("SELECT id, id_setor, descricao FROM fugulin_leitos ORDER BY descricao ASC")->fetchAll();
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Escala de Fugulin</h1>
        <p class="text-slate-500">Formulário de classificação de pacientes segundo o sistema de Fugulin.</p>
    </div>

    <form action="fugulin_action.php" method="POST" id="fugulinForm" class="space-y-8 pb-20">
        <input type="hidden" name="acao" value="salvar">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <!-- Informações Básicas -->
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i class="fas fa-id-card text-7xl text-blue-600"></i>
            </div>
            
            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-2">Profissional</label>
                <div class="flex items-center gap-3 px-4 py-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <i class="fas fa-user-md text-slate-400"></i>
                    <input type="text" value="<?php echo $_SESSION['user_nome']; ?>" readonly class="bg-transparent border-none focus:ring-0 text-slate-600 font-bold w-full">
                </div>
            </div>

            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-2">Paciente *</label>
                <div class="flex items-center gap-3 px-4 py-3 bg-white rounded-2xl border border-slate-200 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent transition-all">
                    <i class="fas fa-user-injured text-slate-400"></i>
                    <input type="text" name="paciente" required placeholder="Nome completo do paciente" class="bg-transparent border-none focus:ring-0 text-slate-800 font-medium w-full">
                </div>
            </div>

            <div>
                <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-2">Setor *</label>
                <select name="setor" id="selectSetor" required class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700">
                    <option value="">Selecione o Setor</option>
                    <?php foreach ($setores as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo $s['nome']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-2">Leito *</label>
                <select name="leito" id="selectLeito" required disabled class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700 disabled:opacity-50">
                    <option value="">Selecione primeiro o Setor</option>
                </select>
            </div>
        </div>

        <!-- Questões -->
        <div class="space-y-6">
            <?php $count = 1; foreach ($questoes_agrupadas as $q): ?>
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-start gap-4">
                        <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 text-sm font-black"><?php echo $count++; ?></span>
                        <?php echo $q['titulo']; ?>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($q['opcoes'] as $opt): ?>
                            <label class="relative flex flex-col p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl cursor-pointer hover:bg-white hover:border-blue-200 transition-all group">
                                <input type="radio" name="pergunta[<?php echo $q['id']; ?>]" value="<?php echo $opt['id']; ?>" data-pontos="<?php echo $opt['pontos']; ?>" required class="peer sr-only">
                                <div class="flex items-center justify-between pointer-events-none mb-1">
                                    <span class="text-sm font-bold text-slate-600 group-hover:text-blue-600 transition-colors"><?php echo $opt['texto']; ?></span>
                                    <div class="w-5 h-5 border-2 border-slate-300 rounded-full flex items-center justify-center peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-all">
                                        <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 font-medium">+<?php echo $opt['pontos']; ?> pontos</span>
                                <!-- Peer styling -->
                                <div class="absolute inset-0 border-2 border-transparent peer-checked:border-blue-600 rounded-2xl pointer-events-none transition-all"></div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Floating Result Box -->
        <div class="fixed bottom-0 left-0 right-0 md:bottom-8 md:right-8 md:left-auto z-40 p-4 md:p-0">
            <div class="bg-white p-4 md:p-6 rounded-t-3xl md:rounded-3xl shadow-[0_-10px_40px_rgba(0,0,0,0.1)] md:shadow-2xl border border-slate-200 flex flex-col items-center gap-2 min-w-full md:min-w-[240px] animate-in slide-in-from-bottom-10 duration-500">
                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Total de Pontos</p>
                <div id="totalPontos" class="text-4xl md:text-5xl font-black text-blue-600">0</div>
                <div id="classificacaoText" class="px-4 py-2 bg-slate-100 rounded-2xl text-[10px] md:text-xs font-black uppercase text-slate-500 text-center w-full">Selecione as opções</div>
                <button type="submit" class="mt-2 md:mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white py-3 md:py-4 rounded-xl md:rounded-2xl font-bold shadow-lg shadow-blue-500/20 transition-all transform active:scale-95 text-sm md:text-base">
                    Finalizar Classificação
                </button>
            </div>
        </div>

    </form>
</div>

<script>
    const leitos = <?php echo json_encode($leitos_all); ?>;
    const selectSetor = document.getElementById('selectSetor');
    const selectLeito = document.getElementById('selectLeito');
    const totalPontosEl = document.getElementById('totalPontos');
    const classificacaoTextEl = document.getElementById('classificacaoText');
    const inputs = document.querySelectorAll('input[type="radio"]');

    // Mapeamento dinâmico de leitos
    selectSetor.addEventListener('change', function() {
        const setorId = this.value;
        const filtered = leitos.filter(l => l.id_setor == setorId);
        
        selectLeito.innerHTML = '<option value="">Selecione o Leito</option>';
        filtered.forEach(l => {
            selectLeito.innerHTML += `<option value="${l.id}">${l.descricao}</option>`;
        });
        
        selectLeito.disabled = !setorId;
        selectLeito.classList.toggle('bg-white', !!setorId);
        selectLeito.classList.toggle('bg-slate-50', !setorId);
    });

    // Cálculo dinâmico do score
    function updateScore() {
        let total = 0;
        let respondidas = 0;
        const totalQuestoes = <?php echo count($questoes_agrupadas); ?>;

        document.querySelectorAll('input[type="radio"]:checked').forEach(input => {
            total += parseInt(input.dataset.pontos);
            respondidas++;
        });

        totalPontosEl.innerText = total;

        if (respondidas < totalQuestoes) {
            classificacaoTextEl.innerText = `Faltam ${totalQuestoes - respondidas} questões`;
            classificacaoTextEl.className = "px-4 py-2 bg-slate-100 rounded-2xl text-xs font-black uppercase text-slate-500 text-center w-full";
            return;
        }

        let cls = "";
        let color = "";

        if (total >= 12 && total <= 17) {
            cls = "Cuidados mínimos (CM)";
            color = "bg-green-100 text-green-700 border-green-200";
        } else if (total >= 18 && total <= 23) {
            cls = "Cuidados intermediários (CI)";
            color = "bg-blue-100 text-blue-700 border-blue-200";
        } else if (total >= 24 && total <= 29) {
            cls = "Alta dependência (AD)";
            color = "bg-amber-100 text-amber-700 border-amber-200";
        } else if (total >= 30 && total <= 34) {
            cls = "Cuidados Semi-Intensivo (CSI)";
            color = "bg-orange-100 text-orange-700 border-orange-200";
        } else {
            cls = "Cuidados Intensivos (CI)";
            color = "bg-red-100 text-red-700 border-red-200";
        }

        classificacaoTextEl.innerText = cls;
        classificacaoTextEl.className = `px-4 py-2 rounded-2xl text-xs font-black uppercase text-center w-full border ${color}`;
    }

    inputs.forEach(input => input.addEventListener('change', updateScore));
</script>

<?php 
echo "</div></main></body></html>";
?>
