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

    protected function getClassname($table_name)
    {
        return \Illuminate\Support\Str::studly($table_name);
    }

    protected function getClassnameWithNamespace($table_name)
    {
        return "\\App\\Models\\" . $this->getClassname($table_name);
    }

    protected function getClassFilePath($table_name)
    {
        return app_path("/Models/".$this->getClassname($table_name).".php");
    }

    protected function getReferenceNameFromItemData($item_data,$upper = false)
    {
        $referenceName = trim(strtolower(str_replace(["-"," "],"_",$item_data)));
        return ($upper)?strtoupper($referenceName):$referenceName;
    }

    protected function rewriteClass($file_path,array $search_replace_data=[])
    {
        if(count($search_replace_data)){
            $content = file_get_contents($file_path);
            foreach($search_replace_data as $key_search => $replace_data) {
                $replace_data['data'][] = $key_search; // di-include-kan di akhir data supaya tetap tergenerate key_search sehingga bisa dipake buat replace dikemudian hari

                $replace_string = implode($replace_data['glue'],$replace_data['data']);
                $content = str_replace($key_search,$replace_string,$content);
            }
            file_put_contents($file_path,$content);
        }
    }

    public function proses()
    {
        foreach ($this->tables as $table_name => $column_ref) {
            if($table_name == "settings") {
                $this->prosesTabelSettings($table_name,$column_ref);
            } else {
                $this->prosesTabel($table_name,$column_ref);
            }
        }
    }

    protected function prosesTabelSettings($table_name,$column_ref)
    {
        $classname_full = $this->getClassnameWithNamespace($table_name);
        $model_file_path = $this->getClassFilePath($table_name);

        $available_methods = $classname_full::AVAILABLE_SETTINGS;
        $methods_comment = [];
        $methods_available = [];
        $blnSave = false;
        $data = $classname_full::all();
        if(count($data)) {
            foreach ($data as $model) {
                $reference_name = $this->getReferenceNameFromItemData($model->$column_ref);
                if(!in_array($reference_name,$available_methods)) {
                    $blnSave = true;
                    $methods_available[] = "'$reference_name',";
                    $methods_comment[] = sprintf('* @method static %s %s($default,$increment=0,$type="string") "Setting untuk %s"',$model->type,$reference_name,$model->info);
                }
            }

            if($blnSave) {
                $this->rewriteClass($model_file_path,[
                    "* **************" => [
                        "glue" => "\n ",
                        "data" => $methods_comment
                    ],
                    "/* -available-settings- */" => [
                        "glue" => "\n        ",
                        "data" => $methods_available
                    ],
                ]);
            }
        }
    }

    protected function prosesTabel($table_name,$column_ref)
    {
        $classname_full = $this->getClassnameWithNamespace($table_name);
        $model_file_path = $this->getClassFilePath($table_name);
        $reflection = new ReflectionClass($classname_full);
        $constants = [];
        $blnSave = false;
        $data = $classname_full::all();
        if(count($data)) {
            foreach ($data as $model) {
                $reference_name = $this->getReferenceNameFromItemData($model->$column_ref,true);
                if(!array_key_exists($reference_name,$reflection->getConstants())){
                    $blnSave = true;
                    $constants[] = "const " . strtoupper($reference_name) ." = ".$model->id .";";
                    if($table_name == "roles") {
                        $constants[] = "const " . strtoupper($reference_name) ."_STRING = '".$model->$column_ref ."';";
                    }
                }
            }

            if($blnSave) {
                $this->rewriteClass($model_file_path,[
                    "/* -constant-definition- */" => [
                        "glue" => "\n    ",
                        "data" => $constants
                    ]
                ]);
            }
        }
    }
}
