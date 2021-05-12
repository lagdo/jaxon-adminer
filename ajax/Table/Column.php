<?php

namespace Lagdo\Adminer\Ajax\Table;

use Lagdo\Adminer\AdminerCallable;

use Exception;

/**
 * When creating or modifying a table, this class
 * provides CRUD features on table columns.
 * It does not persist data. It only updates the UI.
 */
class Column extends AdminerCallable
{
    /**
     * The form id
     */
    protected $formId = 'adminer-table-form';

    /**
     * Insert a new column at a given position
     *
     * @param string $target      The target element
     * @param string $id          The new element id
     * @param string $class       The new element class
     * @param string $content     The new element content
     * @param array  $attrs       The new element attributes
     *
     * @return \Jaxon\Response\Response
     */
    public function insertBefore($target, $id, $class, $content, array $attrs = [])
    {
        // Insert a div with the id before the target
        $this->response->insert($target, 'div', $id);
        // Set the new element class
        $this->jq("#$id")->attr('class', "form-group $class");
        // Set the new element attributes
        foreach($attrs as $name => $value)
        {
            $this->jq("#$id")->attr($name, $value);
        }
        // Set the new element content
        $this->response->html($id, $content);
    }

    /**
     * Insert a new column at a given position
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param int    $length      The number of columns in the table
     * @param int    $target      The new column is added before this position. Set to -1 to add at the end.
     *
     * @return \Jaxon\Response\Response
     */
    public function add($server, $database, $length, $target = -1)
    {
        $tableData = $this->dbProxy->getTableData($server, $database);
        // Make data available to views
        $this->view()->shareValues($tableData);

        $columnClass = "{$this->formId}-column";
        $columnId = \sprintf('%s-%02d', $columnClass, $length);
        $targetId = \sprintf('%s-%02d', $columnClass, $target);
        $vars = [
            'index' => $length,
            'field' => $this->dbProxy->getTableField($server, $database)
        ];
        if($target < 0)
        {
            // Get the content with wrapper
            $vars['class'] = $columnClass;
        }
        $content = $this->render('table/column', $vars);

        $contentId = $this->package->getDbContentId();
        $length = \jq(".$columnClass", "#$contentId")->length;
        $index = \jq()->parent()->attr('data-index');
        if($target < 0)
        {
            // Add the new column at the end of the list
            $this->response->append($this->formId, 'innerHTML', $content);
        }
        else
        {
            // Insert the new column before the given index
            /*
            * The prepend() function is not suitable here because it rewrites the
            * $targetId element, resetting all its event handlers and inputs.
            */
            $this->insertBefore($targetId, $columnId, $columnClass, $content);
            // $this->response->prepend($targetId, 'outerHTML', $content);
        }

        // Set the button event handlers on the new and the modified column
        $this->jq('.adminer-table-column-add', "#$columnId")
            ->click($this->rq()->add($server, $database, $length, $index));
        $this->jq('.adminer-table-column-del', "#$columnId")
            ->click($this->rq()->del($server, $database, $length, $index));

        return $this->response;
    }

    /**
     * Delete a column
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param int    $length      The number of columns in the table
     * @param int    $index       The column index
     *
     * @return \Jaxon\Response\Response
     */
    public function del($server, $database, $length, $index)
    {
        $columnId = \sprintf('%s-column-%02d', $this->formId, $index);

        // Delete the column
        $this->response->remove($columnId, 'outerHTML', '');

        // Reset the added columns ids, so they remain contiguous.
        $length--;
        for($id = $index; $id < $length; $id++)
        {
            $currId = \sprintf('%s-column-%02d', $this->formId, $id + 1);
            $nextId = \sprintf('%s-column-%02d', $this->formId, $id);
            $this->jq('.adminer-table-column-buttons', "#$currId")->attr('data-index', $id);
            $this->jq("#$currId")->attr('id', $nextId);
        }

        return $this->response;
    }

    /**
     * Mark an existing column as to be deleted
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param int    $index       The column index
     *
     * @return \Jaxon\Response\Response
     */
    public function setForDelete($server, $database, $index)
    {
        $columnId = \sprintf('%s-column-%02d', $this->formId, $index);

        $this->jq('.adminer-table-column-del', "#$columnId")
            ->removeClass('btn-primary')
            ->addClass('btn-danger')
            // Remove the current onClick handler and set a new one.
            ->unbind('click')
            ->click($this->rq()->cancelDelete($server, $database, $index));
        $this->jq('.adminer-table-column-del>span', "#$columnId")
            ->removeClass('glyphicon-remove')
            ->addClass('glyphicon-trash');

        return $this->response;
    }

    /**
     * Cancel delete on an existing column
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param int    $index       The column index
     *
     * @return \Jaxon\Response\Response
     */
    public function cancelDelete($server, $database, $index)
    {
        $columnId = \sprintf('%s-column-%02d', $this->formId, $index);

        $this->jq('.adminer-table-column-del', "#$columnId")
            ->removeClass('btn-danger')
            ->addClass('btn-primary')
            // Remove the current onClick handler and set a new one.
            ->unbind('click')
            ->click($this->rq()->setForDelete($server, $database, $index));
        $this->jq('.adminer-table-column-del>span', "#$columnId")
            ->removeClass('glyphicon-trash')
            ->addClass('glyphicon-remove');

        return $this->response;
    }
}
