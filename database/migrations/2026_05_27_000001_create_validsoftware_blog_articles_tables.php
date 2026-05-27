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
        Schema::create('validsoftware_blog_projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('local_path')->nullable();
            $table->string('client_name')->nullable();
            $table->string('public_name')->nullable();
            $table->string('status', 50)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('validsoftware_blog_articles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('blog_project_id');
            $table->string('title');
            $table->string('type', 80)->default('project_case_study');
            $table->string('status', 80)->default('not_started');
            $table->string('next_action')->nullable();
            $table->text('topic_summary')->nullable();
            $table->longText('technical_notes')->nullable();
            $table->longText('article_notes')->nullable();
            $table->longText('internal_notes')->nullable();
            $table->string('draft_doc_link', 1000)->nullable();
            $table->string('published_url', 1000)->nullable();
            $table->date('sent_to_vali_at')->nullable();
            $table->date('published_at')->nullable();
            $table->timestamps();

            $table->foreign('blog_project_id')
                ->references('id')
                ->on('validsoftware_blog_projects')
                ->cascadeOnDelete();
        });

        $now = now();

        $createProject = function (array $project) use ($now): int {
            return DB::table('validsoftware_blog_projects')->insertGetId(array_merge([
                'slug' => Str::slug($project['name']),
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ], $project));
        };

        $createArticle = function (int $projectId, array $article) use ($now): void {
            DB::table('validsoftware_blog_articles')->insert(array_merge([
                'blog_project_id' => $projectId,
                'type' => 'project_case_study',
                'status' => 'published',
                'next_action' => 'Adauga URL-ul publicat, daca lipseste.',
                'created_at' => $now,
                'updated_at' => $now,
            ], $article));
        };

        $publishedProjects = [
            [
                'project' => [
                    'name' => 'Blueprint',
                    'local_path' => 'C:\\laragon\\www\\blue-print',
                    'public_name' => 'Blue Print',
                    'notes' => 'Articol deja publicat. Evita suprapunerea pe prezentarea generala a proiectului.',
                ],
                'article' => [
                    'title' => 'Blue Print - platforma interna de management',
                    'topic_summary' => 'Prezentare proiect despre transformarea proceselor fragmentate intr-o platforma interna.',
                    'article_notes' => 'Deja acoperit ca studiu de caz.',
                ],
            ],
            [
                'project' => [
                    'name' => 'Maseco',
                    'local_path' => 'C:\\laragon\\www\\maseco-express',
                    'public_name' => 'Maseco Expres',
                    'notes' => 'Articol deja publicat. Evita o noua prezentare generala fara un unghi tehnic nou.',
                ],
                'article' => [
                    'title' => 'Platforma software pentru managementul operatiunilor de transport - Maseco Expres',
                    'topic_summary' => 'Studiu de caz pentru aplicatia de management operational in transport.',
                    'article_notes' => 'Deja acoperit ca studiu de caz.',
                ],
            ],
            [
                'project' => [
                    'name' => 'Politia Focsani',
                    'local_path' => 'C:\\laragon\\www\\politialocalafocsani',
                    'public_name' => 'Politia Locala Focsani',
                    'notes' => 'Articol deja publicat.',
                ],
                'article' => [
                    'title' => 'Politia Focsani - proiect prezentat pe blog',
                    'topic_summary' => 'Proiect deja prezentat. Completeaza titlul exact si URL-ul publicat cand este nevoie.',
                    'article_notes' => 'Deja acoperit ca studiu de caz.',
                ],
            ],
            [
                'project' => [
                    'name' => 'Theranova',
                    'local_path' => 'C:\\laragon\\www\\theranova',
                    'public_name' => 'Theranova',
                    'notes' => 'Articol deja publicat.',
                ],
                'article' => [
                    'title' => 'Ecosistem digital integrat pentru un centru medical specializat - Theranova',
                    'topic_summary' => 'Studiu de caz pentru ecosistem digital medical.',
                    'article_notes' => 'Deja acoperit ca studiu de caz.',
                ],
            ],
            [
                'project' => [
                    'name' => 'Autogns',
                    'local_path' => 'C:\\laragon\\www\\autogns',
                    'public_name' => 'AUTO GNS Focsani',
                    'notes' => 'Articol deja publicat.',
                ],
                'article' => [
                    'title' => 'Aplicatie personalizata de management pentru service-ul auto AUTO GNS Focsani',
                    'topic_summary' => 'Studiu de caz pentru aplicatia de management service auto.',
                    'article_notes' => 'Deja acoperit ca studiu de caz.',
                ],
            ],
            [
                'project' => [
                    'name' => 'Lorand Wood Factory',
                    'local_path' => 'C:\\laragon\\www\\casedinlemn-vrancea',
                    'client_name' => 'Lorand Wood Factory',
                    'public_name' => 'Case din lemn Vrancea',
                    'notes' => 'Articol tehnic/problem-solution scris despre integrarea WooCommerce cu stocul intern.',
                ],
                'article' => [
                    'title' => 'Cum am legat comenzile WooCommerce de stocul intern pentru Lorand Wood Factory',
                    'type' => 'technical_problem_solution',
                    'topic_summary' => 'Sincronizare WooCommerce, SKU-uri principale si aliasuri, miscari diferentiale de stoc si evitarea dublarii scaderilor.',
                    'technical_notes' => 'Unghi deja folosit: WooCommerce order sync -> local stock movements -> SKU alias resolution -> stock pushback.',
                    'article_notes' => 'Articol pregatit pentru Vali in format DOCX.',
                    'draft_doc_link' => 'C:\\Users\\Andrei\\Documents\\Blog Validsoftware\\articol-tehnic-casedinlemn-lorand-woocommerce-stoc.docx',
                ],
            ],
            [
                'project' => [
                    'name' => 'ArtDentistry',
                    'local_path' => 'C:\\laragon\\www\\artdentistry',
                    'client_name' => 'ArtDentistry',
                    'public_name' => 'ArtDentistry',
                    'notes' => 'Studiu de caz scris despre aplicatia interna de management clinic.',
                ],
                'article' => [
                    'title' => 'Studiu de caz: aplicatie interna pentru managementul activitatii ArtDentistry',
                    'type' => 'project_case_study',
                    'topic_summary' => 'Prezentare proiect: programari, fise de tratament, chestionare, SMS-uri, retete, PDF-uri si cardiologie.',
                    'article_notes' => 'Articol pregatit pentru Vali in format DOCX.',
                    'draft_doc_link' => 'C:\\Users\\Andrei\\Documents\\Blog Validsoftware\\articol-studiu-de-caz-artdentistry.docx',
                ],
            ],
        ];

        foreach ($publishedProjects as $item) {
            $projectId = $createProject($item['project']);
            $createArticle($projectId, $item['article']);
        }

        $notStartedProjects = [
            [
                'name' => 'Evidenta populatiei',
                'local_path' => 'C:\\laragon\\www\\evidentapersoanelorfocsani',
                'public_name' => 'Evidenta populatiei',
            ],
            [
                'name' => 'Mt servicii',
                'local_path' => 'C:\\laragon\\www\\mtserviciiexterne',
                'public_name' => 'MT Servicii',
            ],
            [
                'name' => 'Kids outlet',
                'local_path' => 'C:\\laragon\\www\\kids-outlet',
                'public_name' => 'Kids Outlet',
            ],
        ];

        foreach ($notStartedProjects as $project) {
            $projectId = $createProject(array_merge($project, [
                'notes' => 'Proiect adaugat pentru planificare. Nu a fost inca inspectat pentru articol.',
            ]));

            $createArticle($projectId, [
                'title' => 'De decis articol pentru ' . $project['public_name'],
                'type' => 'project_case_study',
                'status' => 'not_started',
                'next_action' => 'Inspecteaza proiectul local si decide daca primul articol trebuie sa fie studiu de caz sau articol tehnic.',
                'topic_summary' => 'Articol neinceput.',
                'article_notes' => 'Nu scrie inca fara inspectarea proiectului, pentru a evita suprapuneri sau afirmatii generice.',
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('validsoftware_blog_articles');
        Schema::dropIfExists('validsoftware_blog_projects');
    }
};
