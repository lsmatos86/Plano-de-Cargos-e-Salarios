<?php
// Arquivo: test_hash.php (Crie este arquivo na raiz do projeto)

$senha_bruta = '12345';
$hash_correto = '$2y$10$o.R0Tf5bK7wzX5vL/1Q.vO.J3F.K0Q.vO.J3F.K0Q.vO.J3F.K0Q.vO.J3F.K0Q.vO.J3F.K0Q.vO.J3F.K0Q.vO.J3F.K0Q.vO.J3F.K0Q.vO.J3F.K0Q';

echo "<h2>Teste de Hash de Senha</h2>";

// 1. Tenta verificar a senha de teste
if (password_verify($senha_bruta, $hash_correto)) {
    echo "<p style='color:green; font-weight:bold;'>1. VERIFICAÇÃO CONCLUÍDA: O hash está funcionando corretamente!</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>1. ERRO CRÍTICO: O PHP não está conseguindo verificar a senha '12345' com o hash fornecido.</p>";
}

echo "<hr>";

// 2. Gera um novo hash para uma senha customizada (opcional)
$nova_senha = 'minhasenha'; // Crie uma senha de teste única
$novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
echo "<h3>Geração de Novo Hash</h3>";
echo "Senha Bruta Usada: <b>{$nova_senha}</b><br>";
echo "Hash Gerado (Copie este valor para o seu banco de dados, se quiser usar esta senha): <b>{$novo_hash}</b><br>";

// 3. Testa o novo hash
if (password_verify($nova_senha, $novo_hash)) {
    echo "<p style='color:green;'>2. Teste interno: O hash gerado foi verificado com sucesso.</p>";
} else {
    echo "<p style='color:red;'>2. Teste interno: Falha na verificação do hash recém-gerado.</p>";
}
?>