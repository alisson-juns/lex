<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    // ─── Hierarquia ───────────────────────────────────────────────
    // super_admin > admin > lawyer > user
    // Um usuário só pode agir sobre outro de nível estritamente inferior.

    private function roleLevel(User $user): int
    {
        return match (true) {
            $user->hasRole('super_admin') => 4,
            $user->hasRole('admin')       => 3,
            $user->hasRole('lawyer')      => 2,
            default                       => 1,
        };
    }

    private function canActOn(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return false; // auto-ação tratada individualmente em cada método
        }

        // super_admin pode agir sobre qualquer um, inclusive outro super_admin
        if ($actor->hasRole('super_admin')) {
            return true;
        }

        return $this->roleLevel($actor) > $this->roleLevel($target);
    }

    // ─── Coleção ──────────────────────────────────────────────────

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_user');
    }

    public function create(User $user): bool
    {
        return $user->can('create_user');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_user');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_user');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_user');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_user');
    }

    // ─── Instância ────────────────────────────────────────────────

    public function view(User $user, User $model): bool
    {
        // Pode ver o próprio perfil, ou qualquer usuário de nível inferior
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can('view_user') && $this->canActOn($user, $model);
    }

    public function update(User $user, User $model): bool
    {
        // Permite editar o próprio perfil (nome, senha, etc.)
        if ($user->id === $model->id) {
            return $user->can('update_user');
        }

        return $user->can('update_user') && $this->canActOn($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('delete_user') && $this->canActOn($user, $model);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->can('force_delete_user') && $this->canActOn($user, $model);
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('restore_user') && $this->canActOn($user, $model);
    }

    public function replicate(User $user, User $model): bool
    {
        return $user->can('replicate_user') && $this->canActOn($user, $model);
    }
}
