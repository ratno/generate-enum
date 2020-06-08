<?php

namespace Ratno\GenerateEnum;

use ReflectionClass;

class GenerateEnum
{
    protected $tables;

    public function __construct()
    {
        $this->tables = config("generate-enum");
    }

    public function proses()
    {
        foreach ($this->tables as $table_name => $column_ref) {
            $classname = \Illuminate\Support\Str::studly($table_name);
            $classname_full = "\\App\\Models\\$classname";
            $model_file_path = app_path("/Models/$classname.php");

            if($table_name == "settings") {
                $this->prosesTabelSettings($classname_full,$column_ref,$model_file_path);
            } else {
                $this->prosesTabel($classname_full,$column_ref,$model_file_path);
            }
        }
    }

    protected function prosesTabelSettings($classname_full,$column_ref,$model_file_path)
    {
        $available_methods = $classname_full::AVAILABLE_SETTINGS;
        $methods_comment = [];
        $methods_available = [];
        $blnSave = false;
        $data = $classname_full::all();
        if(count($data)) {
            foreach ($data as $model) {
                $reference_name = trim(strtolower(str_replace(["-"," "],"_",$model->$column_ref)));
                if(!in_array($reference_name,$available_methods)) {
                    $blnSave = true;
                    $methods_available[] = "'$reference_name',";
                    $methods_comment[] = sprintf('* @method static %s %s($default,$increment=0,$type="string") "Setting untuk %s"',$model->type,$reference_name,$model->info);
                }
            }

            if($blnSave) {
                $search_method_comment = "* **************";
                $search_array_content = "/* -available-settings- */";
                $methods_comment[] = $search_method_comment;
                $methods_available[] = $search_array_content;

                $content = file_get_contents($model_file_path);
                // pertama tambahkan @method
                $content = str_replace($search_method_comment,implode("\n ",$methods_comment),$content);
                // kedua tambahkan method-available
                $content = str_replace($search_array_content,implode("\n        ",$methods_available),$content);
                file_put_contents($model_file_path,$content);
            }
        }
    }

    protected function prosesTabel($classname_full,$column_ref,$model_file_path)
    {
        $reflection = new ReflectionClass($classname_full);
        $constants = [];
        $blnSave = false;
        $data = $classname_full::all();
        if(count($data)) {
            foreach ($data as $model) {
                $reference_name = trim(strtolower(str_replace(["-"," "],"_",$model->$column_ref)));
                $reference_name = strtoupper($reference_name);
                if(!array_key_exists($reference_name,$reflection->getConstants())){
                    $blnSave = true;
                    $constants[] = "const " . strtoupper($reference_name) ." = ".$model->id .";";
                }
            }

            if($blnSave) {
                $search_constant_anchor = "/* -constant-definition- */";
                $constants[] = $search_constant_anchor;

                $content = file_get_contents($model_file_path);
                $content = str_replace($search_constant_anchor,implode("\n    ",$constants),$content);
                file_put_contents($model_file_path,$content);
            }
        }
    }
}
