<?php

namespace Orderonlineid\Permission\Traits;

use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Query\Builder;
use Orderonlineid\Permission\Guard;
use Orderonlineid\Permission\Models\Role;
use ReflectionException;
use function collect;

/**
 * Trait HasRoles
 * @package Orderonlineid\Permission\Traits
 */
trait HasRoles
{
	use HasPermissions;
	public function assignRole(...$roles)
	{
		$roles = collect($roles)
			->map(function ($role) {
				$dataRole = $this->getStoredRole($role);
				return [
					'id' => new ObjectId($dataRole->id),
					'code' => $role
				];
			})
			->whereNotIn('code', collect($this->roles)->pluck('code'));


		if ($roles->empty()) {
			$this->roles = collect($this->roles)->merge($roles)->toArray();
			$this->save();
		}
		return $roles;
	}

	/**
	 * Revoke the given role from the model.
	 *
	 * @param array|string|Role ...$roles
	 *
	 * @return array|Role|string
	 */
	public function removeRole(...$roles)
	{
	   $roles = collect($this->roles)
		   ->whereNotIn('code', $roles)
		   ->toArray();
	   $this->roles = $roles;
	   $this->save();

		return $roles;
	}

	/**
	 * Return Role object
	 *
	 * @param String|Role $role role name
	 *
	 * @return Builder|Model
	 * @throws ReflectionException
	 */
	protected function getStoredRole(Role|string $role): Builder|Model
	{
		$guardName = (new Guard())->getDefaultName();
		if (\is_string($role)) {
			return Role::findByCode($role, $guardName);
		}

		return $role;
	}
}
