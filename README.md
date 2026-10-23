# 5.

```markdown
# php2shell_check

Script único em PHP para verificar se o ambiente em execução está protegido
contra as vulnerabilidades PHP2Shell, identificadas como CVE-2026-109618 e
CVE-2026-109619.

## O que o script faz

O arquivo `php2shell_check.php` executa uma verificação local e imprime um
relatório em texto simples com três informações:

1. A versão do PHP em execução.
2. A interface SAPI utilizada (CLI, apache2handler, fpm-fcgi, etc.).
3. A presença da diretiva de configuração `memory_hard_limit_percent`,
   introduzida pelo patch oficial das vulnerabilidades.

A partir desses dados, o script classifica o ambiente em um dos três estados
descritos na seção "Interpretação da saída".

## Requisitos

- PHP instalado no servidor.
- Permissão para executar o interpretador PHP.
- Leitura das configurações via `ini_get`, disponível por padrão.

O script não precisa de dependências externas, banco de dados ou bibliotecas
adicionais.

## Como executar

### Via linha de comando

Recomendado. Não expõe informação na rede e não depende da configuração do
servidor web.

```bash
php php2shell_check.php
```

Se o arquivo estiver em outro diretório:

```bash
php /caminho/para/php2shell_check.php
```

Saída esperada:

```text
=== VERIFICAÇÃO DE VULNERABILIDADE PHP2SHELL ===

Versão do PHP em execução : 8.2.20
Interface (SAPI)          : cli
Marcador de patch         : AUSENTE

=== RESULTADO DA ANÁLISE ===
STATUS: VULNERÁVEL (versão 8.2.20 é anterior a 8.2.35).
AÇÃO RECOMENDADA: Atualize para PHP 8.2.35 ou superior dentro da ramificação 8.2.x.

=== FIM DO RELATÓRIO ===
```

### Via navegador

Útil quando não há acesso SSH ao servidor.

1. Copie o arquivo para o diretório público do servidor web.
2. Acesse `https://seu-dominio.com/php2shell_check.php`.
3. Leia o relatório exibido.
4. Remova o arquivo do servidor após a verificação.

O script imprime um aviso no final da saída quando detecta execução via web.

## Interpretação da saída

O script produz três estados possíveis.

### VULNERÁVEL

A versão em execução é anterior à versão corrigida da sua ramificação, ou
está fora do ciclo de suporte oficial (8.1 e anteriores).

Ação: atualizar o PHP para a versão corrigida indicada na saída.

### POTENCIALMENTE SEGURO, MAS SEM MARCADOR DE PATCH

A versão é igual ou superior à corrigida, mas a diretiva
`memory_hard_limit_percent` não foi encontrada. Esse cenário ocorre em
compilações próprias ou em backports que não expõem a diretiva.

Ação: consultar o changelog do fornecedor da distribuição para confirmar a
aplicação do patch.

```bash
apt changelog php8.2 | head -40      # Debian/Ubuntu
rpm -q --changelog php | head -40    # RHEL/Fedora
```

### CORRIGIDO

A diretiva `memory_hard_limit_percent` foi encontrada. O patch está presente,
mesmo que a versão exibida seja anterior à corrigida. Esse cenário é comum em
distribuições Linux que aplicam backports de segurança mantendo o número de
versão upstream.

Ação: nenhuma.

## Versões corrigidas consideradas

| Ramificação | Versão corrigida |
|---|---|
| PHP 8.2 | 8.2.35 |
| PHP 8.3 | 8.3.36 |
| PHP 8.4 | 8.4.27 |
| PHP 8.5 | 8.5.12 |

Ramificações anteriores ao PHP 8.2 são tratadas como vulneráveis, por estarem
fora do ciclo de suporte oficial.

## Estrutura do arquivo

```text
php2shell_check.php   Script de verificação
README.md             Este documento
```

## Segurança operacional

- O script não explora a falha. Ele apenas lê a versão e a configuração.
- Quando executado via web, restringir o acesso por IP, autenticação básica
  ou regra equivalente no servidor web.
- Remover o arquivo do diretório público após a verificação.
- Não copiar o arquivo para diretórios com listagem aberta.

## Limitações

- Verifica apenas o binário PHP que executa o script. Servidores com múltiplas
  versões do PHP exigem execução em cada binário.
- Não retorna código de saída distinto por status. A conclusão está apenas no
  texto impresso.
- Instalações compiladas manualmente não são cobertas por gerenciadores de
  pacotes.
- Não substitui auditoria de segurança completa.

## Quando não confiar apenas no número de versão

Distribuições como Debian e Ubuntu aplicam patches de segurança mantendo o
número de versão upstream. Nesses casos, a versão exibida é anterior à
corrigida, mas o código já está protegido. Por isso o script verifica a
diretiva `memory_hard_limit_percent` e prioriza esse sinal sobre a comparação
de versão.

Verificação manual complementar:

```bash
php -i | grep memory_hard_limit_percent
php -r 'echo PHP_VERSION, PHP_EOL;'
```

Se a diretiva aparecer, o patch está presente.

## Contexto

Em outubro de 2026, a descoberta das vulnerabilidades PHP2Shell levou órgãos
públicos brasileiros a suspender preventivamente o acesso externo a sistemas
como o SEI. As falhas permitem execução remota de código sem autenticação, com
pontuação CVSS v4.0 de 9.2. Ambientes com Apache e `mod_php` apresentam risco
maior. A correção oficial remove a lógica de `longjmp` do tratamento de falta
de memória e introduz a diretiva `memory_hard_limit_percent`.

## Licença

MIT.
```
