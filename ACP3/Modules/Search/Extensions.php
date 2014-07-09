<?php

namespace ACP3\Modules\Search;

use ACP3\Core;

/**
 * Description of Extensions
 *
 * @author Tino Goratsch
 */
class Extensions
{

    /**
     *
     * @var string
     */
    protected $area;

    /**
     * Whther to sort the results ascending/descending
     *
     * @var string
     */
    protected $sort;

    /**
     * The search term
     *
     * @var string
     */
    protected $searchTerm;

    /**
     * DB Connection Handler
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;
    /**
     * @var \ACP3\Core\Lang
     */
    protected $lang;
    /**
     * @var \ACP3\Core\URI
     */
    protected $uri;
    /**
     * @var \ACP3\Core\Helpers\StringFormatter
     */
    protected $stringFormatter;

    /**
     * SQL Prepared Parameters
     *
     * @var array
     */
    protected $params = array();

    public function __construct(\Doctrine\DBAL\Connection $db, Core\Date $date, Core\Lang $lang, Core\URI $uri, Core\Helpers\StringFormatter $stringFormatter)
    {
        $this->db = $db;
        $this->lang = $lang;
        $this->uri = $uri;
        $this->stringFormatter = $stringFormatter;

        $this->params = array(
            'searchterm' => $this->searchTerm,
            'time' => $date->getCurrentDateTime()
        );
    }

    /**
     * @param $area
     * @return $this
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @param $sort
     * @return $this
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @param $searchTerm
     * @return $this
     */
    public function setSearchTerm($searchTerm)
    {
        $this->searchTerm = $searchTerm;

        return $this;
    }

    public function articlesSearch()
    {
        switch ($this->area) {
            case 'title':
                $fields = 'title';
                break;
            case 'content':
                $fields = 'text';
                break;
            default:
                $fields = 'title, text';
        }

        $period = '(start = end AND start <= :time OR start != end AND :time BETWEEN start AND end)';
        $results = $this->db->fetchAll('SELECT id, title, text FROM ' . DB_PRE . 'articles WHERE MATCH (' . $fields . ') AGAINST (:searchterm IN BOOLEAN MODE) AND ' . $period . ' ORDER BY start ' . $this->sort . ', end ' . $this->sort . ', title ' . $this->sort, $this->params);
        $c_results = count($results);
        $searchResults = array();

        if ($c_results > 0) {
            $name = $this->lang->t('articles', 'articles');
            $searchResults[$name]['dir'] = 'articles';
            for ($i = 0; $i < $c_results; ++$i) {
                $searchResults[$name]['results'][$i]['hyperlink'] = $this->uri->route('articles/index/details/id_' . $results[$i]['id']);
                $searchResults[$name]['results'][$i]['title'] = $results[$i]['title'];
                $searchResults[$name]['results'][$i]['text'] = $this->stringFormatter->shortenEntry($results[$i]['text'], 200, 0, '...');
            }
        }
        return $searchResults;
    }

    public function filesSearch()
    {
        switch ($this->area) {
            case 'title':
                $fields = 'title, file';
                break;
            case 'content':
                $fields = 'text';
                break;
            default:
                $fields = 'title, file, text';
        }

        $period = '(start = end AND start <= :time OR start != end AND :time BETWEEN start AND end)';
        $results = $this->db->fetchAll('SELECT id, title, text FROM ' . DB_PRE . 'files WHERE MATCH (' . $fields . ') AGAINST (:searchterm IN BOOLEAN MODE) AND ' . $period . ' ORDER BY start ' . $this->sort . ', end ' . $this->sort . ', id ' . $this->sort, $this->params);
        $c_results = count($results);
        $searchResults = array();

        if ($c_results > 0) {
            $name = $this->lang->t('files', 'files');
            $searchResults[$name]['dir'] = 'files';
            for ($i = 0; $i < $c_results; ++$i) {
                $searchResults[$name]['results'][$i]['hyperlink'] = $this->uri->route('files/index/details/id_' . $results[$i]['id']);
                $searchResults[$name]['results'][$i]['title'] = $results[$i]['title'];
                $searchResults[$name]['results'][$i]['text'] = $this->stringFormatter->shortenEntry($results[$i]['text'], 200, 0, '...');
            }
        }
        return $searchResults;
    }

    public function newsSearch()
    {
        switch ($this->area) {
            case 'title':
                $fields = 'title';
                break;
            case 'content':
                $fields = 'text';
                break;
            default:
                $fields = 'title, text';
        }

        $period = '(start = end AND start <= :time OR start != end AND :time BETWEEN start AND end)';
        $results = $this->db->fetchAll('SELECT id, title, text FROM ' . DB_PRE . 'news WHERE MATCH (' . $fields . ') AGAINST (:searchterm IN BOOLEAN MODE) AND ' . $period . ' ORDER BY start ' . $this->sort . ', end ' . $this->sort . ', id ' . $this->sort, $this->params);
        $c_results = count($results);
        $searchResults = array();

        if ($c_results > 0) {
            $name = $this->lang->t('news', 'news');
            $searchResults[$name]['dir'] = 'news';
            for ($i = 0; $i < $c_results; ++$i) {
                $searchResults[$name]['results'][$i]['hyperlink'] = $this->uri->route('news/index/details/id_' . $results[$i]['id']);
                $searchResults[$name]['results'][$i]['title'] = $results[$i]['title'];
                $searchResults[$name]['results'][$i]['text'] = $this->stringFormatter->shortenEntry($results[$i]['text'], 200, 0, '...');
            }
        }
        return $searchResults;
    }

}
