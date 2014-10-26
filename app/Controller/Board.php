<?php

namespace Controller;

use Model\Project as ProjectModel;
use Model\User as UserModel;
use Core\Security;

/**
 * Board controller
 *
 * @package  controller
 * @author   Frederic Guillot
 */
class Board extends Base
{
    /**
     * Move a column down or up
     *
     * @access public
     */
    public function moveColumn()
    {
        $this->checkCSRFParam();
        $project = $this->getProjectManagement();
        $column_id = $this->request->getIntegerParam('column_id');
        $direction = $this->request->getStringParam('direction');

        if ($direction === 'up' || $direction === 'down') {
            $this->board->{'move'.$direction}($project['id'], $column_id);
        }

        $this->response->redirect('?controller=board&action=edit&project_id='.$project['id']);
    }

    /**
     * Change a task assignee directly from the board
     *
     * @access public
     */
    public function changeAssignee()
    {
        $task = $this->getTask();
        $project = $this->project->getById($task['project_id']);
        $projects = $this->projectPermission->getAllowedProjects($this->acl->getUserId());
        $params = array(
            'errors' => array(),
            'values' => $task,
            'users_list' => $this->projectPermission->getUsersList($project['id']),
            'projects' => $projects,
            'current_project_id' => $project['id'],
            'current_project_name' => $project['name'],
        );

        if ($this->request->isAjax()) {

            $this->response->html($this->template->load('board_assignee', $params));
        }
        else {

            $this->response->html($this->template->layout('board_assignee', $params + array(
                'menu' => 'boards',
                'title' => t('Change assignee').' - '.$task['title'],
            )));
        }
    }

    /**
     * Validate an assignee modification
     *
     * @access public
     */
    public function updateAssignee()
    {
        $values = $this->request->getValues();
        $this->checkProjectPermissions($values['project_id']);

        list($valid,) = $this->taskValidator->validateAssigneeModification($values);

        if ($valid && $this->task->update($values)) {
            $this->session->flash(t('Task updated successfully.'));
        }
        else {
            $this->session->flashError(t('Unable to update your task.'));
        }

        $this->response->redirect('?controller=board&action=show&project_id='.$values['project_id']);
    }

    /**
     * Change a task category directly from the board
     *
     * @access public
     */
    public function changeCategory()
    {
        $task = $this->getTask();
        $project = $this->project->getById($task['project_id']);
        $projects = $this->projectPermission->getAllowedProjects($this->acl->getUserId());
        $params = array(
            'errors' => array(),
            'values' => $task,
            'categories_list' => $this->category->getList($project['id']),
            'projects' => $projects,
            'current_project_id' => $project['id'],
            'current_project_name' => $project['name'],
        );

        if ($this->request->isAjax()) {

            $this->response->html($this->template->load('board_category', $params));
        }
        else {

            $this->response->html($this->template->layout('board_category', $params + array(
                'menu' => 'boards',
                'title' => t('Change category').' - '.$task['title'],
            )));
        }
    }

    /**
     * Validate a category modification
     *
     * @access public
     */
    public function updateCategory()
    {
        $values = $this->request->getValues();
        $this->checkProjectPermissions($values['project_id']);

        list($valid,) = $this->taskValidator->validateCategoryModification($values);

        if ($valid && $this->task->update($values)) {
            $this->session->flash(t('Task updated successfully.'));
        }
        else {
            $this->session->flashError(t('Unable to update your task.'));
        }

        $this->response->redirect('?controller=board&action=show&project_id='.$values['project_id']);
    }

    /**
     * Display the public version of a board
     * Access checked by a simple token, no user login, read only, auto-refresh
     *
     * @access public
     */
    public function readonly()
    {
        $token = $this->request->getStringParam('token');
        $project = $this->project->getByToken($token);

        // Token verification
        if (! $project) {
            $this->forbidden(true);
        }

        // Display the board with a specific layout
        $this->response->html($this->template->layout('board_public', array(
            'project' => $project,
            'columns' => $this->board->get($project['id']),
            'categories' => $this->category->getList($project['id'], false),
            'title' => $project['name'],
            'no_layout' => true,
            'not_editable' => true,
            'board_public_refresh_interval' => $this->config->get('board_public_refresh_interval'),
        )));
    }

