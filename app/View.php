<?php

namespace Lagdo\DbAdmin\App;

use Lagdo\DbAdmin\CallableClass;

use Exception;

/**
 * Adminer Ajax client
 */
class View extends CallableClass
{
    /**
     * Display the content of a tab
     *
     * @param array  $viewData  The data to be displayed in the view
     * @param string $tabId     The tab container id
     *
     * @return void
     */
    protected function showTab(array $viewData, string $tabId)
    {
        // Make data available to views
        $this->view()->shareValues($viewData);

        $content = $this->render('main/content');
        $this->response->html($tabId, $content);
    }

    /**
     * Show detailed info of a given view
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $view        The view name
     *
     * @return \Jaxon\Response\Response
     */
    public function show($server, $database, $schema, $view)
    {
        $viewInfo = $this->dbAdmin->getViewInfo($server, $database, $schema, $view);
        // Make view info available to views
        $this->view()->shareValues($viewInfo);

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

        $content = $this->render('main/db-table');
        $this->response->html($this->package->getDbContentId(), $content);

        // Show fields
        $fieldsInfo = $this->dbAdmin->getViewFields($server, $database, $schema, $view);
        $this->showTab($fieldsInfo, 'tab-content-fields');

        // Show triggers
        $triggersInfo = $this->dbAdmin->getViewTriggers($server, $database, $schema, $view);
        if(\is_array($triggersInfo))
        {
            $this->showTab($triggersInfo, 'tab-content-triggers');
        }

        // Set onclick handlers on toolbar buttons
        $this->jq('#adminer-main-action-edit-view')
            ->click($this->rq()->edit($server, $database, $schema, $view));
        $this->jq('#adminer-main-action-drop-view')
            ->click($this->rq()->drop($server, $database, $schema, $view)
            ->confirm("Drop view $view?"));

        return $this->response;
    }

    /**
     * Show the new view form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function add($server, $database, $schema)
    {
        $formId = 'view-form';
        $title = 'Create a view';
        $content = $this->render('view/add', [
            'formId' => $formId,
            'materializedview' => $this->dbAdmin->support($server, 'materializedview'),
        ]);
        $buttons = [[
            'title' => 'Cancel',
            'class' => 'btn btn-tertiary',
            'click' => 'close',
        ],[
            'title' => 'Save',
            'class' => 'btn btn-primary',
            'click' => $this->rq()->create($server, $database, $schema, \pm()->form($formId)),
        ]];
        $this->response->dialog->show($title, $content, $buttons);

        return $this->response;
    }

    /**
     * Show edit form for a given view
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $view        The view name
     *
     * @return \Jaxon\Response\Response
     */
    public function edit($server, $database, $schema, $view)
    {
        $viewData = $this->dbAdmin->getView($server, $database, $schema, $view);
        // Make view info available to views
        $this->view()->shareValues($viewData);

        $formId = 'view-form';
        $title = 'Edit a view';
        $content = $this->render('view/edit', [
            'formId' => $formId,
            'materializedview' => $this->dbAdmin->support($server, 'materializedview'),
        ]);
        $buttons = [[
            'title' => 'Cancel',
            'class' => 'btn btn-tertiary',
            'click' => 'close',
        ],[
            'title' => 'Save',
            'class' => 'btn btn-primary',
            'click' => $this->rq()->update($server, $database, $schema, $view, \pm()->form($formId)),
        ]];
        $this->response->dialog->show($title, $content, $buttons);

        return $this->response;
    }

    /**
     * Create a new view
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $values      The view values
     *
     * @return \Jaxon\Response\Response
     */
    public function create($server, $database, $schema, array $values)
    {
        $values['materialized'] = \array_key_exists('materialized', $values);

        $result = $this->dbAdmin->createView($server, $database, $schema, $values);
        if(!$result['success'])
        {
            $this->response->dialog->error($result['error']);
            return $this->response;
        }

        $this->response->dialog->hide();
        $this->cl(Database::class)->showViews($server, $database, $schema);
        $this->response->dialog->success($result['message']);
        return $this->response;
    }

    /**
     * Update a given view
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $view        The view name
     * @param string $values      The view values
     *
     * @return \Jaxon\Response\Response
     */
    public function update($server, $database, $schema, $view, array $values)
    {
        $values['materialized'] = \array_key_exists('materialized', $values);

        $result = $this->dbAdmin->updateView($server, $database, $schema, $view, $values);
        if(!$result['success'])
        {
            $this->response->dialog->error($result['error']);
            return $this->response;
        }

        $this->response->dialog->hide();
        $this->cl(Database::class)->showViews($server, $database, $schema);
        $this->response->dialog->success($result['message']);
        return $this->response;
    }

    /**
     * Drop a given view
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $view        The view name
     *
     * @return \Jaxon\Response\Response
     */
    public function drop($server, $database, $schema, $view)
    {
        $result = $this->dbAdmin->dropView($server, $database, $schema, $view);
        if(!$result['success'])
        {
            $this->response->dialog->error($result['error']);
            return $this->response;
        }

        $this->cl(Database::class)->showViews($server, $database, $schema);
        $this->response->dialog->success($result['message']);
        return $this->response;
    }
}
