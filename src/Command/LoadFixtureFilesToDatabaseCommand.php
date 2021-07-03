<?php

namespace App\Command;

use App\Service\Legacy\LegacyImportService;
use App\Service\User\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * LoadFixtureFilesToDatabase
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class LoadFixtureFilesToDatabaseCommand extends Command
{
    const NAME = 'debes:test:filldatabase';

    /**
     * @var string
     */
    protected static $defaultName = self::NAME;

    /**
     * @var LegacyImportService
     */
    private $legacyImportService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * LoadFixtureFilesToDatabase constructor.
     */
    public function __construct(
        LegacyImportService $legacyImportService,
        UserService $userService
    ) {
        parent::__construct();

        $this->legacyImportService = $legacyImportService;
        $this->userService = $userService;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDescription('Fill Database With Data');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->getStandardUserSet() as $standardUserdata) {
            $this->legacyImportService->createUserByData(
                $standardUserdata[0],
                $standardUserdata[1],
                $standardUserdata[2],
                $standardUserdata[3],
                $standardUserdata[4]
            );
        }

        foreach ($this->getStandardBankAccounts() as $standardTransactionData) {
            $this->legacyImportService->creatBankAccountByData(
                $standardTransactionData[0],
                $standardTransactionData[1],
                $standardTransactionData[2],
                $standardTransactionData[3],
                $standardTransactionData[4],
                $standardTransactionData[5],
                $standardTransactionData[6],
                $standardTransactionData[7]
            );
        }

        foreach ($this->getStandardTransactionSet() as $standardTransactionData) {
            $this->legacyImportService->createTransaction(
                $standardTransactionData[0],
                $standardTransactionData[1],
                $standardTransactionData[2],
                $standardTransactionData[3]
            );
        }
        return 0;
    }

    /**
     * getStandardUserSet
     *
     * @return array
     */
    private function getStandardUserSet(): array
    {
        return [
            ['eva@testmail.com', '123Katzen', 'Godman', 'Eva', 'Eva'],
            ['adam@testmail.com', '123Katzen', 'Godman', 'Adam', 'Adam'],
            ['kain@testmail.com', '123Katzen', 'Godman', 'Kain', 'Kain'],
            ['abel@testmail.com', '123Katzen', 'Godman', 'Abel', 'Abel'],
        ];
    }

    /**
     * getStandardBankAccounts
     *
     * @return array
     */
    private function getStandardBankAccounts(): array
    {
        return [
            [
                true,
                'KSK',
                'COKS99',
                'DE5427507047052',
                'Bankkonto von Evas Mann',
                true,
                $this->userService->findUserByUserName('Eva'),
                'Adam Godman',
            ],
            [
                true,
                'KSK',
                'COKS99',
                'DE927498274ds9',
                'Bankkonto von Eva',
                false,
                $this->userService->findUserByUserName('Eva'),
                'Eva Devilman',
            ],
            [
                true,
                'KSK',
                'COKS99',
                'DE5427507047052',
                'Bankkonto von Adam',
                true,
                $this->userService->findUserByUserName('Adam'),
                'Adam Godman',
            ],
            [
                true,
                'Postbank',
                'PKS887',
                'DE53759598729932',
                'Bankkonto von Kain',
                true,
                $this->userService->findUserByUserName('Kain'),
                'Kain Godman',
            ],
            [
                true,
                'Commerzbank',
                'CM327393',
                'DE3789273410133',
                'Bankkonto von Abel',
                true,
                $this->userService->findUserByUserName('Abel'),
                'Abel Godman',
            ],
        ];
    }

    /**
     * getStandardTransactionSet
     *
     * @return array
     */
    private function getStandardTransactionSet(): array
    {
        return [
            [
                'Apfel',
                99.98,
                $this->userService->findUserByUserName('Kain'),
                $this->userService->findUserByUserName('Abel'),
            ],
            [
                'Fuchsbandwurm',
                14.98,
                $this->userService->findUserByUserName('Adam'),
                $this->userService->findUserByUserName('Abel'),
            ],
            [
                'Eis',
                110.98,
                $this->userService->findUserByUserName('Eva'),
                $this->userService->findUserByUserName('Abel'),
            ],
            [
                'Nutella',
                199.98,
                $this->userService->findUserByUserName('Eva'),
                $this->userService->findUserByUserName('Adam'),
            ],
        ];
    }
}