    /**
     * Redirect the user to the default project
     *
     * @access public
     */
    public function index()
    {
        $last_seen_project_id = $this->user->getLastSeenProjectId();
        $favorite_project_id = $this->user->getFavoriteProjectId();
        $project_id = $last_seen_project_id ?: $favorite_project_id;

        if (! $project_id) {
            $projects = $this->projectPermission->getAllowedProjects($this->acl->getUserId());

            if (empty($projects)) {

                if ($this->acl->isAdminUser()) {
                    $this->redirectNoProject();
                }

                $this->forbidden();
            }

            $project_id = key($projects);
        }

        $this->show($project_id);
    }

    /**
     * Show a board for a given project
     *
     * @access public
     * @param  integer   $project_id    Default project id
     */
    public function show($project_id = 0)
    {
        $project = $this->getProject($project_id);
        $projects = $this->projectPermission->getAllowedProjects($this->acl->getUserId());

        $board_selector = $projects;
        unset($board_selector[$project['id']]);

        $this->user->storeLastSeenProjectId($project['id']);

        $this->response->html($this->template->layout('board_index', array(
            'users' => $this->projectPermission->getUsersList($project['id'], true, true),
            'filters' => array('user_id' => UserModel::EVERYBODY_ID),
            'projects' => $projects,
            'current_project_id' => $project['id'],
            'current_project_name' => $project['name'],
            'board' => $this->board->get($project['id']),
            'categories' => $this->category->getList($project['id'], true, true),
            'menu' => 'boards',
            'title' => $project['name'],
            'board_selector' => $board_selector,
            'board_private_refresh_interval' => $this->config->get('board_private_refresh_interval'),
            'board_highlight_period' => $this->config->get('board_highlight_period'),
        )));
    }

    /**
     * Display a form to edit a board
     *
     * @access public
     */
    public function edit()
    {
        $project = $this->getProjectManagement();
        $columns = $this->board->getColumns($project['id']);
        $values = array();

        foreach ($columns as $column) {
            $values['title['.$column['id'].']'] = $column['title'];
            $values['task_limit['.$column['id'].']'] = $column['task_limit'] ?: null;
        }

        $this->response->html($this->projectLayout('board_edit', array(
            'errors' => array(),
            'values' => $values + array('project_id' => $project['id']),
            'columns' => $columns,
            'project' => $project,
            'menu' => 'projects',
            'title' => t('Edit board')
        )));
    }

    /**
     * Validate and update a board
     *
     * @access public
     */
    public function update()
    {
        $project = $this->getProjectManagement();
        $columns = $this->board->getColumns($project['id']);
        $data = $this->request->getValues();
        $values = $columns_list = array();

        foreach ($columns as $column) {
            $columns_list[$column['id']] = $column['title'];
            $values['title['.$column['id'].']'] = isset($data['title'][$column['id']]) ? $data['title'][$column['id']] : '';
            $values['task_limit['.$column['id'].']'] = isset($data['task_limit'][$column['id']]) ? $data['task_limit'][$column['id']] : 0;
        }

        list($valid, $errors) = $this->board->validateModification($columns_list, $values);

        if ($valid) {

            if ($this->board->update($data)) {
                $this->session->flash(t('Board updated successfully.'));
                $this->response->redirect('?controller=board&action=edit&project_id='.$project['id']);
            }
            else {
                $this->session->flashError(t('Unable to update this board.'));
            }
        }

        $this->response->html($this->projectLayout('board_edit', array(
            'errors' => $errors,
            'values' => $values + array('project_id' => $project['id']),
            'columns' => $columns,
            'project' => $project,
            'menu' => 'projects',
            'title' => t('Edit board')
        )));
    }

