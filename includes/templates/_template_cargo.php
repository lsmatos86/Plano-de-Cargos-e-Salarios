<?php
/**
 * ARQUIVO DE TEMPLATE DE CARGO (PARCIAL)
 */
 
if (!isset($cargo) || !isset($section_counter)) {
    die("Erro: Template carregado sem dados do cargo ou contador de secção.");
}
?>

<div class="report-header-final">
    <span class="cargo-nome-principal"><?php echo htmlspecialchars($cargo['cargoNome']); ?></span>
    
    <p class="cbo-detail">
        CBO: <?php echo htmlspecialchars($cargo['cboCod'] ?? 'N/A'); ?> - 
        <?php echo htmlspecialchars($cargo['cboTituloOficial'] ?? 'Título Oficial Não Disponível'); ?>
    </p>
</div>

<h2 class="h2-custom"><i class="fas fa-id-card"></i> <?php echo $section_counter++; ?>. Informações Essenciais</h2>
<table class="data-list">
    <tr>
        <th><i class="fas fa-file-alt"></i> Descrição Sumária</th>
        <td><?php echo nl2br(htmlspecialchars($cargo['cargoResumo'] ?? 'N/A')); ?></td>
    </tr>
    <tr>
        <th><i class="fas fa-graduation-cap"></i> Escolaridade</th>
        <td><?php echo htmlspecialchars($cargo['escolaridadeTitulo'] ?? 'N/A'); ?></td>
    </tr>
    <tr>
        <th><i class="fas fa-clock"></i> Experiência</th>
        <td><?php echo htmlspecialchars($cargo['cargoExperiencia'] ?? 'N/A'); ?></td>
    </tr>
     <tr>
        <th><i class="fas fa-tags"></i> Sinônimos</th>
        <td><?php echo empty($data['sinonimos']) ? 'Nenhum' : implode(', ', array_map('htmlspecialchars', $data['sinonimos'])); ?></td>
    </tr>
</table>

<?php if ($show_hierarquia): ?>
<h2 class="h2-custom"><i class="fas fa-sitemap"></i> <?php echo $section_counter++; ?>. Hierarquia e Estrutura</h2>
 <table class="data-list">
    <tr>
        <th><i class="fas fa-level-up-alt"></i> Nível Hierárquico</th>
        <td>
            <?php 
            echo htmlspecialchars($cargo['tipoHierarquiaNome'] ?? 'N/A'); 
            if (!empty($cargo['nivelOrdem'])) {
                echo ' (Ordem: ' . htmlspecialchars($cargo['nivelOrdem']) . ')';
            }
            ?>
        </td>
    </tr>
     <tr>
        <th><i class="fas fa-user-tie"></i> Reporta-se a</th>
        <td><?php 
        if (!empty($supervisores)) {
            echo htmlspecialchars(implode(' | ', $supervisores));
        } else {
            echo 'N/A';
        }
    ?></td>
    </tr>
     <tr>
        <th><i class="fas fa-building"></i> Áreas de Atuação</th>
        <td><?php echo empty($data['areas_atuacao']) ? 'Nenhuma' : implode(', ', array_map('htmlspecialchars', $data['areas_atuacao'])); ?></td>
    </tr>
     <tr>
        <th><i class="fas fa-wallet"></i> Faixa Salarial</th>
        <td>
            <?php 
            echo htmlspecialchars($cargo['faixaNivel'] ?? 'Não definida'); 
            if (!empty($cargo['faixaSalarioMinimo'])) {
                echo ' (R$ ' . htmlspecialchars(number_format($cargo['faixaSalarioMinimo'], 2, ',', '.')) . ' - R$ ' . htmlspecialchars(number_format($cargo['faixaSalarioMaximo'], 2, ',', '.')) . ')';
            }
            ?>
        </td>
    </tr>
</table>
<?php endif; ?>

<h2 class="h2-custom"><i class="fas fa-cogs"></i> <?php echo $section_counter++; ?>. Habilidades e Competências</h2>
<div class="skill-container">
    <div class="skill-column">
        <h5 class="h5-custom skill-type-header"><i class="fas fa-toolbox"></i> Habilidades Técnicas (HARD SKILLS)</h5>
        <ul class="habilidade-list">
        <?php if (empty($hard_skills)): ?>
            <li>Nenhuma Hard Skill associada.</li>
        <?php else: ?>
            <?php foreach ($hard_skills as $h): ?>
                <li>
                    <i class="fas fa-chevron-right"></i>
                    <div class="item-content"> 
                        <span class="habilidade-nome"><?php echo htmlspecialchars($h['habilidadeNome']); ?></span>
                        <?php if (!empty($h['habilidadeDescricao'])): ?>
                            <div class="habilidade-descricao"> - <?php echo htmlspecialchars($h['habilidadeDescricao']); ?></div>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
        </ul>
    </div>
    
    <div class="skill-column">
        <h5 class="h5-custom skill-type-header"><i class="fas fa-users"></i> Competências Comportamentais (SOFT SKILLS)</h5>
        <ul class="habilidade-list">
        <?php if (empty($soft_skills)): ?>
            <li>Nenhuma Soft Skill associada.</li>
        <?php else: ?>
            <?php foreach ($soft_skills as $h): ?>
                 <li>
                    <i class="fas fa-chevron-right"></i>
                    <div class="item-content"> 
                        <span class="habilidade-nome"><?php echo htmlspecialchars($h['habilidadeNome']); ?></span>
                        <?php if (!empty($h['habilidadeDescricao'])): ?>
                            <div class="habilidade-descricao"> - <?php echo htmlspecialchars($h['habilidadeDescricao']); ?></div>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
        </ul>
    </div>
