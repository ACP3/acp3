<?php
namespace ACP3\Modules\ACP3\Newsletter\Helper;

use ACP3\Core\Date;
use ACP3\Modules\ACP3\Newsletter\Model\Repository\NewsletterAccountHistoryRepository;
use ACP3\Modules\ACP3\Newsletter\Model\Repository\NewsletterAccountsRepository;

/**
 * Class AccountStatus
 * @package ACP3\Modules\ACP3\Newsletter\Helper
 */
class AccountStatus
{
    const ACCOUNT_STATUS_CONFIRMATION_NEEDED = 0;
    const ACCOUNT_STATUS_CONFIRMED = 1;
    const ACCOUNT_STATUS_DISABLED = 2;

    /**
     * @var \ACP3\Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Modules\ACP3\Newsletter\Model\Repository\NewsletterAccountsRepository
     */
    protected $accountRepository;
    /**
     * @var \ACP3\Modules\ACP3\Newsletter\Model\Repository\NewsletterAccountHistoryRepository
     */
    protected $accountHistoryRepository;

    /**
     * @param \ACP3\Core\Date $date
     * @param \ACP3\Modules\ACP3\Newsletter\Model\Repository\NewsletterAccountsRepository $accountRepository
     * @param \ACP3\Modules\ACP3\Newsletter\Model\Repository\NewsletterAccountHistoryRepository $accountHistoryRepository
     */
    public function __construct(
        Date $date,
        NewsletterAccountsRepository $accountRepository,
        NewsletterAccountHistoryRepository $accountHistoryRepository
    ) {
        $this->date = $date;
        $this->accountRepository = $accountRepository;
        $this->accountHistoryRepository = $accountHistoryRepository;
    }

    /**
     * @param int $status
     * @param int|array $entryId
     *
     * @return bool|int
     */
    public function changeAccountStatus($status, $entryId)
    {
        $result = $this->accountRepository->update(['status' => $status], $entryId);

        if (is_array($entryId)) {
            $accountId = $this->retrieveAccountId($entryId);

            $this->addAccountHistory($status, $accountId);
        } else {
            $this->addAccountHistory($status, $entryId);
        }

        return $result;
    }

    /**
     * @param int $status
     * @param int $accountId
     *
     * @return bool|int
     */
    protected function addAccountHistory($status, $accountId)
    {
        $historyInsertValues = [
            'newsletter_account_id' => $accountId,
            'date' => $this->date->toSQL(),
            'action' => $status
        ];
        return $this->accountHistoryRepository->insert($historyInsertValues);
    }

    /**
     * @param array $entry
     *
     * @return int
     */
    protected function retrieveAccountId(array $entry)
    {
        switch (key($entry)) {
            case 'mail':
                $account = $this->accountRepository->getOneByEmail($entry['mail']);
                break;
            case 'hash':
                $account = $this->accountRepository->getOneByHash($entry['hash']);
        }

        return (!empty($account)) ? $account['id'] : 0;
    }
}
