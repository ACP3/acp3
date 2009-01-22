<?php
/**
 * Polls
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (!defined('IN_ACP3'))
	exit;

if (validate::isNumber($uri->id) && $db->select('COUNT(id)', 'poll_question', 'id = \'' . $uri->id . '\' AND start <= \'' . $date->timestamp() . '\'', 0, 0, 0, 1) == 1) {
	breadcrumb::assign($lang->t('polls', 'polls'), uri('polls'));
	breadcrumb::assign($lang->t('polls', 'result'));

	$question = $db->select('question', 'poll_question');
	$answers = $db->select('id, text', 'poll_answers', 'poll_id = \'' . $uri->id . '\'', 'id ASC');
	$c_answers = count($answers);
	$total_votes = $db->select('COUNT(answer_id)', 'poll_votes', 'poll_id = \'' . $uri->id . '\'', 0, 0, 0, 1);

	for ($i = 0; $i < $c_answers; ++$i) {
		$answers[$i]['text'] = $answers[$i]['text'];
		$answers[$i]['votes'] = $db->select('COUNT(answer_id)', 'poll_votes', 'poll_id = \'' . $uri->id . '\' AND answer_id = \'' . $answers[$i]['id'] . '\'', 0, 0, 0, 1);
		$answers[$i]['percent'] = $total_votes > '0' ? round(100 * $answers[$i]['votes'] / $total_votes, 2) : '0';
	}
	$tpl->assign('question', $question[0]['question']);
	$tpl->assign('answers', $answers);
	$tpl->assign('total_votes', $total_votes);

	$content = $tpl->fetch('polls/result.html');
} else {
	redirect('errors/404');
}
?>