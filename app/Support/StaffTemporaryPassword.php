<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use LogicException;

/**
 * Hold newly issued staff credentials only in the issuing administrator's
 * session. Never persist a recoverable password on the staff record or disk.
 *
 * A password is returned for a PDF/email only if it STILL matches the current
 * account hash; this prevents distributing an invalid password after a reset.
 */
class StaffTemporaryPassword
{
    private function key(string $type, Authenticatable $account): string
    {
        $prefix = match ($type) {
            'instructor' => 'new_instructor_passwords',
            'coordinator' => 'new_coordinator_passwords',
            default => throw new LogicException('Unknown staff account type.'),
        };

        return $prefix . '.' . $account->getAuthIdentifier();
    }

    public function remember(string $type, Authenticatable $account, string $password): void
    {
        if ($password === '' || !Hash::check($password, $account->getAuthPassword())) {
            throw new LogicException('Issued staff password does not match the saved account.');
        }

        Session::put($this->key($type, $account), $password);
    }

    public function forAccount(string $type, Authenticatable $account): ?string
    {
        $key = $this->key($type, $account);
        $password = Session::get($key);

        if (!is_string($password) || $password === '') {
            return null;
        }

        if (!Hash::check($password, $account->getAuthPassword())) {
            Session::forget($key);

            return null;
        }

        return $password;
    }

    public function forget(string $type, Authenticatable $account): void
    {
        Session::forget($this->key($type, $account));
    }
}
