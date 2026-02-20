<?php
include 'config/database.php';
include 'includes/functions.php';

$id_carrinho = 9;

// 1. Configuração das Gavetas
$gavetas_config = [
    1 => "Medicamentos de Emergência",
    2 => "Materiais de Acesso Venoso",
    3 => "Vias Aéreas e Intubação",
    4 => "Soros e Equipamentos Pesados"
];

foreach ($gavetas_config as $num => $desc) {
    $stmt = $pdo->prepare("REPLACE INTO car_gavetas_config (id_carrinho, num_gaveta, descricao) VALUES (?, ?, ?)");
    $stmt->execute([$id_carrinho, $num, $desc]);
}

// 2. Itens a serem inseridos (Dados extraídos do Excel)
// Estrutura: [Nome, Gaveta, Qtd Ideal, Qtd Minima, Tipo, Unidade, Nome Comercial]
$itens_import = [
    // GAVETA 1 - Medicamentos
    ["AAS 100 mg", 1, 3, 1, "Medicamento", "cp", "Acetilsalicilico"],
    ["Clopidogrel 75 mg", 1, 6, 2, "Medicamento", "cp", "Plavix"],
    ["Isossorbida 5 mg", 1, 2, 1, "Medicamento", "cp", "Isordil"],
    ["Adrenalina 1mg / ml", 1, 20, 5, "Medicamento", "ap", "Adren"],
    ["Adenosina 3mg/ ml", 1, 5, 2, "Medicamento", "ap", ""],
    ["Agua Destilada 10 ml", 1, 5, 2, "Medicamento", "ap", ""],
    ["Amiodarona 50 mg/3 ml", 1, 8, 3, "Medicamento", "ap", "Ancoron"],
    ["Atropina 0,25mg/ml", 1, 10, 3, "Medicamento", "ap", ""],
    ["Bic. De Sodio 8.4% 10 ml", 1, 5, 2, "Medicamento", "ap", ""],
    ["Cloreto de Sodio 0,9% 10 ml", 1, 5, 2, "Medicamento", "ap", ""],
    ["Dobutamina 20mg", 1, 2, 1, "Medicamento", "ap", "Dobutrex"],
    ["Dopamina 50mg/10ml", 1, 4, 2, "Medicamento", "ap", "Revivan"],
    ["Furosemida 10 mg/2ml", 1, 5, 2, "Medicamento", "ap", "Lasix"],
    ["Gluconato Calcio 10% 10 ml", 1, 3, 1, "Medicamento", "ap", ""],
    ["Glicose 50% 10 ml", 1, 5, 2, "Medicamento", "ap", ""],
    ["Hidralazina 20 mg/ml", 1, 5, 2, "Medicamento", "ap", "Nepresol"],
    ["Hidrocortisona 500 mg", 1, 2, 1, "Medicamento", "fr", "Flebocortid"],
    ["Hidrocortisona 100mg", 1, 2, 1, "Medicamento", "fr", "Flebocortid"],
    ["Kanakion 10 mg/ml", 1, 2, 1, "Medicamento", "ap", "Kavit"],
    ["Encrise (Vasopressina) 20UI/ml", 1, 2, 1, "Medicamento", "ap", ""],
    ["Nitroglicerina 5mg/ml", 1, 2, 1, "Medicamento", "ap", "Tridil"],
    ["Licocaina 2% sem vaso 20mg/ml", 1, 1, 1, "Medicamento", "fr", "Xylestein"],
    ["Succitrat 100mg", 1, 1, 1, "Medicamento", "fr", "Succinil Colin"],
    ["Alteplase 50mg/50ml", 1, 1, 0, "Medicamento", "fr", "Actilyse"],
    ["Nitroprussiato 25mg/ml", 1, 1, 1, "Medicamento", "ap", "Nipride"],
    ["Noradrenalina 1mg/ml", 1, 8, 2, "Medicamento", "ap", "Norepinefrina"],
    ["Sulf. Magnésio 10% 10 ml", 1, 5, 2, "Medicamento", "ap", ""],
    ["Metropolol Tartarato", 1, 2, 1, "Medicamento", "ap", "Selozok"],
    ["Bricanyl 1 ml", 1, 2, 1, "Medicamento", "ap", "Sulfato de Terbutalina"],

    // GAVETA 2 - Materiais
    ["Agulha 30x7", 2, 2, 1, "Material", "u", ""],
    ["Agulha 40 x12", 2, 6, 2, "Material", "u", ""],
    ["Seringa 1 ml c/ agulha", 2, 2, 1, "Material", "u", ""],
    ["Seringa 3 ml", 2, 2, 1, "Material", "u", ""],
    ["Seringa 5 ml", 2, 5, 2, "Material", "u", ""],
    ["Seringa 10 ml", 2, 5, 2, "Material", "u", ""],
    ["Seringa 20 ml", 2, 2, 1, "Material", "u", ""],
    ["Seringa Preenchida - Salina 10ml", 2, 6, 2, "Material", "u", ""],
    ["Jelco nº 16", 2, 1, 0, "Material", "u", ""],
    ["Jelco nº 18", 2, 1, 0, "Material", "u", ""],
    ["Jelco nº 20", 2, 2, 1, "Material", "u", ""],
    ["Jelco nº 22", 2, 2, 1, "Material", "u", ""],
    ["IV FIX", 2, 2, 1, "Material", "u", ""],

    // GAVETA 3 - Materiais Vias Aereas
    ["Cateter tipo óculos", 3, 1, 0, "Material", "u", ""],
    ["Mascara Ventury", 3, 1, 0, "Material", "u", ""],
    ["Extensão de O2", 3, 2, 1, "Material", "u", ""],
    ["Mascara não reinalante", 3, 1, 0, "Material", "u", ""],
    ["Canula Guedel nº 2", 3, 1, 0, "Material", "u", ""],
    ["Canula Guedel nº 3", 3, 1, 0, "Material", "u", ""],
    ["Fio Guia", 3, 2, 1, "Material", "u", ""],
    ["Sonda de Aspiração nº12", 3, 2, 1, "Material", "u", ""],
    ["Sonda de Aspiração nº14", 3, 1, 0, "Material", "u", ""],
    ["Extensão de Aspiração", 3, 2, 1, "Material", "u", ""],
    ["Tubo endotraqueal n.06", 3, 1, 0, "Material", "h", ""],
    ["Tubo endotraqueal n.6,5", 3, 1, 0, "Material", "U", ""],
    ["Tubo endotraqueal n.7", 3, 1, 0, "Material", "u", ""],
    ["Tubo endotraqueal n.7,5", 3, 2, 1, "Material", "u", ""],
    ["Tubo endotraqueal n.8", 3, 2, 1, "Material", "u", ""],
    ["Tubo endotraqueal n.8,5", 3, 2, 1, "Material", "u", ""],
    ["Tubo endotraqueal n.9", 3, 1, 0, "Material", "u", ""],
    ["Filtro Bacteriologico", 3, 1, 0, "Material", "u", ""],
    ["Guia de Tubo", 3, 1, 0, "Material", "u", ""],
    ["Luva esteril nº6,5", 3, 1, 1, "Material", "u", ""],
    ["Luva esteril nº7", 3, 1, 1, "Material", "u", ""],
    ["Luva esteril nº7,5", 3, 1, 1, "Material", "u", ""],
    ["Luva esteril nº8", 3, 2, 1, "Material", "u", ""],
    ["Luva esteril nº8,5", 3, 1, 1, "Material", "u", ""],

    // GAVETA 4 - Soros/Eq
    ["Ringer Lactato 500 ml", 4, 1, 0, "Medicamento", "fr", ""],
    ["Bicarbonato de sódio 8,4% 250 ml", 4, 1, 0, "Medicamento", "fr", ""],
    ["Soro Fisiológico 100 ml", 4, 1, 0, "Medicamento", "fr", ""],
    ["Soro Fisiológico 250 ml", 4, 1, 0, "Medicamento", "fr", ""],
    ["Soro Fisiológico 500 ml", 4, 1, 0, "Medicamento", "fr", ""],
    ["Soro Fisiológico 1000 ml", 4, 1, 0, "Medicamento", "fr", ""],
    ["Soro Glicosado 5% 250 ml", 4, 1, 0, "Medicamento", "fr", ""],
    ["Soro Glicosado 5% 500 ml", 4, 1, 0, "Medicamento", "fr", ""],
    ["Gel condutor", 4, 1, 0, "Material", "Fr", ""],
    ["Cardioversor / Desfibrilador", 4, 1, 1, "Equipamento", "Eq", ""],
    ["Cabo Laringoscópio", 4, 1, 1, "Equipamento", "Eq", ""],
    ["Lamina Laringoscopio Curva nº 3", 4, 1, 1, "Equipamento", "Eq", ""],
    ["Lamina Laringoscopio Curva nº 4", 4, 2, 1, "Equipamento", "Eq", ""],
    ["Lamina Laringoscopio Curva nº 5", 4, 1, 1, "Equipamento", "Eq", ""],
    ["Tabua Rigida", 4, 1, 1, "Equipamento", "Eq", ""],
    ["Aparelho de Marca Passo", 4, 1, 1, "Equipamento", "Eq", ""],
    ["Cabo extensão do marca passo", 4, 1, 1, "Equipamento", "Eq", ""],
    ["Eletrodo de Marca passo", 4, 1, 1, "Equipamento", "Eq", ""]
];

