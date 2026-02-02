<?php
require_once __DIR__ . '/../includes/header.php';

// Busca setores
$setores = $pdo->query("SELECT * FROM fugulin_setores ORDER BY nome ASC")->fetchAll();

// Busca pacientes ativos para o datalist
$pacientes = $pdo->query("SELECT * FROM fugulin_pacientes WHERE ativo = 1 ORDER BY nome ASC")->fetchAll();

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

// Lógica de Edição
$id_edicao = isset($_GET['id']) ? (int)$_GET['id'] : null;
$edicao_dados = null;
$respostas_edicao = [];

if ($id_edicao) {
    if (!$can_edit) {
        $_SESSION['mensagem_erro'] = "Você não tem permissão para editar classificações.";
        redirect('fugulin_lista.php');
    }
    
    $stmt_edit = $pdo->prepare("SELECT * FROM fugulin_classificacoes WHERE id = ?");
    $stmt_edit->execute([$id_edicao]);
    $edicao_dados = $stmt_edit->fetch();
    
    if ($edicao_dados) {
        $stmt_resp = $pdo->prepare("SELECT id_questao, id_opcao FROM fugulin_respostas WHERE id_classificacao = ?");
        $stmt_resp->execute([$id_edicao]);
        $respostas_edicao = $stmt_resp->fetchAll(PDO::FETCH_KEY_PAIR);
    } else {
        $_SESSION['mensagem_erro'] = "Classificação não encontrada.";
        redirect('fugulin_lista.php');
    }
}

