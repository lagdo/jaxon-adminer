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
     * Insert a new HTML element before a given target
     *
     * @param string $target      The target element
     * @param string $id          The new element id
     * @param string $class       The new element class
     * @param string $content     The new element content
     * @param array  $attrs       The new element attributes
     *
     * @return \Jaxon\Response\Response
     */
    private function insertBefore($target, $id, $class, $content, array $attrs = [])
    {
        // Insert a div with the id before the target
        $this->response->insertBefore($target, 'div', $id);
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
     * Insert a new HTML element after a given target
     *
     * @param string $target      The target element
     * @param string $id          The new element id
     * @param string $class       The new element class
     * @param string $content     The new element content
     * @param array  $attrs       The new element attributes
     *
     * @return \Jaxon\Response\Response
     */
    private function insertAfter($target, $id, $class, $content, array $attrs = [])
    {
        // Insert a div with the id after the target
        $this->response->insertAfter($target, 'div', $id);
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
        $vars = [
            'index' => $length,
            'field' => $this->dbProxy->getTableField($server, $database),
            'prefixFields' => sprintf("fields[%d]", $length + 1),
        ];
        if($target < 0)
        {
            // Get the content with wrapper
            $vars['class'] = $columnClass;
        }
        $content = $this->render('table/column', $vars);

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
            $targetId = \sprintf('%s-%02d', $columnClass, $target);
            $this->insertAfter($targetId, $columnId, $columnClass, $content, ['data-index' => $length]);
            // $this->response->prepend($targetId, 'outerHTML', $content);
        }

        $contentId = $this->package->getDbContentId();
        $length = \jq(".$columnClass", "#$contentId")->length;
        $index = \jq()->parent()->attr('data-index');
        // Set the button event handlers on the new column
        $this->jq('[data-field]', "#$columnId")
            ->on('jaxon.adminer.renamed', \pm()->js('jaxon.adminer.onColumnRenamed'));
        $this->jq('.adminer-table-column-add', "#$columnId")
            ->click($this->rq()->add($server, $database, $length, $index));
        $this->jq('.adminer-table-column-del', "#$columnId")
            ->click($this->rq()->del($server, $database, $length, $index)
            ->confirm('Delete this column?'));

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
        $this->response->remove($columnId);

        // Reset the added columns ids and input names, so they remain contiguous.
        $length--;
        for($id = $index; $id < $length; $id++)
        {
            $currId = \sprintf('%s-column-%02d', $this->formId, $id + 1);
            $nextId = \sprintf('%s-column-%02d', $this->formId, $id);
            $this->jq("#$currId")->attr('data-index', $id)->attr('id', $nextId);
            $this->jq('.adminer-table-column-buttons', "#$nextId")->attr('data-index', $id);
            $this->jq('[data-field]', "#$nextId")->trigger('jaxon.adminer.renamed');
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

        // To mark a column as to be dropped, set its name to an empty string.
        $this->jq('input.column-name', "#$columnId")->attr('name', '');
        // Replace the icon and the onClick event handler.
        $this->jq('.adminer-table-column-del', "#$columnId")
            ->removeClass('btn-primary')
            ->addClass('btn-danger')
            // Remove the current onClick handler before setting a new one.
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
        $columnName = \sprintf('fields[%d][field]', $index + 1);

        // To cancel the drop, reset the column name to its initial value.
        $this->jq('input.column-name', "#$columnId")->attr('name', $columnName);
        // Replace the icon and the onClick event handler.
        $this->jq('.adminer-table-column-del', "#$columnId")
            ->removeClass('btn-danger')
            ->addClass('btn-primary')
            // Remove the current onClick handler before setting a new one.
            ->unbind('click')
            ->click($this->rq()->setForDelete($server, $database, $index));
        $this->jq('.adminer-table-column-del>span', "#$columnId")
            ->removeClass('glyphicon-trash')
            ->addClass('glyphicon-remove');

        return $this->response;
    }
}