    /**
     * Validate and add a new column
     *
     * @access public
     */
    public function add()
    {
        $project = $this->getProjectManagement();
        $columns = $this->board->getColumnsList($project['id']);
        $data = $this->request->getValues();
        $values = array();

        foreach ($columns as $column_id => $column_title) {
            $values['title['.$column_id.']'] = $column_title;
        }

        list($valid, $errors) = $this->board->validateCreation($data);

        if ($valid) {

            if ($this->board->addColumn($project['id'], $data['title'])) {
                $this->session->flash(t('Board updated successfully.'));
                $this->response->redirect('?controller=board&action=edit&project_id='.$project['id']);
            }
            else {
                $this->session->flashError(t('Unable to update this board.'));
            }
        }

        $this->response->html($this->projectLayout('board_edit', array(
            'errors' => $errors,
            'values' => $values + $data,
            'columns' => $columns,
            'project' => $project,
            'menu' => 'projects',
            'title' => t('Edit board')
        )));
    }

    /**
     * Remove a column
     *
     * @access public
     */
    public function remove()
    {
        $project = $this->getProjectManagement();

        if ($this->request->getStringParam('remove') === 'yes') {

            $this->checkCSRFParam();
            $column = $this->board->getColumn($this->request->getIntegerParam('column_id'));

            if ($column && $this->board->removeColumn($column['id'])) {
                $this->session->flash(t('Column removed successfully.'));
            } else {
                $this->session->flashError(t('Unable to remove this column.'));
            }

            $this->response->redirect('?controller=board&action=edit&project_id='.$project['id']);
        }

        $this->response->html($this->projectLayout('board_remove', array(
            'column' => $this->board->getColumn($this->request->getIntegerParam('column_id')),
            'project' => $project,
            'menu' => 'projects',
            'title' => t('Remove a column from a board')
        )));
    }

    /**
     * Save the board (Ajax request made by the drag and drop)
     *
     * @access public
     */
    public function save()
    {
        $project_id = $this->request->getIntegerParam('project_id');

        if ($project_id > 0 && $this->request->isAjax()) {

            if (! $this->projectPermission->isUserAllowed($project_id, $this->acl->getUserId())) {
                $this->response->status(401);
            }

            $values = $this->request->getValues();

            if ($this->task->movePosition($project_id, $values['task_id'], $values['column_id'],$values['release_id'], $values['position'])) {

                $this->response->html(
                    $this->template->load('board_show', array(
                        'current_project_id' => $project_id,
                        'board' => $this->board->get($project_id),
                        'categories' => $this->category->getList($project_id, false),
                        'board_private_refresh_interval' => $this->config->get('board_private_refresh_interval'),
                        'board_highlight_period' => $this->config->get('board_highlight_period'),
                    )),
                    201
                );
            }
            else {

                $this->response->status(400);
            }
        }
        else {
            $this->response->status(401);
        }
    }

    /**
     * Check if the board have been changed
     *
     * @access public
     */
    public function check()
    {
        if ($this->request->isAjax()) {

            $project_id = $this->request->getIntegerParam('project_id');
            $timestamp = $this->request->getIntegerParam('timestamp');

            if ($project_id > 0 && ! $this->projectPermission->isUserAllowed($project_id, $this->acl->getUserId())) {
                $this->response->text('Not Authorized', 401);
            }

            if ($this->project->isModifiedSince($project_id, $timestamp)) {
                $this->response->html(
                    $this->template->load('board_show', array(
                        'current_project_id' => $project_id,
                        'board' => $this->board->get($project_id),
                        'categories' => $this->category->getList($project_id, false),
                        'board_private_refresh_interval' => $this->config->get('board_private_refresh_interval'),
                        'board_highlight_period' => $this->config->get('board_highlight_period'),
                    ))
                );
            }
            else {
                $this->response->status(304);
            }
        }
        else {
            $this->response->status(401);
        }
    }
}
