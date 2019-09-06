<?php

namespace application\models;

class Army
{

	public $quantity    = 0;
	public $attack      = 0;
	public $defence     = 0;
	public $mobility    = 0;
	public $morale      = 0;
	public $luck        = 0;
	public $experience  = 0;
	
	public function __construct($numberOfSoldiers) 
	{
        $this->luck     = mt_rand(1, 10) / 10;
        $this->morale   = mt_rand(1, 10) / 10;
        $this->mobility = 1;

        $this->setBaseStats($numberOfSoldiers);
	}

	#region setters
    private function setBaseStats($x)
    {
        $this->setAttack($x);
        $this->setDefence($x);
        $this->setQuantity($x);
    }

    private function setQuantity($x)
    {
        $this->quantity += $x;
    }

    private function setAttack($x)
    {
        $this->attack += $x;
    }

    private function setDefence($x)
    {
        $this->defence += $x;
    }

    private function setMobility($x)
    {
        $this->mobility += $x;
    }

    private function setMorale($x)
    {
        $this->morale += $x * 0.1;
    }

    private function setExperience($x)
    {
        $this->experience += $x;
    }
    #endregion

    #region getters
    public function getRemainingSoldiers()
    {
        return $this->quantity;
    }

    public function getAttackStrength()
    {
        if($this->attack > 0)
            return ($this->attack + $this->mobility + $this->experience ) * ($this->luck + $this->morale);
        else
            return 0;
    }

    public function getDefenceStrength()
    {
        if($this->defence > 0)
            return ($this->defence + $this->mobility + $this->experience ) * ($this->luck + $this->morale);
        else
            return 0;
    }

    public function getState()
    {
        $response = [
            "attack" => $this->attack,
            "defence" => $this->defence,
            "mobility" => $this->mobility,
            "morale" => $this->morale,
            "experience" => $this->experience,
            "luck" => $this->luck
        ];

        return $response;
    }
    #endregion

    #region logic
    public function attackSuccess()
    {
        $this->setExperience(1);
        $this->luck = mt_rand(5, 15) / 10;
    }

    public function attackFail()
    {
        $this->setMorale(-1);
        $this->luck = mt_rand(5, 15) / 10;
    }

    public function defenseSuccess()
    {
        $this->setMorale(1);
        $this->luck = mt_rand(5, 15) / 10;
    }

    public function defenseFail()
    {
        $this->setBaseStats(-1);
        $this->setMobility(1);
        $this->luck = mt_rand(5, 15) / 10;
    }
    #endregion

}