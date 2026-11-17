<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Links a site admin to their academy account, for cross-site login. Run the
 * matching `php artisan sso:link` on hemam-academy-api too: SSO needs both
 * links.
 */
class SsoLink extends Command
{
    protected $signature = 'sso:link
        {email : Email of the site admin}
        {academy_user_id? : id of the account in the academy\'s users table}
        {--unlink : Remove the link instead}';

    protected $description = 'Link a site admin to an academy account for cross-site login (SSO)';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('No site admin with that email.');

            return self::FAILURE;
        }

        if ($this->option('unlink')) {
            $user->forceFill(['academy_user_id' => null])->save();
            $user->tokens()->where('name', 'sso')->delete();
            $this->info("Unlinked {$user->email} and revoked its SSO tokens.");

            return self::SUCCESS;
        }

        $academyUserId = $this->argument('academy_user_id');
        if (! is_numeric($academyUserId) || (int) $academyUserId < 1) {
            $this->error('Pass the academy user id (a positive integer), or --unlink.');

            return self::FAILURE;
        }

        $taken = User::where('academy_user_id', (int) $academyUserId)->whereKeyNot($user->id)->first();
        if ($taken) {
            $this->error("Academy user {$academyUserId} is already linked to {$taken->email}.");

            return self::FAILURE;
        }

        $user->forceFill(['academy_user_id' => (int) $academyUserId])->save();
        $this->info("Linked {$user->email} (site #{$user->id}) to academy user #{$academyUserId}.");
        $this->line("Now on hemam-academy-api: php artisan sso:link <email> {$user->id}");

        return self::SUCCESS;
    }
}
