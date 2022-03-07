<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeCrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CRUD Files';

    private $json;
    private $varModelName;
    private $modelName;
    private $varRepoName;
    private $repoName;
    private $tableName;
    private $FILLABLES;
    private $controllerName;
    private $requestName;
    private $folderViewName;
    private $routeName;
    private $STOREVALIDATIONS;
    private $UPDATEVALIDATIONS;
    private $arrayColumns;
    private $moduleName;
    private $arrayTH;
    private $arrayTD;
    private $exportClassName;
    private $importClassName;
    private $icon;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function setColumnsWithTab($tab = 1)
    {
        $columns = '';
        $i = 0;
        $count = count($this->arrayColumns);
        $tabs = collect(range(1, $tab))->transform(function ($item) {
            return "\t";
        })->toArray();
        $tabs = implode('', $tabs);
        foreach ($this->arrayColumns as $column) {
            if ($i < $count - 1)
                $columns .= "$tabs'$column',\n";
            else
                $columns .= "$tabs'$column',";
            $i++;
        }
        return $columns;
    }

    private function setTab(array $data, $tabSize = 0, $spacing = 2)
    {
        $spaces = collect(range(1, $spacing))->transform(function ($item) {
            return " ";
        })->toArray();
        $spaces = implode('', $spaces);
        $tabs = collect(range(1, $tabSize))->transform(function ($item) use ($spaces) {
            return $spaces;
        })->toArray();
        $tabs = implode('', $tabs);

        $columns = '';
        $count = count($data);
        $i = 0;
        foreach ($data as $column) {
            if ($i < $count - 1)
                $columns .= "$tabs$column\n";
            else
                $columns .= "$tabs$column";
            $i++;
        }
        return $columns;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filename = $this->ask('CRUD Filename? (check example in ' . app_path('Console/Commands/data/crud/files/student.json') . ') type like this [student]');
        if (!$filename) {
            $this->error("CRUD file required");
            return 0;
        }
        $filename = 'student';
        $filepath = app_path('Console/Commands/data/crud/files/' . $filename . '.json');
        if (File::exists($filepath) === false) {
            $this->error("File not found");
            return 0;
        }

        $this->json = json_decode(file_get_contents($filepath));

        $this->modelName      = $modelName = $this->json->model;
        $this->varModelName = Str::camel($modelName);
        $this->repoName       = $modelName . 'Repository';
        $this->varRepoName    = Str::camel($modelName) . 'Repository';
        $controllerName = $this->controllerName = $modelName . 'Controller';
        $exportClassName = $this->exportClassName = $modelName . 'Export';
        $importClassName = $this->importClassName = $modelName . 'Import';
        $this->icon = $this->json->icon;
        $this->moduleName = $this->json->title;
        $this->requestName = $modelName . 'Request';
        $routeName = $this->routeName = $this->folderViewName = $folderViewName = Str::plural(Str::kebab($modelName));
        $modelNameSnake       = Str::snake($modelName);
        $migrationExample     = file_get_contents(
            app_path(
                'Console/Commands/data/crud/migration.php.dummy'
            )
        );
        $this->tableName = $TABLENAME = Str::plural($modelNameSnake);
        $migrationContent = str_replace('TABLENAME', $TABLENAME, $migrationExample);
        $MIGRATIONNAME = 'Create' . $modelName . 'Table';
        $migrationContent = str_replace('MIGRATIONNAME', $MIGRATIONNAME, $migrationContent);
        $FILLABLES = '';
        $STRUCTURE = '';
        $UPDATEVALIDATIONS = '';
        $STOREVALIDATIONS = '';
        $TH = '';
        $TD = '';
        $FORM = '';
        $TYPESVALUE = '';
        $SEEDERCOLUMNS = '';
        $this->arrayColumns = collect($this->json->columns)
            ->pluck('name')
            ->filter(function ($item) {
                return $item !== null;
            })
            ->values()
            ->toArray();
        foreach ($this->json->columns as $column) {
            if ($column->type === 'ai')
                $STRUCTURE .= '$table->id();';
            else if ($column->type === 'timestamps')
                $STRUCTURE .= '$table->timestamps();';
            else if (in_array($column->type, ['date', 'tinyInteger', 'text'])) {
                $STRUCTURE .= '$table->' . $column->type . '(\'' . $column->name . '\');';
                // $FILLABLES .= "\t\t'" . $column->name . "',\n";
                $FILLABLES .= "'" . $column->name . "', ";
                if ($column->type === 'date')
                    $SEEDERCOLUMNS .= "'" . $column->name . '\'' . ' => $faker->date("Y-m-d", $max = date("Y-m-d")), // ganti method fakernya sesuai kebutuhan' . "\n\t\t\t\t";
                else if (isset($column->options)) {
                    $max = count($column->options) - 1;
                    $SEEDERCOLUMNS .= "'" . $column->name . '\'' . ' => $faker->numberBetween(0, ' . $max . '), // ganti method fakernya sesuai kebutuhan' . "\n\t\t\t\t";
                } else
                    $SEEDERCOLUMNS .= "'" . $column->name . '\'' . ' => $faker->numberBetween(0,1000), // ganti method fakernya sesuai kebutuhan' . "\n\t\t\t\t";
            } else {
                $STRUCTURE .= '$table->string(\'' . $column->name . '\', ' . ($column->length ?? 191) . ');';
                // $FILLABLES .= "\t\t'" . $column->name . "',\n";
                $FILLABLES .= "'" . $column->name . "', ";
                $SEEDERCOLUMNS .= "'" . $column->name . '\'' . ' => Str::random(10),' . "\n\t\t\t\t";
            }

            // setup TH dan TD
            if (!($column->type === 'ai' || $column->type === 'timestamps')) {
                $label = $column->label ?? Str::title(str_replace('_', ' ', $column->name));
                $th = "<th class=\"text-center\">{{ __('" . $label . "') }}</th>";
                $TH .= "\t\t$th\n";
                if (isset($column->options)) {
                    $td = "<td>" . '{{ \App\Models\\' . $modelName . '::TYPES[\'' . $column->name . '\'][$item->' . $column->name . "] }}</td>";
                } else {
                    $td = "<td>" . '{{ $item->' . $column->name . " }}</td>";
                }
                $TD .= "\t\t$td\n";
                $this->arrayTH[] = $th;
                $this->arrayTD[] = $td;
            }
            $STRUCTURE .= "\n\t\t\t";

            if (isset($column->validations)) {
                if (isset($column->validations->store)) {
                    $STOREVALIDATIONS .= "\t\t\t'" . $column->name . '\' => ' . json_encode($column->validations->store) . ",\n";
                }
                if (isset($column->validations->update)) {
                    $UPDATEVALIDATIONS .= "\t\t\t\t'" . $column->name . '\' => ' . json_encode($column->validations->store) . ",\n";
                    // $UPDATEVALIDATIONS .= $column->name . ' => ' . json_encode($column->validations->store);
                }
            }

            if (isset($column->options)) {
                $options = json_decode(json_encode($column->options), true);
                $values = collect($options)->pluck('value');
                $newOptions = [];
                $values->each(function ($item) use (&$newOptions, $options) {
                    return $newOptions[(string)$item] = $options[$item]['label'];
                });
                // dd($newOptions);
                // $newOptions
                $options = json_encode($newOptions);
                $TYPESVALUE .= "\n\t\t'" . $column->name . "' => " . $options;
            }

            if (isset($column->form)) {
                $label = $column->label ?? Str::title(str_replace('_', ' ', $column->name));
                switch ($column->form->type) {
                    case 'text':
                        $FORM .= "\t\t\t\t<div class=\"col-md-6\">
                  @include('stisla.includes.forms.inputs.input', ['required'=>true, 'type'=>'text', 'id'=>'$column->name', 'name'=>'$column->name', 'label'=>__('" . $label . "')])
                </div>\n\n";
                        break;
                    case 'email':
                        $FORM .= "\t\t\t\t<div class=\"col-md-6\">
                  @include('stisla.includes.forms.inputs.input-email', ['required'=>true, 'type'=>'email', 'id'=>'$column->name', 'name'=>'$column->name', 'label'=>__('" . $label . "')])
                </div>\n\n";
                        break;
                    case 'password':
                        $FORM .= "\t\t\t\t<div class=\"col-md-6\">
                  @include('stisla.includes.forms.inputs.input-password', ['required'=>true, 'type'=>'text', 'id'=>'$column->name', 'name'=>'$column->name', 'label'=>__('" . $label . "')])
                </div>\n\n";
                        break;
                    case 'image':
                        $FORM .= "\t\t\t\t<div class=\"col-md-6\">
                  @include('stisla.includes.forms.inputs.input', ['required'=>true, 'type'=>'file', 'accept'=>'image/*', 'id'=>'$column->name', 'name'=>'$column->name', 'label'=>__('" . $label . "')])
                </div>\n\n";
                        break;
                    case 'file':
                        $FORM .= "\t\t\t\t<div class=\"col-md-6\">
                  @include('stisla.includes.forms.inputs.input', ['required'=>true, 'type'=>'file', 'accept'=>'*', 'id'=>'$column->name', 'name'=>'$column->name', 'label'=>__('" . $label . "')])
                </div>\n\n";
                        break;
                    case 'number':
                        $FORM .= "\t\t\t\t<div class=\"col-md-6\">
                  @include('stisla.includes.forms.inputs.input', ['required'=>true, 'type'=>'number', 'id'=>'$column->name', 'name'=>'$column->name', 'label'=>__('" . $label . "'), 'min'=>0])
                </div>\n\n";
                        break;
                    case 'time':
                        $FORM .= "\t\t\t\t<div class=\"col-md-6\">
                  @include('stisla.includes.forms.inputs.input', ['required'=>true, 'type'=>'time', 'id'=>'$column->name', 'name'=>'$column->name', 'label'=>__('" . $label . "')])
                </div>\n\n";
                        break;
                    case 'colorpicker':
                        $FORM .= "\t\t\t\t<div class=\"col-md-6\">
                  @include('stisla.includes.forms.inputs.input-colorpicker', ['required'=>true, 'type'=>'text', 'id'=>'$column->name', 'name'=>'$column->name', 'label'=>__('" . $label . "')])
                </div>\n\n";
                        break;
                    case 'date':
                        $FORM .= "\t\t\t\t<div class=\"col-md-6\">
                  @include('stisla.includes.forms.inputs.input', ['required'=>true, 'type'=>'date', 'id'=>'$column->name', 'name'=>'$column->name', 'label'=>__('" . $label . "')])
                </div>\n\n";
                        break;
                    case 'textarea':
                        $FORM .= "\t\t\t\t<div class=\"col-md-6\">
                  @include('stisla.includes.forms.editors.textarea', ['required'=>true, 'id'=>'$column->name', 'name'=>'$column->name', 'label'=>__('" . $label . "')])
                </div>\n\n";
                        break;
                    case 'select2':
                        $multiple = $column->form->multiple ?? false ? 'true' : 'false';
                        $options = json_encode($column->form->options ?? []);
                        $FORM .= "\t\t\t\t<div class=\"col-md-6\">
                  @include('stisla.includes.forms.selects.select2', ['required'=>true, 'id'=>'$column->name', 'name'=>'$column->name', 'label'=>__('" . $label . "'), 'options'=>" . $options . ", 'multiple'=>" . $multiple . "])
                </div>\n\n";
                        break;
                    case 'select':
                        $options = json_encode($column->form->options ?? []);
                        $FORM .= "\t\t\t\t<div class=\"col-md-6\">
                  @include('stisla.includes.forms.selects.select', ['required'=>true, 'id'=>'$column->name', 'name'=>'$column->name', 'label'=>__('" . $label . "'), 'options'=>" . $options . "])
                </div>\n\n";
                        break;
                    case 'radio':
                        $options = json_decode(json_encode($column->options), true);
                        $values = collect($options)->pluck('value');
                        $newOptions = [];
                        $values->each(function ($item) use (&$newOptions, $options) {
                            return $newOptions[(string)$item] = $options[$item]['label'];
                        });
                        // dd($newOptions);
                        // $newOptions
                        $options = json_encode($newOptions);
                        $FORM .= "\t\t\t\t<div class=\"col-md-6\">
                  @include('stisla.includes.forms.inputs.input-radio-toggle', ['required'=>true, 'id'=>'$column->name', 'name'=>'$column->name', 'label'=>__('" . $label . "'), 'options'=>" . $options . "])
                </div>\n\n";
                        break;
                }
            }
        }

        $this->UPDATEVALIDATIONS = $UPDATEVALIDATIONS;
        $this->STOREVALIDATIONS = $STOREVALIDATIONS;
        $this->FILLABLES = $FILLABLES;

        $migrationContent = str_replace('STRUCTURE', $STRUCTURE, $migrationContent);
        $migrationFiles = File::files(database_path('migrations'));
        $migrationFiles = array_map(function ($item) {
            return substr(
                str_replace(database_path('migrations') . '/', '', $item->getPathname()),
                18
            );
        }, $migrationFiles);
        // dd($migrationFiles);
        // if (
        //     // !Str::contains(
        //     //     $migrationFiles[0]->getPathname(),
        //     //     '_create_' . $modelNameSnake . '_table'
        //     // )
        //     !in_array('create_' . $modelNameSnake . '_table.php', $migrationFiles)
        // ) {
        //     file_put_contents($migrationPath = database_path('migrations\\' . date('Y_m_d_His') . '_create_' . $modelNameSnake . '_table.php'), $migrationContent);
        // }
        $migrationFileNames = getFileNamesFromDir(database_path('migrations'));
        $exist = false;
        foreach ($migrationFileNames as $migrationFileName) {
            $contain = Str::contains($migrationFileName, '_create_' . $modelNameSnake . '_table.php');
            if ($contain) {
                $migrationPath = database_path('migrations/' . $migrationFileName);
                file_put_contents($migrationPath, $migrationContent);
                $exist = true;
                break;
            }
            // if($migrationFileName)
        }
        if ($exist === false) {
            $migrationPath = database_path('migrations/' . date('Y_m_d_His') . '_create_' . $modelNameSnake . '_table.php');
            file_put_contents($migrationPath, $migrationContent);
        }

        // CREATE MODEL
        $modelExample = file_get_contents(
            app_path(
                'Console/Commands/data/crud/model.php.dummy'
            )
        );
        $modelContent = str_replace('TABLENAME', $TABLENAME, $modelExample);
        $modelContent = str_replace('FILLABLES', $this->setColumnsWithTab(2), $modelContent);
        $modelContent = str_replace('MODELNAME', $modelName, $modelContent);
        $modelContent = str_replace('TYPESVALUE', '[' . $TYPESVALUE . "\n\t]", $modelContent);
        file_put_contents($modelPath = app_path('Models/' . $modelName . '.php'), $modelContent);

        // CREATE REPOSITORY
        $repositoryFile = file_get_contents(app_path('Console/Commands/data/NameRepository.php.dummy'));
        $repositoryFile = str_replace('ModelName', $modelName, $repositoryFile);
        $repositoryFile = str_replace('NameRepository', $modelName . 'Repository', $repositoryFile);
        $filepath = app_path('Repositories/' . $modelName . 'Repository.php');
        file_put_contents($repositoryPath = $filepath, $repositoryFile);

        // CREATE CONTROLLER
        $controllerFile = file_get_contents(
            app_path(
                'Console/Commands/data/crud/controller2.php.dummy'
            )
        );
        $controllerFile = str_replace('TITLE', $this->json->title, $controllerFile);
        $controllerFile = str_replace('CONTROLLERNAME', $modelName . 'Controller', $controllerFile);
        $controllerFile = str_replace('VARREPOSITORYNAME', Str::camel($modelName) . 'Repository', $controllerFile);
        $controllerFile = str_replace('REPOSITORYNAME', $modelName . 'Repository', $controllerFile);
        $controllerFile = str_replace('VARMODELNAME', Str::camel($modelName), $controllerFile);
        $controllerFile = str_replace('MODELNAME', $modelName, $controllerFile);
        $controllerFile = str_replace('REQUESTNAME', $modelName . 'Request', $controllerFile);
        $controllerFile = str_replace('COLUMNS', $this->setColumnsWithTab(3), $controllerFile);
        $controllerFile = str_replace('FOLDERVIEW', $folderViewName, $controllerFile);
        $controllerFile = str_replace('ROUTENAME', $this->routeName, $controllerFile);
        $controllerFile = str_replace('EXPORTCLASSNAME', $this->exportClassName, $controllerFile);
        $controllerFile = str_replace('IMPORTCLASSNAME', $this->importClassName, $controllerFile);
        $filepath = app_path('Http/Controllers/' . $modelName . 'Controller.php');
        file_put_contents($controllerPath = $filepath, $controllerFile);

        // CREATE REQUEST
        $requestFile = file_get_contents(
            app_path(
                'Console/Commands/data/crud/request.php.dummy'
            )
        );
        $requestFile = str_replace('REQUESTNAME', $modelName . 'Request', $requestFile);
        $requestFile = str_replace('UPDATEVALIDATIONS', $UPDATEVALIDATIONS, $requestFile);
        $requestFile = str_replace('STOREVALIDATIONS', $STOREVALIDATIONS, $requestFile);
        $filepath    = app_path('Http/Requests/' . $modelName . 'Request.php');
        file_put_contents($requestPath = $filepath, $requestFile);

        // CREATE VIEWS
        $viewIndexFile = file_get_contents(app_path('Console/Commands/data/crud/views/index2.blade.php.dummy'));
        $viewIndexFile = str_replace('TITLE', $this->json->title, $viewIndexFile);
        $viewIndexFile = str_replace('ROUTE', $routeName, $viewIndexFile);
        $viewIndexFile = str_replace('ICON', $this->json->icon, $viewIndexFile);
        $viewIndexFile = str_replace('TH', $this->setTab($this->arrayTH, 11, 2), $viewIndexFile);
        $viewIndexFile = str_replace('TD', $this->setTab($this->arrayTD, 12, 2), $viewIndexFile);
        $folder = base_path('resources/views/stisla/') . $folderViewName;
        // dd($folder);
        if (!file_exists($folder)) {
            File::makeDirectory($folder);
            // mkdir($folder);
        }
        $filepath    = $folder . '/index.blade.php';
        file_put_contents($viewIndexPath = $filepath, $viewIndexFile);

        $viewFormFile = file_get_contents(app_path('Console/Commands/data/crud/views/form.blade.php.dummy'));
        $viewFormFile = str_replace('TITLE', $this->json->title, $viewFormFile);
        $viewFormFile = str_replace('ROUTE', $routeName, $viewFormFile);
        $viewFormFile = str_replace('ICON', $this->json->icon, $viewFormFile);
        $viewFormFile = str_replace('FORM', $FORM, $viewFormFile);
        $filepath    = $folder . '/form.blade.php';
        file_put_contents($viewCreatePath = $filepath, $viewFormFile);

        $viewExportExcelFile = file_get_contents(app_path('Console/Commands/data/crud/views/export-excel-example.blade.php.dummy'));
        $viewExportExcelFile = str_replace('TH', $this->setTab($this->arrayTH, 3, 2), $viewExportExcelFile);
        $viewExportExcelFile = str_replace('TD', $this->setTab($this->arrayTD, 4, 2), $viewExportExcelFile);
        $viewExportExcelPath    = $folder . '/export-excel-example.blade.php';
        file_put_contents($viewExportExcelPath, $viewExportExcelFile);

        $viewExportPdf = file_get_contents(app_path('Console/Commands/data/crud/views/export-pdf.blade.php.dummy'));
        $viewExportPdf = str_replace('TITLE', $this->json->title, $viewExportPdf);
        $viewExportPdf = str_replace('TH', $this->setTab($this->arrayTH, 4, 2), $viewExportPdf);
        $viewExportPdf = str_replace('TD', $this->setTab($this->arrayTD, 5, 2), $viewExportPdf);
        $viewExportExcelPath    = $folder . '/export-pdf.blade.php';
        file_put_contents($viewExportExcelPath, $viewExportPdf);

        $exportExcelFile = file_get_contents(app_path('Console/Commands/data/crud/export.php.dummy'));
        $exportExcelFile = str_replace('FOLDERVIEW', $folderViewName, $exportExcelFile);
        $exportExcelFile = str_replace('MODELNAME', $modelName, $exportExcelFile);
        $exportExcelFile = str_replace('FILLABLES', $this->setColumnsWithTab(4), $exportExcelFile);
        $exportExcelPath = app_path('Exports/' . $modelName . 'Export.php');
        file_put_contents($exportExcelPath, $exportExcelFile);

        $importExcelFile = file_get_contents(app_path('Console/Commands/data/crud/import.php.dummy'));
        $importExcelFile = str_replace('MODELNAME', $modelName, $importExcelFile);
        $importExcelPath = app_path('Imports/' . $modelName . 'Import.php');
        file_put_contents($importExcelPath, $importExcelFile);

        // SEEDER
        $seederFile = file_get_contents(app_path('Console/Commands/data/crud/seeder.php.dummy'));
        $seederFile = str_replace('MODELNAME', $modelName, $seederFile);
        $seederFile = str_replace('SEEDERCOLUMNS', $SEEDERCOLUMNS, $seederFile);
        $seederPath = database_path('seeders/' . $modelName . 'Seeder.php');
        file_put_contents($seederPath, $seederFile);

        // MENU
        $menuContent = file_get_contents(app_path('Console/Commands/data/crud/menu.json.dummy'));
        $menuContent = str_replace('TITLE', $this->json->title, $menuContent);
        $menuContent = str_replace('ROUTENAME', $this->routeName, $menuContent);
        $menuContent = str_replace('ICON', $this->icon, $menuContent);
        $menuContent = str_replace('PERMISSION', $this->json->title, $menuContent);
        $menuPath = database_path('seeders/data/menu-modules/' . $this->routeName . '.json');
        file_put_contents($menuPath, $menuContent);

        if (isset($migrationPath))
            $this->info('Created migration file => ' . $migrationPath);
        $this->info('Created seeder file => ' . $seederPath);
        $this->info('Created model file => ' . $modelPath);
        $this->info('Created controller file => ' . $controllerPath);
        $this->info('Created repository file => ' . $repositoryPath);
        $this->info('Created request file => ' . $requestPath);
        $this->info('Created export excel file => ' . $exportExcelPath);
        $this->info('Created import excel file => ' . $importExcelPath);
        $this->info('Created view index file => ' . $viewIndexPath);
        $this->info('Created form index file => ' . $viewCreatePath);
        // $this->info('Don\'t forget to run php artisan migrate');
        // $this->info('copy this to your route file 👇');

        // for copy route
        $fullControllerName = '\App\Http\Controllers\\' . $controllerName . '::class';
        // $this->info('Route::get(\'' . $routeName . '/pdf\', [' . $fullControllerName . ', \'pdf\'])->name(\'' . $routeName . '.pdf\');');
        // $this->info('Route::get(\'' . $routeName . '/csv\', [' . $fullControllerName . ', \'csv\'])->name(\'' . $routeName . '.csv\');');
        // $this->info('Route::get(\'' . $routeName . '/json\', [' . $fullControllerName . ', \'json\'])->name(\'' . $routeName . '.json\');');
        // $this->info('Route::get(\'' . $routeName . '/excel\', [' . $fullControllerName . ', \'excel\'])->name(\'' . $routeName . '.excel\');');
        // $this->info('Route::get(\'' . $routeName . '/import-excel-example\', [' . $fullControllerName . ', \'importExcelExample\'])->name(\'' . $routeName . '.import-excel-example\');');
        // $this->info('Route::post(\'' . $routeName . '/import-excel\', [' . $fullControllerName . ', \'importExcel\'])->name(\'' . $routeName . '.import-excel\');');
        // $this->info('Route::resource(\'' . $routeName . '\', ' . $fullControllerName . ');');

        $this->apiController();
        $this->apiRequest();
        $this->permission();
        $this->routing();
        return 0;
    }

    private function apiController()
    {

        $master  = app_path('Console/Commands/data/crud/apicontroller.php.dummy');
        $content = file_get_contents($master);

        // replace specific var
        $content = str_replace('TITLE', $this->json->title, $content);
        $content = str_replace('CONTROLLERNAME', $this->controllerName, $content);
        $content = str_replace('VARREPOSITORYNAME', $this->varRepoName, $content);
        $content = str_replace('REPOSITORYNAME', $this->repoName, $content);
        $content = str_replace('VARMODELNAME', $this->varModelName, $content);
        $content = str_replace('MODELNAME', $this->modelName, $content);
        $content = str_replace('REQUESTNAME', $this->requestName, $content);
        $content = str_replace('COLUMNS', $this->setColumnsWithTab(3), $content);
        $content = str_replace('FOLDERVIEW', $this->folderViewName, $content);

        // save to specific path
        $filepath = app_path('Http/Controllers/Api/' . $this->controllerName . '.php');
        file_put_contents($filepath, $content);
    }

    private function apiRequest()
    {
        $path    = app_path('Console/Commands/data/crud/request.php.dummy');
        $content = file_get_contents($path);

        $content = str_replace('REQUESTNAME', $this->requestName, $content);
        $content = str_replace('UPDATEVALIDATIONS', $this->UPDATEVALIDATIONS, $content);
        $content = str_replace('STOREVALIDATIONS', $this->STOREVALIDATIONS, $content);
        $content = str_replace("namespace App\Http\Requests;", "namespace App\Http\Requests\Api;", $content);

        $filepath = app_path('Http/Requests/Api/' . $this->requestName . '.php');
        file_put_contents($filepath, $content);
    }

    private function permission()
    {
        $path    = app_path('Console/Commands/data/crud/permission.json.dummy');
        $content = file_get_contents($path);

        $content = str_replace('MODULENAME', $this->moduleName, $content);

        $filepath = database_path('seeders/data/permission-modules/' . $this->routeName . '.json');
        file_put_contents($filepath, $content);
    }

    private function routing()
    {
        $path    = app_path('Console/Commands/data/crud/route.php.dummy');
        $content = file_get_contents($path);

        $content = str_replace('CONTROLLERNAME', $this->controllerName, $content);
        $content = str_replace('ROUTENAME', $this->routeName, $content);

        $filename = Str::singular($this->routeName);
        $filepath = base_path('routes/modules/' . $filename . '.php');
        file_put_contents($filepath, $content);
    }
}
