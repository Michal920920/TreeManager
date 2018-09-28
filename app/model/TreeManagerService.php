<?php

namespace App\Model;

use Nette;
use Nette\SmartObject;
use Nette\Security\User;
use Nette\Database\Context;
use Tracy\Debugger;
class TreeManagerService {

	use SmartObject;

	/** @var Nette\Database\Context */
	private $database;
	/** @var Nette\Security\User */
	private $user;

	public function __construct(Context $database, User $user) {
		$this->user = $user;
		$this->database = $database;
	}
        
    public function displayTree($id = null) : array{
        
        if($id == null) {   //pokud není specifikován uzel hodnotou ID, vybereme celý strom
            $parent = $this->getParent();
            return $this->getTreeBySides($parent['lft'], $parent['rgt']);
        }
        
        else {  //pokud je uzel specifikován, proběhne dotaz na základě id
            $parent = $this->getNodesById($id);
            
            for($i=0; !empty($parent[$i]); $i++){
                $tree = $this->getTreeBySides($parent[$i]['lft'], $parent[$i]['rgt']);
            }
            return $tree;
        }
    }
    
    /**
     * Vloží do stromu uzel na základě PID a dle hierarchie upraví ostatní uzly
     * @param $pid
     */
    public function addNode($pid, $color){
       
        //uloží uzel předka, kterému chceme přidat dítě
        $parent = $this->getNodeById($pid);
        $last = $this->lastId();
        if(!$parent){   //inicializace prvního uzlu
            $child['id'] = 1;
            $child['pid'] = 0;
            $child['lvl'] = 1;
            $child['lft'] = 1;
            $child['rgt'] = 2;
        }else if($parent === 0){
            return false;
        }else{
            $child['id'] = $last['id'] + 1;
            $child['pid'] = $parent['id'];
            $child['lvl'] = $parent['lvl'] + 1;
        
            $family = $this->getFamilyOldest($child['lvl'], $child['pid']);
            
            if($family){    //pokud má uzel sourozence, přidá se jako poslední v řadě
                $child['lft'] =  $family['rgt'] + 1;
                $child['rgt'] =  $child['lft'] + 1;
            
                $this->updateNodes($parent['rgt'], $child['lft']);
            }else{   //pokud nemá, odvíjí se hodnoty od rodiče
                $child['lft'] =  $parent['lft'] + 1;
                $child['rgt'] =  $child['lft'] + 1;
            
                $this->updateNodes($parent['rgt']);
            }
        }
        if($child['lvl'] != 1 && $child['lvl'] != 2){
              Debugger::barDump($child['lvl'],'childlvl');
            $color = $this->generateColor($parent);
        }
        //do uvolněného místa se vloží nový uzel
         $this->insertNode($child, $color);
    }
    
    private function generateColor($parent){
        
        list($r,$g,$b) = sscanf($parent['color'], "#%02x%02x%02x");
       
        $color = $this->rgb2hsl($r,$g,$b);
        
        $minSaturation = 90 - $parent['lvl'] * 2.5;
        $minLightness = 55 + $parent['lvl'] * 2.5;
        $maxSaturation = $minSaturation + 7;
        $maxLightness =  $minLightness + 7;
        
        $saturation = rand($minSaturation,  $maxSaturation);
        if($saturation < 10){
            $saturation = 15;
        }
        $lightness = rand($minLightness, $maxLightness);
        if($lightness > 95){
            $lightness = 95;
        }
        $rgbColor = $this->hsl2rgb($color['h'], $saturation, $lightness);
        
        $hexColor = $this->rgb2hex($rgbColor);
        return $hexColor;
    }
    
private function rgb2hsl ($r, $g, $b) {
    $r /= 255;
    $g /= 255;
    $b /= 255;
    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $l = ($max + $min) / 2;
    if ($max == $min) {
        $h = $s = 0;
    } else {
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        switch ($max) {
            case $r:
                $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                break;
            case $g:
                $h = ($b - $r) / $d + 2;
                break;
            case $b:
                $h = ($r - $g) / $d + 4;
                break;
        }
        $h /= 6;
    }
    $h = floor($h * 360);
    $s = floor($s * 100);
    $l = floor($l * 100);
    return ['h' => $h, 's' => $s, 'l' => $l];
}

private function rgb2hex($rgb) {
   $hex = "#";
   $hex .= str_pad(dechex($rgb['r']), 2, "0", STR_PAD_LEFT);
   $hex .= str_pad(dechex($rgb['g']), 2, "0", STR_PAD_LEFT);
   $hex .= str_pad(dechex($rgb['b']), 2, "0", STR_PAD_LEFT);

   return $hex; // returns the hex value including the number sign (#)
}

private function hsl2rgb ($h, $s, $l) {

    $h /= 60;
    if ($h < 0) $h = 6 - fmod(-$h, 6);
    $h = fmod($h, 6);

    $s = max(0, min(1, $s / 100));
    $l = max(0, min(1, $l / 100));

    $c = (1 - abs((2 * $l) - 1)) * $s;
    $x = $c * (1 - abs(fmod($h, 2) - 1));

    if ($h < 1) {
        $r = $c;
        $g = $x;
        $b = 0;
    } elseif ($h < 2) {
        $r = $x;
        $g = $c;
        $b = 0;
    } elseif ($h < 3) {
        $r = 0;
        $g = $c;
        $b = $x;
    } elseif ($h < 4) {
        $r = 0;
        $g = $x;
        $b = $c;
    } elseif ($h < 5) {
        $r = $x;
        $g = 0;
        $b = $c;
    } else {
        $r = $c;
        $g = 0;
        $b = $x;
    }

    $m = $l - $c / 2;
    $r = round(($r + $m) * 255);
    $g = round(($g + $m) * 255);
    $b = round(($b + $m) * 255);

    return ['r' => $r, 'g' => $g, 'b' => $b];

}

