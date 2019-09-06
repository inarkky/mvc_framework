<?php

namespace application\models;

use application\core\Model;
use application\core\View;

class Main extends Model 
{

    protected $army1;
    protected $army2;

	public function initiateBattle()
	{
        $this->checkForEggs($this->request->get("army2"));

        $this->army1 = new Army( $this->request->get("army1") );
        $this->army2 = new Army( $this->request->get("army2") );

        $response = $this->logBattle();

		return $response;
	}

	private function logBattle()
    {
        $iterator = 1;

        $this->log['Initial state'] = [
            'army1' => $this->army1->getState(),
            'army2' => $this->army2->getState(),
        ];

        while(true){
            //army1 napada army2 se brani
            $a1 = $this->army1->getAttackStrength();
            $a2 = $this->army2->getDefenceStrength();

            $outcome = $this->calculateOutcome($a1, $a2);
            $this->resolveOutcome($outcome, 1);

            $this->log["log"]["$iterator. turn 1st phase (Army1 attacks)"] = "$outcome";

            if( $this->isFinished() ) break;

            //army2 napada army1 se brani
            $a2 = $this->army2->getAttackStrength();
            $a1 = $this->army1->getDefenceStrength();

            $outcome = $this->calculateOutcome($a2, $a1);
            $this->resolveOutcome($outcome, 2);

            $this->log["log"]["$iterator. turn 2nd phase (Army2 attacks)"] = "$outcome";

            $iterator++;
            if( $this->isFinished() ) break;

        }

        $this->log["End state"] = [
            "army1" => $this->army1->getState(),
            "army2" => $this->army2->getState(),
        ];

        return $this->log;
    }

    private function calculateOutcome($attack, $defence)
    {
        if($attack > $defence){
            $result = "attack wins";

        } else {
            $result = "defence wins";
        }

        return $result;
    }

    private function resolveOutcome($condition, $phase)
    {
        switch ($phase) {
            case 1:
                if( strcmp($condition, "attack wins") === 0 ){
                    $this->army1->attackSuccess();
                    $this->army2->defenseFail();
                }else{
                    $this->army1->attackFail();
                    $this->army2->defenseSuccess();
                }
                break;
            case 2:
                if( strcmp($condition, "attack wins") === 0 ){
                    $this->army2->attackSuccess();
                    $this->army1->defenseFail();
                }else{
                    $this->army2->attackFail();
                    $this->army1->defenseSuccess();
                }
                break;
        }
    }

    private function isFinished()
    {
        if( $this->army1->getRemainingSoldiers() == 0 ) {
            $this->log["Victor"] = "Army2";

            return true;
        } elseif($this->army2->getRemainingSoldiers() == 0) {
            $this->log["Victor"] = "Army1";

            return true;
        }
    }

    private function checkForEggs($egg)
    {
        ($egg == 300) ? View::easterEgg() : false ;
    }

}