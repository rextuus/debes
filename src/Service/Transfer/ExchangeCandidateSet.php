<?php

namespace App\Service\Transfer;

/**
 * ExchangeCandidateSet
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class ExchangeCandidateSet
{
    /**
     * @var array
     */
    private $fittingCandidates;

    /**
     * @var array
     */
    private $nonFittingCandidates;

    /**
     * @return array
     */
    public function getFittingCandidates(): array
    {
        return $this->fittingCandidates;
    }

    /**
     * @param array $fittingCandidates
     */
    public function setFittingCandidates(array $fittingCandidates): void
    {
        $this->fittingCandidates = $fittingCandidates;
    }

    /**
     * @return array
     */
    public function getNonFittingCandidates(): array
    {
        return $this->nonFittingCandidates;
    }

    /**
     * @param array $nonFittingCandidates
     */
    public function setNonFittingCandidates(array $nonFittingCandidates): void
    {
        $this->nonFittingCandidates = $nonFittingCandidates;
    }
}