</div>

<h5 class="h5-custom"><i class="fas fa-user-tag"></i> Características Pessoais Desejáveis</h5>
<ul class="caracteristica-list">
<?php if (empty($data['caracteristicas'])): ?>
    <li>Nenhuma característica associada.</li>
<?php else: ?>
    <?php foreach ($data['caracteristicas'] as $c): ?>
        <li>
            <i class="fas fa-chevron-right"></i>
            <div class="item-content">
                <?php echo htmlspecialchars($c['caracteristicaNome']); ?>
            </div>
        </li>
    <?php endforeach; ?>
<?php endif; ?>
</ul>

<div class="section-container">
    <div class="section-column">
        <h2 class="h2-custom"><i class="fas fa-handshake"></i> <?php echo $section_counter++; ?>. Qualificação e Recursos</h2>
        
        <h5 class="h5-custom"><i class="fas fa-certificate"></i> Cursos e Treinamentos</h5>
        <ul class="curso-list">
            <?php if (empty($data['cursos'])): ?>
                <li>Nenhum curso associado.</li>
            <?php else: ?>
                <?php foreach ($data['cursos'] as $cur): ?>
                    <li>
                        <i class="fas fa-check-circle" style="color: <?php echo $cur['obrigatorio'] ? '#dc3545' : '#198754'; ?>;"></i>
                        <div class="item-content">
                            <?php echo htmlspecialchars($cur['nome']); ?>
                            <span style="color: <?php echo $cur['obrigatorio'] ? '#dc3545' : '#555'; ?>; font-size: 0.9em;">
                                (<?php echo $cur['obrigatorio'] ? 'OBRIGATÓRIO' : 'Recomendado'; ?>)
                            </span>
                            <?php echo !empty($cur['obs']) ? '<br><small style="color: #555;">Observação: '. htmlspecialchars($cur['obs']) . '</small>' : '' ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <h5 class="h5-custom"><i class="fas fa-wrench"></i> Grupos de Recursos Utilizados</h5>
         <ul class="caracteristica-list">
            <?php if (empty($data['recursos_grupos'])): ?>
                <li>Nenhum grupo de recurso associado.</li>
            <?php else: ?>
                <?php foreach ($data['recursos_grupos'] as $rg): ?>
                    <li>
                        <i class="fas fa-chevron-right"></i>
                        <div class="item-content">
                            <?php echo htmlspecialchars($rg); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div><div class="section-column">
        <h2 class="h2-custom"><i class="fas fa-radiation-alt"></i> <?php echo $section_counter++; ?>. Riscos de Exposição</h2>
        
        <ul class="risco-list">
            <?php if (empty($data['riscos'])): ?>
                <li>Nenhum risco de exposição registrado.</li>
            <?php else: ?>
                <?php foreach ($data['riscos'] as $r): ?>
                    <li>
                        <i class="<?php echo getRiscoIcon($r['riscoNome']); // ?>"></i>
                        <div class="item-content">
                            <strong><?php echo htmlspecialchars($r['riscoNome']); ?>:</strong>
                            <?php echo htmlspecialchars($r['riscoDescricao']); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        
    </div></div><h2 class="h2-custom"><i class="fas fa-book-open"></i> <?php echo $section_counter++; ?>. Descrições Detalhadas</h2>

<h5 class="h5-custom"><i class="fas fa-clipboard-list"></i> Responsabilidades Detalhadas:</h5>
<div class="wysiwyg-content"><?php echo nl2br(htmlspecialchars($cargo['cargoResponsabilidades'] ?? 'N/A')); ?></div>

<h5 class="h5-custom"><i class="fas fa-cloud-sun"></i> Condições Gerais de Trabalho:</h5>
<div class="wysiwyg-content"><?php echo nl2br(htmlspecialchars($cargo['cargoCondicoes'] ?? 'N/A')); ?></div>

<h5 class="h5-custom"><i class="fas fa-layer-group"></i> Complexidade do Cargo:</h5>
<div class="wysiwyg-content"><?php echo nl2br(htmlspecialchars($cargo['cargoComplexidade'] ?? 'N/A')); ?></div>