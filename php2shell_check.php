<?php
/**
 * php2shell_check.php
 * Script de verificação única para as vulnerabilidades PHP2Shell
 * (CVE-2026-109618 e CVE-2026-109619).
 *
 * Uso (CLI):   php php2shell_check.php
 * Uso (Web):   Acesse via navegador (recomenda-se proteger com .htaccess ou IP).
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== VERIFICAÇÃO DE VULNERABILIDADE PHP2SHELL ===\n\n";

// 1. Coleta de informações do ambiente
$currentVersion = phpversion();
$patchMarker    = ini_get('memory_hard_limit_percent');
$isCli          = (php_sapi_name() === 'cli');

echo "Versão do PHP em execução : {$currentVersion}\n";
echo "Interface (SAPI)          : " . php_sapi_name() . "\n";
echo "Marcador de patch         : " . ($patchMarker !== false ? "PRESENTE (valor: '{$patchMarker}')" : "AUSENTE") . "\n\n";

// 2. Definição das versões oficiais corrigidas
// Fonte: Comunicado oficial do PHP Group (21/10/2026)
$fixedVersions = [
    '8.2' => '8.2.35',
    '8.3' => '8.3.36',
    '8.4' => '8.4.27',
    '8.5' => '8.5.12',
];

// 3. Lógica de verificação
$isVulnerable = false;
$statusMessage = '';
$recommendation = '';

// Verifica se a versão é anterior a 8.2 (sem patch oficial)
if (version_compare($currentVersion, '8.2.0', '<')) {
    $isVulnerable = true;
    $statusMessage = "VULNERÁVEL (versão {$currentVersion} não possui suporte oficial).";
    $recommendation = "Atualize para PHP 8.2.35, 8.3.36, 8.4.27 ou 8.5.12. Versões 8.1 e anteriores estão em fim de vida (EOL) e não receberão correção.";
}
// Verifica a versão dentro das ramificações suportadas
else {
    foreach ($fixedVersions as $branch => $fixedVersion) {
        if (version_compare($currentVersion, $fixedVersion, '<') && version_compare($currentVersion, $branch . '.0', '>=')) {
            $isVulnerable = true;
            $statusMessage = "VULNERÁVEL (versão {$currentVersion} é anterior a {$fixedVersion}).";
            $recommendation = "Atualize para PHP {$fixedVersion} ou superior dentro da ramificação {$branch}.x.";
            break;
        }
    }

    // Se não for vulnerável pela versão, mas o marcador estiver ausente,
    // pode ser um backport que não expõe a diretiva (raro, mas possível).
    if (!$isVulnerable && $patchMarker === false) {
        $statusMessage = "POTENCIALMENTE SEGURO, MAS SEM MARCADOR DE PATCH.";
        $recommendation = "Sua versão ({$currentVersion}) é igual ou superior à corrigida, mas a diretiva 'memory_hard_limit_percent' não foi encontrada. Verifique o changelog do seu fornecedor (ex: `apt changelog php`) para confirmar a aplicação do patch para CVE-2026-109618 / CVE-2026-109619.";
    }
}

// 4. Sobrescreve o status se o marcador de patch estiver presente
// (Backports de distribuições podem manter a versão antiga, mas incluir a correção)
if ($patchMarker !== false) {
    $isVulnerable = false;
    $statusMessage = "CORRIGIDO (marcador de patch 'memory_hard_limit_percent' encontrado).";
    $recommendation = "Nenhuma ação necessária. O patch do PHP2Shell está presente, mesmo que a versão exibida seja anterior.";
}

// 5. Exibição do resultado final
echo "=== RESULTADO DA ANÁLISE ===\n";
if ($isVulnerable) {
    echo "STATUS: ⚠️  {$statusMessage}\n";
    echo "AÇÃO RECOMENDADA: {$recommendation}\n";
} else {
    echo "STATUS: ✅  {$statusMessage}\n";
    echo "OBSERVAÇÃO: {$recommendation}\n";
}

echo "\n=== FIM DO RELATÓRIO ===\n";

// 6. Dica de segurança para execução via web
if (!$isCli) {
    echo "\n[AVISO] Este script está sendo executado via web. Recomenda-se restringir o acesso a ele ou removê-lo após a verificação.\n";
}
