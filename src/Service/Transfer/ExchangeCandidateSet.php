<?php

namespace App\Service\Transfer;

use App\Entity\Loan;
use App\Service\Loan\LoanDto;

/**
 * ExchangeCandidateSet
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class ExchangeCandidateSet
{
    /**
     * @var LoanDto[]
     */
    private $fittingCandidatesDtoVersion;

    /**
     * @var LoanDto[]
     */
    private $nonFittingCandidatesDtoVersion;

    /**
     * @var Loan[]
     */
    private $fittingCandidates;

    /**
     * @var Loan[]
     */
    private $nonFittingCandidates;

    /**
     * @return LoanDto[]
     */
    public function getFittingCandidatesDtoVersion(): array
    {
        return $this->fittingCandidatesDtoVersion;
    }

    /**
     * @param LoanDto[] $fittingCandidatesDtoVersion
     */
    public function setFittingCandidatesDtoVersion(array $fittingCandidatesDtoVersion): void
    {
        $this->fittingCandidatesDtoVersion = $fittingCandidatesDtoVersion;
    }

    /**
     * @return LoanDto[]
     */
    public function getNonFittingCandidatesDtoVersion(): array
    {
        return $this->nonFittingCandidatesDtoVersion;
    }

    /**
     * @param LoanDto[] $nonFittingCandidatesDtoVersion
     */
    public function setNonFittingCandidatesDtoVersion(array $nonFittingCandidatesDtoVersion): void
    {
        $this->nonFittingCandidatesDtoVersion = $nonFittingCandidatesDtoVersion;
    }

    /**
     * @return Loan[]
     */
    public function getFittingCandidates(): array
    {
        return $this->fittingCandidates;
    }

    /**
     * @param Loan[] $fittingCandidates
     */
    public function setFittingCandidates(array $fittingCandidates): void
    {
        $this->fittingCandidates = $fittingCandidates;
    }

    /**
     * @return Loan[]
     */
    public function getNonFittingCandidates(): array
    {
        return $this->nonFittingCandidates;
    }

    /**
     * @param Loan[] $nonFittingCandidates
     */
    public function setNonFittingCandidates(array $nonFittingCandidates): void
    {
        $this->nonFittingCandidates = $nonFittingCandidates;
    }

    public function getAllCandidates(){
        return $this->getFittingCandidates();
    }

    public function getAllCandidatesDto(){
        return $this->getFittingCandidatesDtoVersion();
    }
}
