<?php

/**
 * @package         Joomla.Plugins
 * @subpackage      Task.Resthits
 *
 * @copyright   (C) 2023 JCM
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\Resethits\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use LogicException;

\defined('_JEXEC') or die;

/**
 * Task plugin with routines that offer checks on files.
 * At the moment, offers a single routine to check and resize image files in a directory.
 *
 * @since  4.1.0
 */
final class Resethits extends CMSPlugin implements SubscriberInterface
{
    use TaskPluginTrait;
	use DatabaseAwareTrait;

    /**
     * @var string[]
     *
     * @since 4.1.0
     */
    protected const TASKS_MAP = [
        'Resethits.resethits' => [
            'langConstPrefix' => 'PLG_TASK_RESETHITS_RESETHITS',
            'form'            => 'resethits',
            'method'          => 'resetDataHits',
        ],
    ];

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @since 4.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList' => 'advertiseRoutines',
            'onExecuteTask' => 'standardRoutineHandler',
            'onContentPrepareForm' => 'enhanceTaskItemForm',
        ];
    }

    /**
     * @var boolean
     * @since 4.1.0
     */
    protected $autoloadLanguage = true;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher  The dispatcher
     * @param   array                $config      An optional associative array of configuration settings
     *
     * @since   4.2.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config)
    {
        parent::__construct($dispatcher, $config);
    }

    /**
     * @param   ExecuteTaskEvent  $event  The onExecuteTask event
     *
     * Reset hits for components setted to.
     *
     * @return integer  The exit code
     *
     * @throws \RuntimeException
     * @throws LogicException
     *
     * @since 4.1.0
     */
    protected function resetDataHits(ExecuteTaskEvent $event): int
    {
        $this->logTask('Launching Reset hits task...', 'info');
	$db = Factory::getDbo();
        //$db     = $this->getDatabase();

	$params = $event->getArgument('params');

        if (!$db) {
            $this->logTask('No DB connection', 'warning');
            return TaskStatus::NO_RUN;
        }

        if (empty($params)) {
            $this->logTask('Params are empty', 'warning');
            return TaskStatus::NO_RUN;
        }

	    
        $bIsContentReset = (int)$params->reset_content;
        $bIsTagReset = (int)$params->reset_tag;
        $bIsCategoryReset = (int)$params->reset_categ;
		
	if($bIsContentReset) {
	    $this->logTask('Processing Content reset...', 'info');
	    $query  = $db->getQuery(true);
	    $query->update($db->quoteName('#__content'));
            $query->set($db->quoteName('hits') . ' = 0');
            $db->setQuery($query)->execute();
	}

	if($bIsTagReset) {
	    $this->logTask('Processing Tag reset...', 'info');
	    $query  = $db->getQuery(true);
	    $query->update($db->quoteName('#__tags'));
            $query->set($db->quoteName('hits') . ' = 0');
            $db->setQuery($query)->execute();
	}

	if($bIsCategoryReset) {
	    $this->logTask('Processing Category reset...', 'info');
	    $query  = $db->getQuery(true);
	    $query->update($db->quoteName('#__categories'));
            $query->set($db->quoteName('hits') . ' = 0');
            $db->setQuery($query)->execute();
	}

        $this->logTask('Completed processing reset hits.', 'info');

        return TaskStatus::OK;
    }
}
