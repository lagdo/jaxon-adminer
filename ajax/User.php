<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\Package;
use Lagdo\Adminer\AdminerCallable;
use Lagdo\Adminer\Db\Proxy as DbProxy;

use Exception;

/**
 * Adminer Ajax
 */
class User extends AdminerCallable
{
    /**
     * The constructor
     *
     * @param Package $package    The Adminer package
     * @param DbProxy $dbProxy    The proxy to Adminer
     */
    public function __construct(Package $package, DbProxy $dbProxy)
    {
        $this->package = $package;
        $this->dbProxy = $dbProxy;
    }

    /**
     * Show the new user form
     *
     * @param string $server      The database server
     *
     * @return \Jaxon\Response\Response
     */
    public function add(string $server)
    {
        $options = $this->package->getServerOptions($server);

        $userInfo = $this->dbProxy->newUserPrivileges($options);

        // Make user info available to views
        foreach($userInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $formId = 'user-form';
        $title = 'Add user privileges';
        $content = $this->render('main/user', [
            'formId' => $formId,
            'content' => $content = $this->render('main/content'),
        ]);
        // $this->response->html($this->package->getDbContentId(), $content);

        $buttons = [[
            'title' => 'Cancel',
            'class' => 'btn btn-tertiary',
            'click' => 'close',
        ],[
            'title' => 'Save',
            'class' => 'btn btn-primary',
            'click' => $this->rq()->create($server, \pm()->form($formId)),
        ]];
        $this->response->dialog->show($title, $content, $buttons);

        return $this->response;
    }

    /**
     * Save new user privileges
     *
     * @param string $server      The database server
     * @param array  $formValues  The form values
     *
     * @return \Jaxon\Response\Response
     */
    public function create(string $server, array $formValues)
    {
        // $this->logger()->debug('Form values', $formValues);

        $this->response->dialog->hide();
        $this->response->dialog->warning("This feature is not yet implemented.");
        // $this->response->dialog->info("User privileges created.");

        return $this->response;
    }

    /**
     * Show the edit user form
     *
     * @param string $server    The database server
     * @param string $username  The user name
     * @param string $hostname  The host name
     * @param string $database  The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function edit(string $server, string $username, string $hostname, string $database)
    {
        $options = $this->package->getServerOptions($server);

        $userInfo = $this->dbProxy->getUserPrivileges($options, $username, $hostname, $database);

        // Make user info available to views
        foreach($userInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $formId = 'user-form';
        $title = 'Edit user privileges';
        $content = $this->render('main/user', [
            'formId' => $formId,
            'content' => $content = $this->render('main/content'),
        ]);
        // $this->response->html($this->package->getDbContentId(), $content);

        $buttons = [[
            'title' => 'Cancel',
            'class' => 'btn btn-tertiary',
            'click' => 'close',
        ],[
            'title' => 'Save',
            'class' => 'btn btn-primary',
            'click' => $this->rq()->update($server, \pm()->form($formId)),
        ]];
        $this->response->dialog->show($title, $content, $buttons);

        return $this->response;
    }

    /**
     * Update user privileges
     *
     * @param string $server      The database server
     * @param array  $formValues  The form values
     *
     * @return \Jaxon\Response\Response
     */
    public function update(string $server, array $formValues)
    {
        // $this->logger()->debug('Form values', $formValues);

        $this->response->dialog->hide();
        $this->response->dialog->warning("This feature is not yet implemented.");
        // $this->response->dialog->info("User privileges updated.");

        return $this->response;
    }
}
