<?php

namespace Nesazit\MediaManager\Traits;

trait HasMediaPermissions
{
    public function hasPermission($user, string $permission): bool
    {
        if (!$this->permissions || !is_array($this->permissions)) {
            return false;
        }

        $userType = get_class($user);
        $userId = $user->id;

        // Check user-specific permissions
        if (isset($this->permissions['users'][$userType][$userId])) {
            return in_array($permission, $this->permissions['users'][$userType][$userId]);
        }

        // Check role-based permissions (if user has roles)
        if (method_exists($user, 'roles')) {
            foreach ($user->roles as $role) {
                if (isset($this->permissions['roles'][$role->name])) {
                    if (in_array($permission, $this->permissions['roles'][$role->name])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function grantPermission($user, string $permission): void
    {
        $userType = get_class($user);
        $userId = $user->id;

        $permissions = $this->permissions ?? [];

        if (!isset($permissions['users'][$userType][$userId])) {
            $permissions['users'][$userType][$userId] = [];
        }

        if (!in_array($permission, $permissions['users'][$userType][$userId])) {
            $permissions['users'][$userType][$userId][] = $permission;
        }

        $this->update(['permissions' => $permissions]);
    }

    public function revokePermission($user, string $permission): void
    {
        $userType = get_class($user);
        $userId = $user->id;

        $permissions = $this->permissions ?? [];

        if (isset($permissions['users'][$userType][$userId])) {
            $permissions['users'][$userType][$userId] = array_filter(
                $permissions['users'][$userType][$userId],
                fn($p) => $p !== $permission
            );

            if (empty($permissions['users'][$userType][$userId])) {
                unset($permissions['users'][$userType][$userId]);
            }
        }

        $this->update(['permissions' => $permissions]);
    }

    public function shareWithUser($user, array $permissions = ['read']): void
    {
        foreach ($permissions as $permission) {
            $this->grantPermission($user, $permission);
        }
    }
}
