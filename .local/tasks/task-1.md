---
title: Padronizar hardskills de liderança por nível
---
# Padronizar Hardskills de Liderança por Nível

## What & Why
Inserir um conjunto baseline de hardskills de supervisão, gestão de pessoas e liderança em todos os cargos dos níveis Supervisor, Coordenador e Gerente. Cada nível tem um conjunto progressivo (Coordenador herda o de Supervisor, Gerente herda o de Coordenador). Apenas as skills faltantes são adicionadas — nada é removido.

## Done looks like
- Todos os 10 supervisores têm as 7 hardskills base de supervisão cadastradas
- Todos os 4 coordenadores têm as 9 hardskills base (inclui planejamento e coordenação)
- Os 2 gerentes têm as 10 hardskills base (inclui relatório gerencial)
- "Análise de Riscos e Controle Financeiro (Gerencial)" permanece EXCLUSIVA do GERENTE ADMINISTRATIVO — não é replicada para o GERENTE DE CAMPO
- Total: 50 novas linhas inseridas em `habilidades_cargo`
- Nenhuma skill existente é alterada ou removida

## Out of scope
- Softskills (só hardskills são padronizadas agora)
- Outros níveis hierárquicos (Encarregado, Assistente, Auxiliar, Operacional)
- Criação de novas habilidades no banco — todas as 10 skills já existem em `habilidades`

## Inserções necessárias por cargo

### SUPERVISOR (7 hardskills base — IDs: 12, 13, 14, 15, 17, 25, 203)
| cargoId | cargoNome | Skills faltando (IDs) |
|---------|-----------|----------------------|
| 90 | COORDENADOR DE DEPARTAMENTO PESSOAL | 12, 13, 15, 17 |
| 85 | COORDENADOR DE RECURSOS HUMANOS | 14, 25, 203 |
| 29 | ENCARREGADO DE MANUTENÇÃO DE PACKING | 203 |
| 26 | SUPERVISOR DE BIOPROCESSOS | 203 |
| 27 | SUPERVISOR DE COLHEITA | 17, 25, 203 |
| 48 | SUPERVISOR DE COMPRAS | 17, 25, 203 |
| 37 | SUPERVISOR DE COPA E LIMPEZA | 17, 25, 203 |
| 2  | SUPERVISOR DE TRATOS CULTURAIS | 17, 203 |
| 34 | SUPERVISOR FINANCEIRO | 25, 203 |
| 62 | SUPERVISOR FISCAL | 203 |

### COORDENADOR (9 hardskills base — IDs: 12, 13, 14, 15, 17, 25, 38, 105, 203)
| cargoId | cargoNome | Skills faltando (IDs) |
|---------|-----------|----------------------|
| 58 | COORDENADOR DE CAMPO | 17, 25, 38, 105, 203 |
| 71 | COORDENADOR DE MAQUINAS E IMPLEMENTOS | 15, 25, 203 |
| 31 | COORDENADOR DE VIVEIRO | 17, 25, 38, 105, 203 |
| 1  | ENCARREGADO DE COLHEITA | 17, 38, 105, 203 |

### GERENTE (10 hardskills base — IDs: 12, 13, 14, 15, 17, 25, 38, 54, 105, 203)
| cargoId | cargoNome | Skills faltando (IDs) |
|---------|-----------|----------------------|
| 33 | GERENTE ADMINISTRATIVO | 25, 38, 54, 203 |
| 46 | GERENTE DE CAMPO | 17, 25, 38, 54, 105, 203 |

## IDs das habilidades envolvidas
- 12 = Atenção à Qualidade do Produto e/ou Serviço
- 13 = Compromisso com Normas de Segurança e Sustentabilidade
- 14 = Uso Consciente e Racional de Recursos
- 15 = Conformidade com Normas e Boas Práticas (BP)
- 17 = Administração de Equipe e Intermediação de Relações
- 25 = Distribuição de Tarefas e Controle de Frequência
- 38 = Coordenação de Cronogramas de Produção
- 54 = Elaboração de Relatórios Gerenciais e Demonstrações de Resultado
- 105 = Planejamento Estratégico e Gestão de Recursos
- 203 = Supervisão de Equipe

## Steps
1. **Inserir via SQL com ON CONFLICT DO NOTHING** — Para cada par (cargoId, habilidadeId) listado acima, inserir em `habilidades_cargo` usando INSERT ... ON CONFLICT DO NOTHING para segurança contra duplicatas. Agrupar em um único script SQL executado via PDO.
2. **Verificar resultado** — Após inserção, confirmar que cada cargo do nível possui as skills esperadas consultando `habilidades_cargo` JOIN `habilidades`.

## Relevant files
- `sql/postgres/schema.sql`
- `src/Core/Database.php`
- `src/Repository/CargoRepository.php`