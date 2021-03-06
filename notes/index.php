<?php
/**
 * Copyright 2001-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (ASL). If you
 * did not receive this file, see http://www.horde.org/licenses/apache.
 *
 * @package Mnemo
 */

@define('MNEMO_BASE', dirname(__DIR__));
require_once MNEMO_BASE . '/lib/Application.php';
Horde_Registry::appInit('mnemo');

$search = Horde_Util::getGet('q');
if (!$search) {
    header('HTTP/1.0 204 No Content');
    exit;
}

$memos = Mnemo::listMemos($prefs->getValue('sortby'), $prefs->getValue('sortdir'));

$search_pattern = '/^' . preg_quote($search, '/') . '/i';
$search_results = array();
foreach ($memos as $memo_id => $memo) {
    if (preg_match($search_pattern, $memo['desc'])) {
        $search_results[$memo_id] = $memo;
    }
}

if (count($search_results) == 1) {
    $note = array_shift($search_results);
    Horde::url('view.php', true)
        ->add(array('memo' => $note['memo_id'], 'memolist' => $note['memolist_id']))
        ->redirect();
}

$memos = $search_results;

$page_output->addScriptFile('tables.js', 'horde');
$page_output->addScriptFile('quickfinder.js', 'horde');

$page_output->header(array(
    'title' => _("Search Results")
));
require MNEMO_TEMPLATES . '/list/header.inc';

if (count($memos)) {
    $sortby = $prefs->getValue('sortby');
    $sortdir = $prefs->getValue('sortdir');
    $showNotepad = $prefs->getValue('show_notepad');

    $baseurl = 'list.php';
    require MNEMO_TEMPLATES . '/list/memo_headers.inc';

    foreach ($memos as $memo_id => $memo) {
        $viewurl = Horde::url('view.php')->add(
            array('memo' => $memo['memo_id'],
                  'memolist' => $memo['memolist_id']));

        $memourl = Horde::url('memo.php')->add(
            array('memo' => $memo['memo_id'],
                  'memolist' => $memo['memolist_id']));
        try {
            $share = $GLOBALS['mnemo_shares']->getShare($memo['memolist_id']);
            $notepad = Mnemo::getLabel($share);
        } catch (Horde_Share_Exception $e) {
            $notepad = $memo['memolist_id'];
        }

        require MNEMO_TEMPLATES . '/list/memo_summaries.inc';
    }

    require MNEMO_TEMPLATES . '/list/memo_footers.inc';
} else {
    require MNEMO_TEMPLATES . '/list/empty.inc';
}

$page_output->footer();
