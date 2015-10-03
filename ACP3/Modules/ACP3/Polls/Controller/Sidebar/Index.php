<?php

namespace ACP3\Modules\ACP3\Polls\Controller\Sidebar;

use ACP3\Core;
use ACP3\Modules\ACP3\Polls;

/**
 * Class Index
 * @package ACP3\Modules\ACP3\Polls\Controller\Sidebar
 */
class Index extends Core\Modules\Controller
{
    /**
     * @var Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Modules\ACP3\Polls\Model\PollRepository
     */
    protected $pollRepository;
    /**
     * @var \ACP3\Modules\ACP3\Polls\Model\AnswerRepository
     */
    protected $answerRepository;
    /**
     * @var \ACP3\Modules\ACP3\Polls\Model\VoteRepository
     */
    protected $voteRepository;

    /**
     * @param \ACP3\Core\Modules\Controller\Context           $context
     * @param Core\Date                                       $date
     * @param \ACP3\Modules\ACP3\Polls\Model\PollRepository   $pollRepository
     * @param \ACP3\Modules\ACP3\Polls\Model\AnswerRepository $answerRepository
     * @param \ACP3\Modules\ACP3\Polls\Model\VoteRepository   $voteRepository
     */
    public function __construct(
        Core\Modules\Controller\Context $context,
        Core\Date $date,
        Polls\Model\PollRepository $pollRepository,
        Polls\Model\AnswerRepository $answerRepository,
        Polls\Model\VoteRepository $voteRepository)
    {
        parent::__construct($context);

        $this->date = $date;
        $this->pollRepository = $pollRepository;
        $this->answerRepository = $answerRepository;
        $this->voteRepository = $voteRepository;
    }

    public function actionIndex()
    {
        $poll = $this->pollRepository->getLatestPoll($this->date->getCurrentDateTime());

        if (!empty($poll)) {
            $answers = $this->answerRepository->getAnswersWithVotesByPollId($poll['id']);

            $this->view->assign('sidebar_polls', $poll);

            if ($this->user->isAuthenticated() === true) {
                $query = $this->voteRepository->getVotesByUserId($poll['id'], $this->user->getUserId(), $this->request->getServer()->get('REMOTE_ADDR', '')); // Check, whether the logged user has already voted
            } else {
                $query = $this->voteRepository->getVotesByIpAddress($poll['id'], $this->request->getServer()->get('REMOTE_ADDR', '')); // For guest users check against the ip address
            }

            if ($query > 0) {
                $totalVotes = $poll['total_votes'];

                $c_answers = count($answers);
                for ($i = 0; $i < $c_answers; ++$i) {
                    $votes = $answers[$i]['votes'];
                    $answers[$i]['votes'] = ($votes > 1) ? sprintf($this->lang->t('polls', 'number_of_votes'), $votes) : $this->lang->t('polls', ($votes == 1 ? 'one_vote' : 'no_votes'));
                    $answers[$i]['percent'] = $totalVotes > 0 ? round(100 * $votes / $totalVotes, 2) : '0';
                }

                $this->view->assign('sidebar_poll_answers', $answers);
                $this->setTemplate('Polls/Sidebar/index.result.tpl');
                return;
            } else {
                $this->view->assign('sidebar_poll_answers', $answers);
            }
        }

        $this->setTemplate('Polls/Sidebar/index.vote.tpl');
    }
}
