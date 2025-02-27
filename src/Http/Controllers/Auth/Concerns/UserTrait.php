<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Auth\Concerns;

use Diviky\Bright\Models\Models;

trait UserTrait
{
    public function getUserBy($value, $column = 'id')
    {
        return Models::user()::from('users_view')
            ->where($column, $value)
            ->first();
    }

    public function getUsersBy($value, $column = 'id')
    {
        return Models::user()::from('users_role_view')
            ->where($column, $value)
            ->select(['id', 'username', 'parent_id', 'role_name as role', 'status'])
            ->get();
    }

    public function getUserById($id)
    {
        $id = is_array($id) ? array_map('intval', $id) : intval($id);

        return Models::user()::from('users_role_view')
            ->where('id', $id)
            ->select(['id', 'username', 'parent_id', 'role_name as role', 'status'])
            ->first();
    }

    public function getParentById($id)
    {
        return Models::user()::from('users_role_view')
            ->where('parent_id', $id)
            ->select(['id', 'username', 'parent_id', 'role_name as role', 'status'])
            ->first();
    }

    public function getLinkedUsers($parent_id)
    {
        $rows = $this->getUsersBy($parent_id, 'parent_id');

        $roles = [
            'customer' => 'customers',
            'client' => 'customers',
            'partner' => 'resellers',
            'reseller' => 'resellers',
        ];

        $results = [];
        $results['users'][$parent_id] = $parent_id;
        $results['customers'][$parent_id]['user'] = $this->getUserById($parent_id);

        foreach ($rows as $row) {
            $id = $row->id;
            $role = $row->role;

            if ($role == 'agent') {
                continue;
            }

            $role = $roles[$role];

            $results['users'][$id] = $id;

            $users = [];
            $users[$id] = $row;

            $results[$role][$id]['user'] = $this->getUserById($id);

            if ($role == 'resellers') {
                $users = $this->getChildUsers($id, $users);

                $results[$role][$id]['customers'] = $users;
            }

            $results['users'] = \array_merge($results['users'], \array_keys($users));
        }

        return $results;
    }

    protected function getChildUsers($parent_id, &$users = []): array
    {
        $rows = $this->getUsersBy($parent_id, 'parent_id');

        if (empty($rows)) {
            return $users;
        }

        foreach ($rows as $row) {
            $users[$row->id] = $row;
            $this->getChildUsers($row->id, $users);
        }

        return $users;
    }

    protected function getUserParents($user_id): array
    {
        $user = $this->getUserById($user_id);

        return $this->getUserParentsLoop($user->parent_id);
    }

    protected function getUserParentsLoop($parent_id, &$users = [])
    {
        $rows = $this->getUsersBy($parent_id, 'id');

        if (empty($rows)) {
            return $users;
        }

        foreach ($rows as $row) {
            $users[$row->id] = $row;
            $this->getUserParentsLoop($row->parent_id, $users);
        }

        return $users;
    }
}
