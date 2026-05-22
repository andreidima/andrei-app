<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apps_features', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->longText('standard_prompt')->nullable();
            $table->longText('implementation_notes')->nullable();
            $table->longText('verification_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('apps_feature_implementations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('feature_id');
            $table->unsignedInteger('aplicatie_id');
            $table->string('status', 50)->default('not_started');
            $table->string('git_commit', 100)->nullable();
            $table->string('production_url', 500)->nullable();
            $table->date('implemented_at')->nullable();
            $table->date('production_updated_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('differences')->nullable();
            $table->timestamps();

            $table->unique(['feature_id', 'aplicatie_id']);
        });

        $prompt = <<<'PROMPT'
I want you to add the same `/system/database` production database/migration tool that exists in this reference app:

C:\laragon\www\andrei-app

Use that app as the source of truth for controller behavior, routes, Blade page layout/design, button labels, backup behavior, mysqldump test, migration safety confirmation, Composer/shared-hosting section, and temporary backup retention behavior.

In the current app, inspect first:
- Laravel version
- routes/web.php
- main layout/nav Blade file
- existing Tech menu, if any
- existing `/system/database` or migration/database admin page, if any
- existing authorization/admin logic

If there is already a Tech menu or database/migration page, stop and tell me what exists before modifying it. Ask whether to merge, replace, or preserve.

Goal: create the same `/system/database` page as in `C:\laragon\www\andrei-app`, with the same visual design and same behavior as much as this app allows.

Access rules:
- Tech menu should be visible only to authorized tech users.
- My user is Andrei Dima.
- My emails are `adima@validsoftware.ro` and `andrei.dima@usm.ro`.
- My id is usually 1, but prefer email-based authorization.
- If this app already has other users with Tech menu access, preserve their Tech menu access.
- Dangerous actions like running migrations, Composer install, backup download, and mysqldump testing should be restricted to Andrei unless I explicitly approve others.
- Prefer Laravel Gates or the app's existing authorization style.
- Enforce access in routes/controllers, not just the menu.

Implementation guidance:
- Prefer copying/adapting `app/Http/Controllers/System/DatabaseController.php`, `resources/views/system/database.blade.php`, relevant `/system/database` routes from `routes/web.php`, and relevant Tech menu entry from the layout.
- Keep the page design as close as possible to the reference app.
- Do not introduce a new frontend framework.
- Match existing project conventions where needed.
- Do not overwrite existing tooling without asking.

Required behavior:
- Show database connection info, tables, migration status, schema dump status, pending migrations, SQL preview, backup location, and recent backups.
- Add manual DB backup.
- Run pending migrations only after creating a DB backup.
- Use `mysqldump` first, fallback to PHP SQL dumper.
- Add "Test mysqldump availability".
- Keep temporary backups 14 days.
- Do not delete pre-migration backups after download.
- Detect destructive pending migrations and require explicit checkbox confirmation.
- Include Composer helper section if the reference implementation has it and it makes sense in this app.

Verification:
- No need to build tests.
- Run PHP syntax checks.
- Run route list for the new routes.
- If possible, verify the page loads and the mysqldump test works.
- Unless I explicitly say not to, commit and push the changes after verification.
PROMPT;

        $featureId = DB::table('apps_features')->insertGetId([
            'name' => 'Production-safe database tools',
            'slug' => Str::slug('Production-safe database tools'),
            'category' => 'Laravel / DevOps',
            'description' => 'Reusable /system/database page with database inspection, backups, mysqldump support, migration preview, destructive migration confirmation, and shared-hosting Composer helpers.',
            'standard_prompt' => $prompt,
            'implementation_notes' => "Use C:\\laragon\\www\\andrei-app as the reference implementation. Copy/adapt the System\\DatabaseController, system.database Blade view, routes, and Tech menu entry.",
            'verification_notes' => "Run PHP syntax checks, route:list for /system/database, mysqldump availability test, normal backup test, and fallback backup test where possible.",
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $andreiApp = DB::table('apps_aplicatii')
            ->where('nume', 'Andrei - app')
            ->first();

        if ($andreiApp) {
            DB::table('apps_feature_implementations')->insert([
                'feature_id' => $featureId,
                'aplicatie_id' => $andreiApp->id,
                'status' => 'implemented',
                'git_commit' => '4290138',
                'implemented_at' => '2026-05-22',
                'production_updated_at' => '2026-05-22',
                'notes' => 'Reference implementation in this app.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('apps_feature_implementations');
        Schema::dropIfExists('apps_features');
    }
};
