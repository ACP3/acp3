<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Polls\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Polls;
use ACP3\Modules\ACP3\System\Installer\Schema;

class Result extends Core\Controller\AbstractFrontendAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Modules\ACP3\Polls\Model\Repository\PollRepository
     */
    protected $pollRepository;
    /**
     * @var \ACP3\Modules\ACP3\Polls\Model\Repository\AnswerRepository
     */
    protected $answerRepository;

    /**
     * Result constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext   $context
     * @param \ACP3\Core\Date                                 $date
     * @param \ACP3\Modules\ACP3\Polls\Model\Repository\PollRepository   $pollRepository
     * @param \ACP3\Modules\ACP3\Polls\Model\Repository\AnswerRepository $answerRepository
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Date $date,
        Polls\Model\Repository\PollRepository $pollRepository,
        Polls\Model\Repository\AnswerRepository $answerRepository
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->pollRepository = $pollRepository;
        $this->answerRepository = $answerRepository;
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     */
    public function execute($id)
    {
        $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

        if ($this->pollRepository->pollExists($id, $this->date->getCurrentDateTime()) === true) {
            $question = $this->pollRepository->getOneByIdWithTotalVotes($id);
            $answers = $this->answerRepository->getAnswersWithVotesByPollId($id);
            $cAnswers = count($answers);
            $totalVotes = $question['total_votes'];

            for ($i = 0; $i < $cAnswers; ++$i) {
                $answers[$i]['percent'] = $totalVotes > 0 ? round(100 * $answers[$i]['votes'] / $totalVotes, 2) : '0';
            }

            return [
                'question' => $question['title'],
                'answers' => $answers,
                'total_votes' => $totalVotes
            ];
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