    /**
     * Odebere ze stromu uzel na základě id a dle hierarchie upraví ostatní uzly
     * Pokud měl potomky, tak se přesunou na jeho místo
     * @param $id
     */
    public function removeNode($id) : void{
        
        $node = $this->getNodeById($id);
        //uloží potomky uzlu
        $children = $this->getTreeBySides($node['lft'], $node['rgt']);
        $this->deleteNode($node, $children);
    }
    
    private function updateNodes($rgt, $lft = null){
        $this->database->query('UPDATE `tree` SET `lft` = `lft` + 2 WHERE `lft` > ?', $rgt);
        
        if ($lft) {
            $this->database->query('UPDATE `tree` SET `rgt` = `rgt` + 2 WHERE `rgt` >= ?', $lft);
        } else {
            $this->database->query('UPDATE `tree` SET `rgt` = `rgt` + 2 WHERE `rgt` >= ?', $rgt);
        }
    }
    
    private function deleteNode($node, $children = false){
        $this->database->query('DELETE FROM `tree` WHERE `id` = ?', $node['id']);
        
        if($children){
            $this->database->query('UPDATE `tree` SET `lft` = `lft` - 1, `rgt` = `rgt` -1, `lvl` = `lvl` - 1 WHERE `lft` BETWEEN ? AND ?', $node['lft'], $node['rgt']);  
        }
        $this->database->query('UPDATE `tree` SET `pid` = ? WHERE `pid` = ?', $node['pid'],$node['id']);
        
        $this->database->query('UPDATE `tree` SET `lft` = `lft` - 2 WHERE `lft` > ?', $node['rgt']);
        $this->database->query('UPDATE `tree` SET `rgt` = `rgt` - 2 WHERE `rgt` > ?', $node['rgt']);
    }
    
    private function insertNode($node, $color){
        $this->database->query('INSERT `tree` SET `id` = ?, `pid` = ?, `lvl` = ?, `lft` = ?, `rgt` = ?, `color` = ?', $node['id'],$node['pid'],$node['lvl'],$node['lft'],$node['rgt'], $color);
    }
    
    private function getTreeBySides($lft, $rgt){
        return $this->database->fetchAll('SELECT * FROM `tree` WHERE `lft` BETWEEN ? AND ? ORDER BY `lvl`, `lft`', $lft, $rgt);
    }
    
    private function getNodesById($id){
        return $this->database->fetchAll('SELECT * FROM `tree` WHERE `id`= ?', $id);
    }
    
    private function getNodeById($id){
        return $this->database->fetch('SELECT * FROM `tree` WHERE `id`= ?', $id);
    }
    
    private function getParent($pid = 0){
       return $this->database->fetch('SELECT * FROM `tree` WHERE `pid`= ?', $pid);
    }
   
    private function lastId(){
        return $this->database->fetch('SELECT * FROM `tree` ORDER BY `id` DESC LIMIT 1');
    }
    private function getFamilyOldest($lvl, $pid){
        return $this->database->fetch('SELECT * FROM `tree` WHERE `lvl`= ? AND `pid`= ? ORDER BY `rgt` DESC LIMIT 1', $lvl, $pid);
    }
        
}

