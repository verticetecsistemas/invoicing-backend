<?php

namespace App\Utils;

use App\Models\Estados;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Empresas;

class AppUtils
{
    

    public function valida_cnpj($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
        
        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;
    
        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj))
            return false;	
    
        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
    
        $resto = $soma % 11;
    
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
            return false;
    
        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
    
        $resto = $soma % 11;
    
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
    public function mascara_cnpj($cnpj)
    {
        $new_cnpj=substr($cnpj,0,2).'.'.substr($cnpj,2,3).'.'.substr($cnpj,5,3).'/'.substr($cnpj,8,4).'-'.substr($cnpj,12,2);
        return $new_cnpj;
    }
    public function unmask_cnpj($cnpj){
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
        return $cnpj;
    }

    
    public function capitalizeText($text,$limit){

        $arr=explode(' ',$text);
        $newtext='';
        for($a=0;$a<count($arr);$a++)
        {
            if(strlen($arr[$a])>$limit)
               $arr[$a]=mb_convert_case($arr[$a], MB_CASE_TITLE_SIMPLE, "UTF-8");

            $newtext.=' '.$arr[$a];   
               
        }
        

        return $newtext;

    }

    public function queryCnpj($cnpj)
    {
        //primeiro procura na pibe
        
            $cnpjUrl = 'https://receitaws.com.br/v1/cnpj/' . $cnpj;
            $cnpjRequest = Http::get($cnpjUrl);

            $dados = $cnpjRequest->json();            
        return ['dados'=>$dados];
        /* return  response()->json([
            'dados' => $dados,            
            'frompibe'=> $frompibe
        ],200) ; */
        
    }

    public function clearString($text)
    {
        $newtext = str_replace("\n", ' ', $text);
        $newtext = str_replace("\r", ' ', $newtext);

        return $newtext;
    }

    public function lastMonth(){
        $month=date('m');
        
        $month=$month-1;
        if($month==0)
           $month=12;

        if($month==1||$month==3||$month==5||$month==7||$month==8||$month==10||$month==12)
        {
            $dayFinal=31;
        }elseif($month==2)
        {
            $dayFinal=29;
        }else{
            $dayFinal=30;
        }

        $dt_ini = date('Y').'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-01';
        $dt_final = date('Y').'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.$dayFinal;

        return ['dt_ini'=>$dt_ini,'dt_final'=>$dt_final];
    }

    public function ncmMask($ncm){
        $ncm=str_replace('.','',$ncm);
        $new_ncm = substr($ncm,0,4).'.'.substr($ncm,4,2).'.'.substr($ncm,6,2);

        return $new_ncm;
    }

    public function restoreEstados(){
        $estados=Estados::with('municipios')->orderBy('sigla')->get();
        return $estados;
    }
}
