<?php

namespace ACP3\Modules\Polls\Controller\Admin;

use ACP3\Core;
use ACP3\Modules\Polls;

/**
 * Description of PollsAdmin
 *
 * @author Tino Goratsch
 */
class Index extends Core\Modules\Controller\Admin
{

    /**
     *
     * @var Polls\Model
     */
    protected $model;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->model = new Polls\Model($this->db);
    }

    public function actionCreate()
    {
        if (isset($_POST['submit']) === true) {
            try {
                $validator = new Polls\Validator($this->lang);
                $validator->validateCreate($_POST);

                $insertValues = array(
                    'id' => '',
                    'start' => $this->date->toSQL($_POST['start']),
                    'end' => $this->date->toSQL($_POST['end']),
                    'title' => Core\Functions::strEncode($_POST['title']),
                    'multiple' => isset($_POST['multiple']) ? '1' : '0',
                    'user_id' => $this->auth->getUserId(),
                );

                $pollId = $this->model->insert($insertValues);
                $bool2 = false;

                if ($pollId !== false) {
                    foreach ($_POST['answers'] as $row) {
                        if (!empty($row)) {
                            $insertAnswer = array(
                                'id' => '',
                                'text' => Core\Functions::strEncode($row),
                                'poll_id' => $pollId,
                            );
                            $bool2 = $this->model->insert($insertAnswer, Polls\Model::TABLE_NAME_ANSWERS);
                        }
                    }
                }

                $this->session->unsetFormToken();

                $redirect = new Core\Helpers\RedirectMessages($this->uri, $this->view);
                $redirect->setMessage($pollId && $bool2, $this->lang->t('system', $pollId !== false && $bool2 !== false ? 'create_success' : 'create_error'), 'acp/polls');
            } catch (Core\Exceptions\InvalidFormToken $e) {
                $redirect = new Core\Helpers\RedirectMessages($this->uri, $this->view);
                $redirect->setMessage(false, $e->getMessage(), 'acp/polls');
            } catch (Core\Exceptions\ValidationFailed $e) {
                $alerts = new Core\Helpers\Alerts($this->uri, $this->view);
                $this->view->assign('error_msg', $alerts->errorBox($e->getMessage()));
            }
        }

        $answers = array();
        if (isset($_POST['answers'])) {
            // Bisherige Antworten
            $i = 0;
            foreach ($_POST['answers'] as $row) {
                $answers[$i]['number'] = $i;
                $answers[$i]['value'] = $row;
                ++$i;
            }
            // Neue Antwort nur hinzufügen, wenn die vorangegangene nicht leer ist
            if (!empty($_POST['answers'][$i - 1])) {
                $answers[$i]['number'] = $i;
                $answers[$i]['value'] = '';
            }
        } else {
            $answers[0]['number'] = 0;
            $answers[0]['value'] = '';
            $answers[1]['number'] = 1;
            $answers[1]['value'] = '';
        }

        // Übergabe der Daten an Smarty
        $this->view->assign('publication_period', $this->date->datepicker(array('start', 'end')));
        $this->view->assign('title', isset($_POST['title']) ? $_POST['title'] : '');
        $this->view->assign('answers', $answers);
        $this->view->assign('multiple', Core\Functions::selectEntry('multiple', '1', '0', 'checked'));

        $this->session->generateFormToken();
    }

    public function actionDelete()
    {
        $items = $this->_deleteItem('acp/polls/index/delete', 'acp/polls');

        if ($this->uri->action === 'confirmed') {
            $bool = $bool2 = $bool3 = false;
            foreach ($items as $item) {
                $bool = $this->model->delete($item);
                $bool2 = $this->model->delete($item, 'poll_id', Polls\Model::TABLE_NAME_ANSWERS);
                $bool3 = $this->model->delete($item, 'poll_id', Polls\Model::TABLE_NAME_VOTES);
            }
            $redirect = new Core\Helpers\RedirectMessages($this->uri, $this->view);
            $redirect->setMessage($bool !== false && $bool2 !== false && $bool3 !== false, $this->lang->t('system', $bool !== false && $bool2 !== false && $bool3 !== false ? 'delete_success' : 'delete_error'), 'acp/polls');
        } elseif (is_string($items)) {
            throw new Core\Exceptions\ResultNotExists();
        }
    }

    public function actionEdit()
    {
        $poll = $this->model->getOneById($this->uri->id);

        if (empty($poll) === false) {
            if (isset($_POST['submit']) === true) {
                try {
                    $validator = new Polls\Validator($this->lang);
                    $validator->validateEdit($_POST);

                    // Frage aktualisieren
                    $updateValues = array(
                        'start' => $this->date->toSQL($_POST['start']),
                        'end' => $this->date->toSQL($_POST['end']),
                        'title' => Core\Functions::strEncode($_POST['title']),
                        'multiple' => isset($_POST['multiple']) ? '1' : '0',
                        'user_id' => $this->auth->getUserId(),
                    );

                    $bool = $this->model->update($updateValues, $this->uri->id);

                    // Stimmen zurücksetzen
                    if (!empty($_POST['reset'])) {
                        $this->model->delete($this->uri->id, 'poll_id', Polls\Model::TABLE_NAME_VOTES);
                    }

                    // Antworten
                    foreach ($_POST['answers'] as $row) {
                        // Neue Antwort hinzufügen
                        if (empty($row['id'])) {
                            // Neue Antwort nur hinzufügen, wenn die Löschen-Checkbox nicht gesetzt wurde
                            if (!empty($row['value']) && !isset($row['delete']))
                                $this->model->insert(array('text' => Core\Functions::strEncode($row['value']), 'poll_id' => $this->uri->id), Polls\Model::TABLE_NAME_ANSWERS);
                            // Antwort mitsamt Stimmen löschen
                        } elseif (isset($row['delete']) && Core\Validate::isNumber($row['id'])) {
                            $this->model->delete($row['id'], '', Polls\Model::TABLE_NAME_ANSWERS);
                            $this->model->delete($row['id'], 'answer_id', Polls\Model::TABLE_NAME_VOTES);
                            // Antwort aktualisieren
                        } elseif (!empty($row['value']) && Core\Validate::isNumber($row['id'])) {
                            $bool = $this->model->update(array('text' => Core\Functions::strEncode($row['value'])), $row['id'], Polls\Model::TABLE_NAME_ANSWERS);
                        }
                    }

                    $this->session->unsetFormToken();

                    $redirect = new Core\Helpers\RedirectMessages($this->uri, $this->view);
                    $redirect->setMessage($bool, $this->lang->t('system', $bool !== false ? 'edit_success' : 'edit_error'), 'acp/polls');
                } catch (Core\Exceptions\InvalidFormToken $e) {
                    $redirect = new Core\Helpers\RedirectMessages($this->uri, $this->view);
                    $redirect->setMessage(false, $e->getMessage(), 'acp/polls');
                } catch (Core\Exceptions\ValidationFailed $e) {
                    $alerts = new Core\Helpers\Alerts($this->uri, $this->view);
                    $this->view->assign('error_msg', $alerts->errorBox($e->getMessage()));
                }
            }

            $answers = array();
            // Neue Antworten hinzufügen
            if (isset($_POST['answers'])) {
                // Bisherige Antworten
                $i = 0;
                foreach ($_POST['answers'] as $row) {
                    $answers[$i]['number'] = $i;
                    $answers[$i]['id'] = $row['id'];
                    $answers[$i]['value'] = $row['value'];
                    ++$i;
                }
                // Neue Antwort nur hinzufügen, wenn die vorangegangene nicht leer ist
                if (!empty($_POST['answers'][$i - 1]['value'])) {
                    $answers[$i]['number'] = $i;
                    $answers[$i]['id'] = '0';
                    $answers[$i]['value'] = '';
                }
            } else {
                $answers = $this->model->getAnswersByPollId($this->uri->id);
                $c_answers = count($answers);

                for ($i = 0; $i < $c_answers; ++$i) {
                    $answers[$i]['number'] = $i;
                    $answers[$i]['value'] = $answers[$i]['text'];
                }
            }
            $this->view->assign('answers', $answers);

            $options = array();
            $options[0]['name'] = 'reset';
            $options[0]['checked'] = Core\Functions::selectEntry('reset', '1', '0', 'checked');
            $options[0]['lang'] = $this->lang->t('polls', 'reset_votes');
            $options[1]['name'] = 'multiple';
            $options[1]['checked'] = Core\Functions::selectEntry('multiple', '1', $poll['multiple'], 'checked');
            $options[1]['lang'] = $this->lang->t('polls', 'multiple_choice');
            $this->view->assign('options', $options);

            // Übergabe der Daten an Smarty
            $this->view->assign('publication_period', $this->date->datepicker(array('start', 'end'), array($poll['start'], $poll['end'])));
            $this->view->assign('title', isset($_POST['title']) ? $_POST['title'] : $poll['title']);

            $this->session->generateFormToken();
        } else {
            throw new Core\Exceptions\ResultNotExists();
        }
    }

    public function actionIndex()
    {
        $redirect = new Core\Helpers\RedirectMessages($this->uri, $this->view);
        $redirect->getMessage();

        $polls = $this->model->getAllInAcp();
        $c_polls = count($polls);

        if ($c_polls > 0) {
            $canDelete = Core\Modules::hasPermission('admin/polls/index/delete');
            $config = array(
                'element' => '#acp-table',
                'sort_col' => $canDelete === true ? 1 : 0,
                'sort_dir' => 'desc',
                'hide_col_sort' => $canDelete === true ? 0 : ''
            );
            $this->appendContent(Core\Functions::dataTable($config));

            for ($i = 0; $i < $c_polls; ++$i) {
                $polls[$i]['period'] = $this->date->formatTimeRange($polls[$i]['start'], $polls[$i]['end']);
            }
            $this->view->assign('polls', $polls);
            $this->view->assign('can_delete', $canDelete);
        }
    }

}