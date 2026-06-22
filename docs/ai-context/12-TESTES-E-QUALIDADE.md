# Testes e qualidade

## Ferramentas

- Pest 4.1.2.
- pest-plugin-laravel 4.0.0.
- PHPUnit por baixo do Pest.
- RefreshDatabase nos testes de domínio.
- SQLite em memória, cache array, mail array, fila sync e sessão array em phpunit.xml.
- Laravel Pint disponível para formatação PHP.

## Estrutura

- tests/Feature/Auth: autenticação, reset, verificação e senha.
- tests/Feature: agenda, perfis, clientes, dashboard, estilos e exemplos.
- tests/Unit: apenas ExampleTest.
- tests/Pest.php: bootstrap Pest.
- tests/TestCase.php: base Laravel.

## Execução verificada

Comando executado em 21/06/2026:

php artisan test

Resultado:

- 23 testes passaram.
- 8 testes falharam.
- 67 assertions.

Falhas:

1. Três testes de PasswordResetTest esperam a notificação padrão, mas User envia ResetPasswordNotification customizada.
2. Dois testes de RegistrationTest esperam /register ativo, mas as rotas estão desativadas.
3. ClientesTest não envia data_nascimento, hoje obrigatória no store.
4. ExampleTest espera 200 em /, mas a rota redireciona.
5. StylesTest espera o texto Gestor Barber na página de login e não o encontra.

Portanto, a suite não está verde no estado analisado.

## Cobertura existente

### Agenda

- AgendamentoTest confirma que um usuário autenticado recebe 200 na listagem.
- AgendamentoRoleTest cria agendamentos de barbeiros diferentes e confirma status 200.

Limitação: o teste de papel conta registros no banco, mas não assegura que nomes/agendamentos de outro barbeiro estejam ausentes no HTML.

### Clientes

Existe um teste de criação, atualmente desatualizado e falhando.

### Autenticação

Login/logout, confirmação de senha, atualização, verificação de e-mail e reset possuem testes. Registro e reset possuem expectativas incompatíveis com o código atual.

### Perfil e dashboard

Perfil cobre render, atualização, verificação e exclusão. Dashboard cobre acesso autenticado.

## Áreas sem cobertura aparente

- página pública e token ativo/inativo;
- JSON de configuração pública;
- submit público;
- criação/reutilização de Cliente por e-mail;
- duração e ends_at;
- conflito e concorrência;
- agenda config e imagens;
- jornada/pausa;
- serviços profissionais;
- autorização owner;
- criação/edição/exclusão de agenda;
- cancelamento/reagendamento;
- serviços e combos;
- produtos/estoque;
- financeiro e OpenAI fallback;
- upload;
- notificações customizadas;
- rate limiting público;
- contrato frontend/controller de serviços.

## Factories

- UserFactory cria owner verificado por padrão.
- ClienteFactory cria dados básicos, sem data_nascimento.
- AgendamentoFactory cria início futuro e fim 45 minutos depois.

A factory não prova regra de duração de 45 minutos; é apenas dado de teste.

## Seeders

DatabaseSeeder cria um owner de desenvolvimento. RealisticBarbershopSeeder produz massa ampla. Ele pode gerar horários aleatórios sem garantir ausência de conflito, portanto não deve validar disponibilidade.

## Comandos recomendados

- php artisan test
- php artisan test --filter=NomeDoTeste
- php artisan route:list --except-vendor
- php artisan migrate:status
- vendor/bin/pint --test
- npm run build
- git diff --check

Para mudanças somente Blade, npm run build valida compilação de assets; para mudanças PHP, adicione testes de Feature/Unit e rode a suite afetada e completa.

## Estratégia mínima para agenda

Antes de alterar:

1. congelar o comportamento atual em testes;
2. testar owner e barber;
3. testar agenda pública ativa/inativa;
4. testar serviço/profissional inválidos;
5. testar expediente, pausa e duração;
6. testar sobreposição parcial e total;
7. testar duas confirmações concorrentes;
8. testar idempotência;
9. testar cancelamento/reagendamento;
10. testar falha OpenAI sem afetar reserva.

## Critério de qualidade

Não afirmar que um fluxo funciona porque o arquivo existe. Uma afirmação de funcionamento exige teste executado ou inspeção ponta a ponta consistente. As falhas preexistentes devem ser separadas de regressões introduzidas.
