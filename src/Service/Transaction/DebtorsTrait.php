<?php

namespace App\Service\Transaction;

use App\Service\Debt\DebtCreateData;

trait DebtorsTrait
{
    /**
     * @var DebtCreateData
     */
    private $debtor1;

    /**
     * @var DebtCreateData
     */
    private $debtor2;

    /**
     * @var DebtCreateData
     */
    private $debtor3;

    /**
     * @var DebtCreateData
     */
    private $debtor4;

    /**
     * @var DebtCreateData
     */
    private $debtor5;

    /**
     * @var DebtCreateData
     */
    private $debtor6;

    /**
     * @var DebtCreateData
     */
    private $debtor7;

    /**
     * @var DebtCreateData
     */
    private $debtor8;

    /**
     * @var DebtCreateData
     */
    private $debtor9;

    /**
     * @var DebtCreateData
     */
    private $debtor10;

    /**
     * @var DebtCreateData
     */
    private $debtor11;

    /**
     * @var DebtCreateData
     */
    private $debtor12;

    /**
     * @var DebtCreateData
     */
    private $debtor13;

    /**
     * @var DebtCreateData
     */
    private $debtor14;

    /**
     * @var DebtCreateData
     */
    private $debtor15;

    /**
     * @var DebtCreateData
     */
    private $debtor16;

    /**
     * @var DebtCreateData
     */
    private $debtor17;

    /**
     * @var DebtCreateData
     */
    private $debtor18;

    /**
     * @var DebtCreateData
     */
    private $debtor19;

    /**
     * @var DebtCreateData
     */
    private $debtor20;

    /**
     * @var DebtCreateData[]
     */
    private $debtorData;

    /**
     * DebtorsTrait constructor.
     */
    public function __construct()
    {
        $this->debtorData = array();
        foreach (range(1, 20) as $debtorNr) {
            $method = sprintf("setDebtor%d", $debtorNr);
            $newDebtorData = new DebtCreateData();
            $this->$method($newDebtorData);
            $this->debtorData[] = $newDebtorData;
        }
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor1(): DebtCreateData
    {
        return $this->debtor1;
    }

    /**
     * @param DebtCreateData $debtor1
     */
    public function setDebtor1(DebtCreateData $debtor1): void
    {
        $this->debtor1 = $debtor1;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor2(): DebtCreateData
    {
        return $this->debtor2;
    }

    /**
     * @param DebtCreateData $debtor2
     */
    public function setDebtor2(DebtCreateData $debtor2): void
    {
        $this->debtor2 = $debtor2;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor3(): DebtCreateData
    {
        return $this->debtor3;
    }

    /**
     * @param DebtCreateData $debtor3
     */
    public function setDebtor3(DebtCreateData $debtor3): void
    {
        $this->debtor3 = $debtor3;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor4(): DebtCreateData
    {
        return $this->debtor4;
    }

    /**
     * @param DebtCreateData $debtor4
     */
    public function setDebtor4(DebtCreateData $debtor4): void
    {
        $this->debtor4 = $debtor4;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor5(): DebtCreateData
    {
        return $this->debtor5;
    }

    /**
     * @param DebtCreateData $debtor5
     */
    public function setDebtor5(DebtCreateData $debtor5): void
    {
        $this->debtor5 = $debtor5;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor6(): DebtCreateData
    {
        return $this->debtor6;
    }

    /**
     * @param DebtCreateData $debtor6
     */
    public function setDebtor6(DebtCreateData $debtor6): void
    {
        $this->debtor6 = $debtor6;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor7(): DebtCreateData
    {
        return $this->debtor7;
    }

    /**
     * @param DebtCreateData $debtor7
     */
    public function setDebtor7(DebtCreateData $debtor7): void
    {
        $this->debtor7 = $debtor7;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor8(): DebtCreateData
    {
        return $this->debtor8;
    }

    /**
     * @param DebtCreateData $debtor8
     */
    public function setDebtor8(DebtCreateData $debtor8): void
    {
        $this->debtor8 = $debtor8;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor9(): DebtCreateData
    {
        return $this->debtor9;
    }

    /**
     * @param DebtCreateData $debtor9
     */
    public function setDebtor9(DebtCreateData $debtor9): void
    {
        $this->debtor9 = $debtor9;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor10(): DebtCreateData
    {
        return $this->debtor10;
    }

    /**
     * @param DebtCreateData $debtor10
     */
    public function setDebtor10(DebtCreateData $debtor10): void
    {
        $this->debtor10 = $debtor10;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor11(): DebtCreateData
    {
        return $this->debtor11;
    }

    /**
     * @param DebtCreateData $debtor11
     */
    public function setDebtor11(DebtCreateData $debtor11): void
    {
        $this->debtor11 = $debtor11;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor12(): DebtCreateData
    {
        return $this->debtor12;
    }

    /**
     * @param DebtCreateData $debtor12
     */
    public function setDebtor12(DebtCreateData $debtor12): void
    {
        $this->debtor12 = $debtor12;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor13(): DebtCreateData
    {
        return $this->debtor13;
    }

    /**
     * @param DebtCreateData $debtor13
     */
    public function setDebtor13(DebtCreateData $debtor13): void
    {
        $this->debtor13 = $debtor13;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor14(): DebtCreateData
    {
        return $this->debtor14;
    }

    /**
     * @param DebtCreateData $debtor14
     */
    public function setDebtor14(DebtCreateData $debtor14): void
    {
        $this->debtor14 = $debtor14;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor15(): DebtCreateData
    {
        return $this->debtor15;
    }

    /**
     * @param DebtCreateData $debtor15
     */
    public function setDebtor15(DebtCreateData $debtor15): void
    {
        $this->debtor15 = $debtor15;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor16(): DebtCreateData
    {
        return $this->debtor16;
    }

    /**
     * @param DebtCreateData $debtor16
     */
    public function setDebtor16(DebtCreateData $debtor16): void
    {
        $this->debtor16 = $debtor16;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor17(): DebtCreateData
    {
        return $this->debtor17;
    }

    /**
     * @param DebtCreateData $debtor17
     */
    public function setDebtor17(DebtCreateData $debtor17): void
    {
        $this->debtor17 = $debtor17;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor18(): DebtCreateData
    {
        return $this->debtor18;
    }

    /**
     * @param DebtCreateData $debtor18
     */
    public function setDebtor18(DebtCreateData $debtor18): void
    {
        $this->debtor18 = $debtor18;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor19(): DebtCreateData
    {
        return $this->debtor19;
    }

    /**
     * @param DebtCreateData $debtor19
     */
    public function setDebtor19(DebtCreateData $debtor19): void
    {
        $this->debtor19 = $debtor19;
    }

    /**
     * @return DebtCreateData
     */
    public function getDebtor20(): DebtCreateData
    {
        return $this->debtor20;
    }

    /**
     * @param DebtCreateData $debtor20
     */
    public function setDebtor20(DebtCreateData $debtor20): void
    {
        $this->debtor20 = $debtor20;
    }

    /**
     * @return DebtCreateData[]
     */
    public function getDebtorData(): array
    {
        return $this->debtorData;
    }

    /**
     * @param DebtCreateData[] $debtorData
     */
    public function setDebtorData(array $debtorData): void
    {
        $this->debtorData = $debtorData;
    }


}
