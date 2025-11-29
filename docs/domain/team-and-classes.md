# Equipe e classes internas

## Objetivo
- Gerenciar os integrantes do time MM Criativos (cadastro, papeis, aprovação) e o repertório de classes que descrevem hierarquia, habilidades e posicionamento.
- Documentar o fluxo de atribuição de classes, redes sociais e atualização de fotos para manter o perfil do time atualizado no painel administrativo.

## Rotas e controllers
- `App\Http\Controllers\Admin\TeamController` (rotas `admin.team.*` em `routes/web.php:279-289`) implementa:
  - `index` para listar membros e permitir alteração de papel/approve/excluir.
  - `edit/update` para atualizar perfil, redes sociais, classes e foto via `ImageUploadService`.
  - `updateRole`, `approve` e `destroy` com regras de segurança (não remove o último admin nem o usuário logado).
- `App\Http\Controllers\Admin\ClasseController` (`admin.classes.*` em `routes/web.php:292-298`) responde pelas telas de classe, validàre hierarquia e skills, e recebe skills como string ou array para persistir JSON.

## Dados e relacionamentos
- `app/Models/User` inclui relações `socialMedias()` e `classes()` com tabelas pivô `social_media_user` e `class_user`.
- `app/Models\Classe` guarda `hierarquia`, `classe`, `description`, `skills` (cast para array) e `belongsToMany(User::class)` com timestamps.
- As migrações envolvidas:
  - `2025_11_03_003844_create_classes_table.php`: `classes` com hierarquia (1 primária, 2 secundária, 3 final), descricao e skills JSON.
  - `2025_11_03_010745_create_class_user_table.php`: pivot `class_user` assegura unicidade por `user_id`/`class_id`.
  - `2025_11_02_215332_create_social_media_user_table.php`: pivot `social_media_user` armazena `url` por rede e garante unicidade.

## Perfil do time
- `resources/views/admin/team/index.blade.php` apresenta o card de membros, botões para aprovar/excluir, e acesso às classes (`admin.classes.index`).
- `resources/views/admin/team/edit.blade.php`:
  - Formulario multipart com nome, e-mail, cargo, descricao e upload de foto (preview em JS via `previewImage` e `ImageUploadService` no backend).
  - Rede sociais renderizadas a partir de `SocialMedia::orderBy('name')` e sincronizadas como `socials[id] => url`.
  - Painel de classes com badges agrupadas por `hierarquia` (`classes->groupBy`) e inputs escondidos para `classes[]`, sincronizados com o clique dos botões.
  - Botões disparados via JS (`data-selected`) garantem que apenas as classes marcadas são enviadas.

## Classes administrativas
- `resources/views/admin/team/classes/index.blade.php`: tabela simples com ID, nome, hierarquia e link para editar.
- `resources/views/admin/team/classes/edit.blade.php`: formulario com campos `classe`, `hierarquia`, `description` e `skills` (string ou quebra de linha). O controlador normaliza o texto (`preg_split`, `trim`) para popular o array.
- Hierarquias são listadas com labels (“Primária”, “Secundária”, “Final”) para garantir consistência visual.

## Observações operacionais
- O upload de foto do usuário utiliza `App\Services\Upload\ImageUploadService` e `App\Support\StorageHelper` para deletar versões anteriores e gerar nomes slugificados no disco `public`.
- A sincronização de redes sociais remove valores vazios e salva apenas pares `social_media_id => ['url' => ...]` no pivot.
- A classe do usuário e das redes é atualizada via `sync`, garantindo que alterações removam associações antigas.

Documentação complementar:
- Veja `docs/domain/projects-and-tasks.md` para contexto sobre tarefas e dependências da equipe.
