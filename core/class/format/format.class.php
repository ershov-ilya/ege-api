<?php
/**
 * Created by PhpStorm.
 * User: ershov-ilya
 * Website: ershov.pw
 * GitHub : https://github.com/ershov-ilya
 * Date: 04.02.2015
 * Time: 21:00
 */

class Format
{
    static public function parse($arr, $format){
        if(!empty($arr['response'])){
            preg_match('/^(([0-9]{0,4})\s){0,1}(.*)/',$arr['response'],$matches);
            if(!empty($matches[2])) $arr['code']=$matches[2];
            if(!empty($matches[3])){
                $arr['message']=$matches[3];
                unset($arr['response']);
            }
        }
        switch($format)
        {
            case 'plain':
                return Format::keyValue($arr);
                break;
            case 'get':
            case 'GET':
                return Format::keyValue($arr, '=', '', '&', true);
                break;
            case 'php':
            case 'phpArray':
                return Format::phpArray($arr, '  ');
                break;
            case 'sql':
            case 'SQL':
                return Format::SQL($arr);
                break;
            case 'json':
            case 'JSON':
            default:
                return Format::JSON($arr);
        }
    }

    static private function generate()
    {
        return array(
            "METHOD" => "GET",
            "id" => "1223",
            "order" => array(
                "ordersum" => 100.12,
                "ordercurrency" => "RUR",
                "description" => "Носки вязаные"
            )
        );
    }

    static public function test()
    {
        $arr = Format::generate();

        print "Raw array():\n";
        print_r($arr);
        print "\n";

        print 'plain:'."\n";
        print Format::parse($arr, 'plain');
        print "\n";
        print "\n";

        print 'get:'."\n";
        print Format::parse($arr, 'get');
        print "\n";
        print "\n";

        print "phpArray:\n";
        print Format::parse($arr, 'phpArray');
        print "\n";
        print "\n";

        print "SQL:\n";
        print Format::parse($arr, 'sql');
        print "\n";

        print "JSON:\n";
        print Format::JSON($arr);
        print "\n";
        print "\n";
    }

    static public function JSON($arr){
        return json_encode($arr);
    }

    static public function keyValue($arr, $equal=' ', $wrap='', $delim="\n", $encode=false, $namespace=''){
        $output = '';
        $index=0;
        $size = sizeof($arr);

        foreach($arr as $key => $val)
        {
            $index++;
            if(is_array($val))
            {
                $output .= Format::keyValue($val, $equal, $wrap, $delim, $encode, $key);
            }
            else
            {
                if($namespace!=''){
                    $output .= $namespace.'.'.$key.$equal.$wrap.$val.$wrap;
                }
                else{
                    $output .= $key.$equal.$wrap.$val.$wrap;
                }
            }
            if($index<$size) {
                $output .= $delim;
            }
        }
        if($encode) return urlencode($output);
        return $output;
    }

    static public function SQL($arr, $namespace=''){
        $columns='';
        $values='';
        $index=0;

        // Удаляем вложенные массивы
        foreach($arr as $key => $val)
        {
            if(is_array($val))
            {
                unset($arr[$key]);
            }

        }

        $size = sizeof($arr);

        foreach($arr as $key => $val)
        {
            $index++;
            $columns.= $key;
            $values.= "'".$val."'";

            if($index<$size)
            {
                $columns.= ',';
                $values.= ',';
            }
        }
        $output = '('.$columns.') VALUES ('.$values.');';
        return $output;
    }

    static public function phpArray($arr, $indent="\t", $parent='', $level=0){
        $index=0;
        $size=sizeof($arr);
        $output='';
        $padding='';
        $i=0;
        while($i<$level){
            $padding .=$indent;
            $i++;
        }

        if($level==0) $output .= $padding;
        $output .= "array(\n";
        foreach($arr as $key => $val)
        {
            if(is_array($val))
            {
                $output .= $padding.$indent.'"'.$key.'" => ';
                $output .= Format::phpArray($val, $indent, $key, $level+1);
                $output .= $padding.$indent.')';
            }
            else
            {
                $output .= $padding.$indent;
                $output .= '"'.$key.'" => ';

                $value_type=gettype($val);
                if($value_type=='integer' || $value_type=='double')
                {
                    $output .= $val;
                }
                else
                {
                    $output .= '"'.$val.'"';
                }
            }

            $index++;
            if($index<$size) $output .= ",";
            $output .= "\n";
        }
        if($parent=='') $output .= $padding.');';
        return $output;
    }


}