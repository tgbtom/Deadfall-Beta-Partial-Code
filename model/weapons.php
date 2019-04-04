<?php

Class Weapon{
    //itemID (Primary Key), Ammo ID (-1 means none), AP Cost, Min. Kills, Max. Kills, chance to break (out of 100), chance to get injured (out of 100), chance of ammo output, ammo output ID (-1) is for none
    private $w_name, $w_id, $ammoId, $apCost, $minKills, $maxKills, $breakChance, $injuryChance, $outputChance, $outputId;

    public function __construct($w_name, $w_id, $ammoId, $apCost, $minKills, $maxKills, $breakChance, $injuryChance, $outputChance, $outputId){
        $this->w_name = $w_name;
        $this->w_id = $w_id;
        $this->ammoId = $ammoId;
        $this->apCost = $apCost;
        $this->minKills = $minKills;
        $this->maxKills = $maxKills;
        $this->breakChance = $breakChance;
        $this->injuryChance = $injuryChance;
        $this->outputChance = $outputChance;
        $this->outputId = $outputId;
    }

    public function getKillCapacity(){
        return [$this->minKills, $this->maxKills];
    }

    public function rollForOutput(){
        //Return either the ID of the item to get, or false
        return (mt_rand(0,100) <= $this->outputChance) ? $this->outputId : false;
    }
}