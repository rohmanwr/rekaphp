<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });

        $usedUsernames = [];

        DB::table('users')->select('id', 'email')->orderBy('id')->each(function ($user) use (&$usedUsernames) {
            $baseUsername = strtolower(explode('@', $user->email)[0]);
            $baseUsername = preg_replace('/[^a-z0-9._-]/', '', $baseUsername) ?: 'user';
            $username = $baseUsername;

            if (isset($usedUsernames[$username])) {
                $username = $baseUsername . $user->id;
            }

            while (isset($usedUsernames[$username])) {
                $username .= 'x';
            }

            $usedUsernames[$username] = true;

            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