try {
    $pdo->beginTransaction();

    // 3. Limpar composição atual para não duplicar
    $stmt_del = $pdo->prepare("DELETE FROM car_composicao_ideal WHERE id_carrinho = ?");
    $stmt_del->execute([$id_carrinho]);

    foreach ($itens_import as $item) {
        $nome = $item[0];
        $gaveta = $item[1];
        $qtd_ideal = $item[2];
        $qtd_min = $item[3];
        $tipo = $item[4];
        $unidade = $item[5];
        $nome_comercial = $item[6];

        // Busca ou insere no catálogo global
        $stmt_cat = $pdo->prepare("SELECT id FROM car_itens_mestres WHERE nome = ?");
        $stmt_cat->execute([$nome]);
        $id_item = $stmt_cat->fetchColumn();

        if (!$id_item) {
            $stmt_ins_cat = $pdo->prepare("INSERT INTO car_itens_mestres (nome, nome_comercial, tipo, unidade) VALUES (?, ?, ?, ?)");
            $stmt_ins_cat->execute([$nome, $nome_comercial, $tipo, $unidade]);
            $id_item = $pdo->lastInsertId();
            echo "Item CRIADO: $nome\n";
        } else {
            // Atualiza unidade/tipo se necessário
            $stmt_up_cat = $pdo->prepare("UPDATE car_itens_mestres SET nome_comercial = ?, tipo = ?, unidade = ? WHERE id = ?");
            $stmt_up_cat->execute([$nome_comercial, $tipo, $unidade, $id_item]);
            echo "Item VINCULADO: $nome\n";
        }

        // Insere na composição ideal
        $stmt_comp = $pdo->prepare("INSERT INTO car_composicao_ideal (id_carrinho, id_item, gaveta, quantidade_ideal, quantidade_minima) VALUES (?, ?, ?, ?, ?)");
        $stmt_comp->execute([$id_carrinho, $id_item, $gaveta, $qtd_ideal, $qtd_min]);
    }

    $pdo->commit();
    atualizarStatusCarrinho($pdo, $id_carrinho);
    echo "\nSUCESSO: Carrinho da Tomografia configurado!";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERRO: " . $e->getMessage();
}