// Lógica de pré-seleção por paciente
$id_paciente_url = isset($_GET['id_paciente']) ? (int)$_GET['id_paciente'] : null;
if ($id_paciente_url && !$edicao_dados) {
    $stmt_p_url = $pdo->prepare("SELECT * FROM fugulin_pacientes WHERE id = ?");
    $stmt_p_url->execute([$id_paciente_url]);
    $paciente_url_dados = $stmt_p_url->fetch();
    if ($paciente_url_dados) {
        $edicao_dados = [
            'paciente_nome' => $paciente_url_dados['nome'],
            'id_paciente' => $paciente_url_dados['id']
        ];
    }
}
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight"><?php echo $id_edicao ? 'Editar Classificação' : 'Escala de Fugulin'; ?></h1>
            <p class="text-slate-500"><?php echo $id_edicao ? 'Modifique os dados da classificação abaixo.' : 'Formulário de classificação de pacientes segundo o sistema de Fugulin.'; ?></p>
        </div>
        <a href="fugulin_lista.php" class="text-slate-400 hover:text-slate-600 font-bold text-sm flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <form action="fugulin_action.php" method="POST" id="fugulinForm" class="space-y-8 pb-20">
        <input type="hidden" name="acao" value="<?php echo $id_edicao ? 'editar' : 'salvar'; ?>">
        <?php if ($id_edicao): ?>
            <input type="hidden" name="id_classificacao" value="<?php echo $id_edicao; ?>">
        <?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <!-- Informações Básicas -->
        <div class="bg-white p-4 md:p-8 rounded-3xl shadow-sm border border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10 hidden md:block">
                <i class="fas fa-id-card text-7xl text-blue-600"></i>
            </div>
            
            <div class="md:col-span-1">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Enfermeiro(a) Responsável</label>
                <div class="flex items-center gap-3 px-4 py-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <i class="fas fa-user-md text-slate-400"></i>
                    <input type="text" value="<?php echo $_SESSION['user_nome']; ?>" readonly class="bg-transparent border-none focus:ring-0 text-slate-600 font-bold w-full text-sm">
                </div>
            </div>

            <div class="md:col-span-1">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Paciente *</label>
                <div class="flex items-center gap-3 px-4 py-3 bg-white rounded-2xl border border-slate-200 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent transition-all">
                    <i class="fas fa-user-injured text-slate-400"></i>
                    <input type="text" name="paciente_nome" id="paciente_input" list="pacientes_list" required placeholder="Nome do paciente..." value="<?php echo $edicao_dados ? cleanInput($edicao_dados['paciente_nome']) : ''; ?>" class="bg-transparent border-none focus:ring-0 text-slate-800 font-medium w-full text-sm">
                    <datalist id="pacientes_list">
                        <?php foreach ($pacientes as $p): ?>
                            <option value="<?php echo cleanInput($p['nome']); ?>" data-prontuario="<?php echo $p['prontuario']; ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>

            <div class="md:col-span-1">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Prontuário / Registro</label>
                <div class="flex items-center gap-3 px-4 py-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <i class="fas fa-barcode text-slate-400"></i>
                    <input type="text" name="paciente_prontuario" id="prontuario_input" placeholder="Novo ou existente" value="<?php 
                        if ($edicao_dados) {
                            if (isset($edicao_dados['id_paciente'])) {
                                $id_para_busca = $edicao_dados['id_paciente'];
                                $stmt_p = $pdo->prepare("SELECT prontuario FROM fugulin_pacientes WHERE id = ?");
                                $stmt_p->execute([$id_para_busca]);
                                echo $stmt_p->fetchColumn();
                            }
                        }
                    ?>" class="bg-transparent border-none focus:ring-0 text-slate-800 font-medium w-full text-sm">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Setor Assistencial *</label>
                <div class="relative">
                    <select name="setor" id="selectSetor" required class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700 appearance-none text-sm">
                        <option value="">Selecione o Setor</option>
                        <?php foreach ($setores as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo (isset($edicao_dados['id_setor']) && $edicao_dados['id_setor'] == $s['id']) ? 'selected' : ''; ?>><?php echo $s['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Leito / Acomodação *</label>
                <div class="relative">
                    <select name="leito" id="selectLeito" required <?php echo $id_edicao ? '' : 'disabled'; ?> class="w-full px-4 py-3 <?php echo $id_edicao ? 'bg-white' : 'bg-slate-50'; ?> border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-bold text-slate-700 disabled:opacity-50 appearance-none text-sm">
                        <?php if ($id_edicao): ?>
                            <?php 
                                $stmt_leitos = $pdo->prepare("SELECT id, descricao FROM fugulin_leitos WHERE id_setor = ?");
                                $stmt_leitos->execute([$edicao_dados['id_setor']]);
                                $leitos_setor = $stmt_leitos->fetchAll();
                                foreach ($leitos_setor as $l):
                            ?>
                                <option value="<?php echo $l['id']; ?>" <?php echo ($edicao_dados['id_leito'] == $l['id']) ? 'selected' : ''; ?>><?php echo $l['descricao']; ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Aguardando setor...</option>
                        <?php endif; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
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
                                <input type="radio" name="pergunta[<?php echo $q['id']; ?>]" value="<?php echo $opt['id']; ?>" data-pontos="<?php echo $opt['pontos']; ?>" required <?php echo (isset($respostas_edicao[$q['id']]) && $respostas_edicao[$q['id']] == $opt['id']) ? 'checked' : ''; ?> class="peer sr-only">
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
                    <?php echo $id_edicao ? 'Salvar Alterações' : 'Finalizar Classificação'; ?>
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

    // Lógica de preenchimento do paciente
    const pacienteInput = document.getElementById('paciente_input');
    const prontuarioInput = document.getElementById('prontuario_input');
    const pacientesList = document.getElementById('pacientes_list');

    pacienteInput.addEventListener('input', function() {
        const val = this.value;
        const options = pacientesList.options;
        for (let i = 0; i < options.length; i++) {
            if (options[i].value === val) {
                prontuarioInput.value = options[i].getAttribute('data-prontuario');
                prontuarioInput.readOnly = true;
                prontuarioInput.parentElement.classList.add('opacity-70');
                return;
            }
        }
        prontuarioInput.readOnly = false;
        prontuarioInput.parentElement.classList.remove('opacity-70');
    });

    // Inicializa se for edição
    <?php if ($id_edicao): ?>
        updateScore();
        if (pacienteInput.value) {
            prontuarioInput.readOnly = true;
            prontuarioInput.parentElement.classList.add('opacity-70');
        }
    <?php endif; ?>
</script>

<?php 
echo "</div></main></body></html>";
?>
